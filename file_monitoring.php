<?php
/**
 * RansomGuard - File Integrity Monitoring (FIM) Page
 * Reuses existing theme.css and dashboard.css
 */

require_once __DIR__ . '/includes/auth.php';
require_auth(); // Restrict to authenticated users

$currentUser = current_user();
$isAdmin = is_admin();
$pdo = get_db_connection();

$feedbackMsg = '';
$feedbackType = '';

// -----------------------------------------------------------------------------
// POST Request Handlers (Administrator Actions Only)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $action = $_POST['action'] ?? '';

    // Action 1: Add Monitored Folder
    if ($action === 'add_folder') {
        $folderPath = trim($_POST['folder_path'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $recursive = isset($_POST['recursive']) ? 1 : 0;

        if (empty($folderPath)) {
            $feedbackMsg = 'Please provide a valid directory path.';
            $feedbackType = 'error';
        } else {
            // Normalize path
            $normalizedPath = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $folderPath), DIRECTORY_SEPARATOR);

            // If directory doesn't exist on disk, attempt to create it for testing convenience
            if (!is_dir($normalizedPath)) {
                if (!@mkdir($normalizedPath, 0777, true) && !is_dir($normalizedPath)) {
                    $feedbackMsg = "Directory does not exist and could not be created: $normalizedPath";
                    $feedbackType = 'error';
                }
            }

            if (empty($feedbackMsg)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO monitored_paths (`path`, `recursive`, `is_active`, `description`, `created_by`, `created_at`)
                        VALUES (:path, :rec, 1, :desc, :uid, NOW())
                    ");
                    $stmt->execute([
                        ':path' => $normalizedPath,
                        ':rec'  => $recursive,
                        ':desc' => $description ?: 'Administrator Monitored Directory',
                        ':uid'  => $currentUser['id']
                    ]);
                    $feedbackMsg = "Folder successfully added to monitoring queue: " . htmlspecialchars($normalizedPath);
                    $feedbackType = 'success';
                } catch (Exception $e) {
                    $feedbackMsg = "Database error: " . $e->getMessage();
                    $feedbackType = 'error';
                }
            }
        }
    }

    // Action 2: Remove Monitored Folder
    if ($action === 'remove_folder') {
        $pathId = (int)($_POST['path_id'] ?? 0);
        if ($pathId > 0) {
            try {
                // Delete associated files & snapshots
                $pdo->prepare("DELETE FROM monitored_files WHERE path_id = :id")->execute([':id' => $pathId]);
                $pdo->prepare("DELETE FROM monitored_paths WHERE id = :id")->execute([':id' => $pathId]);
                $feedbackMsg = "Monitored folder #$pathId and associated file indexes successfully removed.";
                $feedbackType = 'success';
            } catch (Exception $e) {
                $feedbackMsg = "Error removing folder: " . $e->getMessage();
                $feedbackType = 'error';
            }
        }
    }

    // Action 3: Toggle Enable/Disable Folder
    if ($action === 'toggle_folder') {
        $pathId = (int)($_POST['path_id'] ?? 0);
        $newStatus = (int)($_POST['new_status'] ?? 1);
        if ($pathId > 0) {
            try {
                $pdo->prepare("UPDATE monitored_paths SET is_active = :status WHERE id = :id")
                    ->execute([':status' => $newStatus, ':id' => $pathId]);
                $feedbackMsg = "Folder status updated to " . ($newStatus ? "ACTIVE" : "DISABLED") . ".";
                $feedbackType = 'success';
            } catch (Exception $e) {
                $feedbackMsg = "Error toggling folder: " . $e->getMessage();
                $feedbackType = 'error';
            }
        }
    }

    // Action 4: Toggle Service Start / Stop
    if ($action === 'toggle_service') {
        $targetStatus = ($_POST['service_target'] ?? 'running') === 'running' ? 'running' : 'stopped';
        try {
            $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value, category, description)
                VALUES ('fim_service_status', :stat, 'fim', 'FIM background service status')
                ON DUPLICATE KEY UPDATE setting_value = :stat2, updated_at = NOW()
            ")->execute([':stat' => $targetStatus, ':stat2' => $targetStatus]);
            $feedbackMsg = "FIM Service status changed to " . strtoupper($targetStatus) . ".";
            $feedbackType = 'success';
        } catch (Exception $e) {
            $feedbackMsg = "Error updating service status: " . $e->getMessage();
            $feedbackType = 'error';
        }
    }
}

// -----------------------------------------------------------------------------
// Data Queries for Dashboard Rendering
// -----------------------------------------------------------------------------

// 1. Service Status & Heartbeat
$stmtService = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'fim_service_status'");
$serviceState = $stmtService->fetchColumn() ?: 'running';

$stmtHeartbeat = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'fim_last_heartbeat'");
$lastHeartbeat = $stmtHeartbeat->fetchColumn() ?: null;
$isServiceLive = ($serviceState === 'running') && $lastHeartbeat && (time() - strtotime($lastHeartbeat) < 45);

// 2. Metrics Counts
$totalFolders = (int)$pdo->query("SELECT COUNT(*) FROM monitored_paths")->fetchColumn();
$activeFolders = (int)$pdo->query("SELECT COUNT(*) FROM monitored_paths WHERE is_active = 1")->fetchColumn();
$totalTrackedFiles = (int)$pdo->query("SELECT COUNT(*) FROM monitored_files WHERE status != 'deleted'")->fetchColumn();
$modifiedAlertFiles = (int)$pdo->query("SELECT COUNT(*) FROM monitored_files WHERE status = 'modified'")->fetchColumn();
$totalEventsToday = (int)$pdo->query("SELECT COUNT(*) FROM file_activity_logs WHERE timestamp >= NOW() - INTERVAL 24 HOUR")->fetchColumn();

// 3. Monitored Paths List with file counts
$pathsList = $pdo->query("
    SELECT p.*, COUNT(f.id) as files_count, u.username as creator_name
    FROM monitored_paths p
    LEFT JOIN monitored_files f ON p.id = f.path_id AND f.status != 'deleted'
    LEFT JOIN users u ON p.created_by = u.id
    GROUP BY p.id
    ORDER BY p.id DESC
")->fetchAll();

// 4. Tracked Files (Most recent 50)
$filesList = $pdo->query("
    SELECT f.*, p.path as parent_folder
    FROM monitored_files f
    LEFT JOIN monitored_paths p ON f.path_id = p.id
    ORDER BY f.last_scanned_at DESC, f.id DESC
    LIMIT 50
")->fetchAll();

// 5. Recent File Activity Logs (Most recent 50)
$activityLogs = $pdo->query("
    SELECT a.*, f.file_name
    FROM file_activity_logs a
    LEFT JOIN monitored_files f ON a.file_id = f.id
    ORDER BY a.timestamp DESC
    LIMIT 50
")->fetchAll();

// Initials for user avatar
$avatarInitials = strtoupper(substr($currentUser['full_name'] ?: $currentUser['username'], 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Monitoring - RansomGuard SOC Platform</title>
  <meta name="description" content="RansomGuard File Integrity Monitoring (FIM) and Real-Time Cryptographic File Tamper Detection.">

  <!-- Reusing Exact Same CSS Theme and Dashboard Styling -->
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
    /* Scoped enhancements for FIM Module */
    .fim-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 8px;
      border-radius: var(--radius-sm, 6px);
      font-size: 11px;
      font-weight: 700;
      font-family: var(--font-mono, monospace);
    }
    .badge-create { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge-modify { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .badge-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .badge-rename { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }

    .service-btn-pulse {
      display: inline-block;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background-color: currentColor;
    }

    .form-inline-card {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: flex-end;
      padding: 16px 20px;
      background: rgba(13, 23, 40, 0.6);
      border-radius: var(--radius-md, 8px);
      margin-bottom: 18px;
      border: 1px solid var(--border-card);
    }
    .form-group-fim {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;
      min-width: 200px;
    }
    .form-label-fim {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-muted);
    }
    .form-input-fim {
      height: 38px;
      padding: 0 12px;
      background: var(--bg-card);
      border: 1px solid var(--border-card);
      border-radius: var(--radius-sm, 6px);
      color: var(--text-primary);
      font-size: 13px;
      outline: none;
    }
    .form-input-fim:focus {
      border-color: var(--color-cyan);
    }
    .checkbox-label-fim {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: var(--text-secondary);
      height: 38px;
      cursor: pointer;
    }
    .action-btn-sm {
      padding: 5px 10px;
      font-size: 11.5px;
      border-radius: 4px;
      border: 1px solid var(--border-card);
      background: transparent;
      color: var(--text-secondary);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all var(--transition-fast);
    }
    .action-btn-sm:hover {
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-primary);
    }
    .action-btn-danger:hover {
      background: rgba(239, 68, 68, 0.15);
      border-color: rgba(239, 68, 68, 0.4);
      color: #f87171;
    }
    .action-btn-success:hover {
      background: rgba(16, 185, 129, 0.15);
      border-color: rgba(16, 185, 129, 0.4);
      color: #34d399;
    }
    .hash-mono {
      font-family: var(--font-mono, monospace);
      font-size: 11px;
      color: #94a3b8;
      max-width: 140px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      display: block;
    }
    .cli-cmd-box {
      background: #060b13;
      border: 1px solid var(--border-card);
      border-radius: var(--radius-sm);
      padding: 8px 12px;
      font-family: monospace;
      font-size: 12px;
      color: #38bdf8;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <div class="app-container">
    
    <!-- ====================================================================
         1. SIDEBAR NAVIGATION (Exact match to existing dashboard)
         ==================================================================== -->
    <aside class="sidebar" id="sidebar">
      <div>
        <nav class="sidebar-nav" aria-label="Main Navigation">
          <!-- 1. Dashboard -->
          <a href="index.php" class="nav-item" id="nav-dashboard">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
              </svg>
              <span>Dashboard</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 2. User Information -->
          <a href="index.php#users" class="nav-item" id="nav-users">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span>User Information</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 3. Network Device Inventory -->
          <a href="index.php#devices" class="nav-item" id="nav-devices">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
              </svg>
              <span>Network Device Inventory</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 4. Packet Capture Logs -->
          <a href="index.php#pcap" class="nav-item" id="nav-pcap">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="4" y1="6" x2="20" y2="6"></line>
                <line x1="4" y1="12" x2="20" y2="12"></line>
                <line x1="4" y1="18" x2="12" y2="18"></line>
                <circle cx="18" cy="18" r="2"></circle>
              </svg>
              <span>Packet Capture Logs</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 5. Detection Logs -->
          <a href="index.php#detection" class="nav-item" id="nav-detection">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
              </svg>
              <span>Detection Logs</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 6. Alert Logs -->
          <a href="index.php#alerts" class="nav-item" id="nav-alerts">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <span>Alert Logs</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 7. File Monitoring (ACTIVE) -->
          <a href="file_monitoring.php" class="nav-item active" id="nav-fim" aria-current="page">
            <div class="nav-item-left">
              <!-- Folder / File Shield SVG -->
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                <polyline points="12 11 12 17 15 14"></polyline>
              </svg>
              <span>File Monitoring</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 8. Reports -->
          <a href="index.php#reports" class="nav-item" id="nav-reports">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
              </svg>
              <span>Reports</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>

          <!-- 9. Settings -->
          <a href="index.php#settings" class="nav-item" id="nav-settings">
            <div class="nav-item-left">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
              </svg>
              <span>Settings</span>
            </div>
            <span class="nav-active-dot"></span>
          </a>
        </nav>
      </div>

      <!-- User Profile at Bottom of Sidebar -->
      <div class="sidebar-footer">
        <div class="user-profile">
          <div class="user-avatar" title="<?php echo htmlspecialchars($currentUser['username']); ?>"><?php echo htmlspecialchars($avatarInitials); ?></div>
          <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($currentUser['role']); ?></span>
          </div>
        </div>
        <a href="logout.php" class="sidebar-logout-btn" id="logoutBtn" title="Lock Console / Sign Out">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </a>
      </div>
    </aside>

    <!-- ====================================================================
         2. MAIN CONTENT AREA
         ==================================================================== -->
    <main class="main-content">
      
      <!-- Top Header -->
      <header class="dashboard-header">
        <div class="header-title-group">
          <h1 id="mainHeaderTitle">File Integrity Monitoring (FIM)</h1>
          <div class="header-subtitle" id="mainHeaderSubtitle">REAL-TIME ENDPOINT CRYPTOGRAPHIC INTEGRITY & RANSOMWARE DETECTION</div>
        </div>
        <div class="header-actions">
          
          <!-- Live Service Status Badge -->
          <div class="live-status-badge" style="cursor: default;" title="Heartbeat: <?php echo htmlspecialchars($lastHeartbeat ?: 'Never'); ?>">
            <span class="live-pulse-dot" style="<?php echo $isServiceLive ? '' : 'background: #f59e0b; box-shadow: none;'; ?>"></span>
            <span><?php echo $isServiceLive ? 'FIM SERVICE ACTIVE' : ($serviceState === 'stopped' ? 'SERVICE STOPPED' : 'WAITING FOR CLI RUNNER'); ?></span>
          </div>

          <!-- Administrator Start/Stop Service Toggle -->
          <?php if ($isAdmin): ?>
            <form method="POST" style="margin: 0; display: inline-flex;">
              <input type="hidden" name="action" value="toggle_service">
              <input type="hidden" name="service_target" value="<?php echo $serviceState === 'running' ? 'stopped' : 'running'; ?>">
              <button type="submit" class="btn-cyan" style="<?php echo $serviceState === 'running' ? 'background: #0d1728; border: 1px solid #ef4444; color: #f87171;' : ''; ?>" title="Toggle CLI Background Monitor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <?php if ($serviceState === 'running'): ?>
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                  <?php else: ?>
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                  <?php endif; ?>
                </svg>
                <span><?php echo $serviceState === 'running' ? 'Pause Service' : 'Resume Service'; ?></span>
              </button>
            </form>
          <?php endif; ?>

        </div>
      </header>

      <!-- Feedback Alerts -->
      <?php if (!empty($feedbackMsg)): ?>
        <div style="margin: 0 0 20px 0; padding: 12px 18px; border-radius: var(--radius-md); font-size: 13px; display: flex; align-items: center; justify-content: space-between; <?php echo $feedbackType === 'success' ? 'background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7;' : 'background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5;'; ?>">
          <span><?php echo htmlspecialchars($feedbackMsg); ?></span>
          <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer;">&times;</button>
        </div>
      <?php endif; ?>

      <!-- ==================================================================
           METRICS ROW (4 Cards matching existing style)
           ================================================================== -->
      <div class="metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 22px;">
        
        <!-- Metric 1: Monitored Folders -->
        <div class="metric-card">
          <div class="metric-top">
            <span class="metric-label">Monitored Folders</span>
            <span class="metric-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
              </svg>
            </span>
          </div>
          <div class="metric-value"><?php echo $activeFolders; ?> <span style="font-size: 13px; color: var(--text-muted); font-weight: normal;">/ <?php echo $totalFolders; ?> total</span></div>
        </div>

        <!-- Metric 2: Tracked Files -->
        <div class="metric-card">
          <div class="metric-top">
            <span class="metric-label">Cataloged Files</span>
            <span class="metric-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
              </svg>
            </span>
          </div>
          <div class="metric-value"><?php echo number_format($totalTrackedFiles); ?></div>
        </div>

        <!-- Metric 3: Modified Files -->
        <div class="metric-card">
          <div class="metric-top">
            <span class="metric-label">Integrity Violations</span>
            <span class="metric-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
            </span>
          </div>
          <div class="metric-value" style="<?php echo $modifiedAlertFiles > 0 ? 'color: #f87171;' : ''; ?>"><?php echo $modifiedAlertFiles; ?></div>
        </div>

        <!-- Metric 4: 24h Activity Events -->
        <div class="metric-card">
          <div class="metric-top">
            <span class="metric-label">24h Event Telemetry</span>
            <span class="metric-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
            </span>
          </div>
          <div class="metric-value"><?php echo number_format($totalEventsToday); ?></div>
        </div>

      </div>

      <!-- ==================================================================
           SECTION 1: MONITORED FOLDERS (ADMIN MANAGEMENT)
           ================================================================== -->
      <div class="soc-card table-card" style="margin-bottom: 24px;">
        <div class="soc-card-header">
          <span class="soc-card-title">Monitored Folders Configuration</span>
          <span class="text-muted" style="font-size: 11px;">The background service monitors ONLY paths explicitly registered below.</span>
        </div>

        <!-- Administrator: Add Monitored Folder Form -->
        <?php if ($isAdmin): ?>
          <form method="POST" class="form-inline-card">
            <input type="hidden" name="action" value="add_folder">

            <div class="form-group-fim" style="flex: 2;">
              <label class="form-label-fim">Directory Path to Monitor</label>
              <input type="text" name="folder_path" class="form-input-fim" placeholder="e.g. C:\xampp\Final21\storage or C:\ProtectedFiles" required>
            </div>

            <div class="form-group-fim" style="flex: 1.5;">
              <label class="form-label-fim">Description / Department</label>
              <input type="text" name="description" class="form-input-fim" placeholder="e.g. Finance Shared Storage">
            </div>

            <div class="form-group-fim" style="flex: 0 0 auto;">
              <label class="checkbox-label-fim">
                <input type="checkbox" name="recursive" value="1" checked>
                <span>Recursive Subfolders</span>
              </label>
            </div>

            <button type="submit" class="btn-cyan" style="height: 38px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              <span>Add Monitored Folder</span>
            </button>
          </form>
        <?php endif; ?>

        <!-- Table of Monitored Paths -->
        <div class="table-responsive">
          <table class="soc-table" aria-label="Monitored Folders Table">
            <thead>
              <tr>
                <th style="width: 70px;">Path ID</th>
                <th>Monitored Folder Path</th>
                <th>Description</th>
                <th style="width: 100px;">Mode</th>
                <th style="width: 110px;">Tracked Files</th>
                <th style="width: 110px;">Status</th>
                <?php if ($isAdmin): ?>
                  <th style="width: 170px; text-align: right;">Administrator Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pathsList)): ?>
                <tr>
                  <td colspan="<?php echo $isAdmin ? '7' : '6'; ?>" style="text-align: center; color: var(--text-muted); padding: 24px;">
                    No monitored folders registered. Use the form above to add your first protected directory.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($pathsList as $p): ?>
                  <tr>
                    <td class="font-mono">#<?php echo $p['id']; ?></td>
                    <td style="font-weight: 600; color: #ffffff;">
                      <?php echo htmlspecialchars($p['path']); ?>
                      <?php if (!is_dir($p['path'])): ?>
                        <span style="font-size: 10px; color: #f87171; display: block;">(Directory missing on disk)</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-muted"><?php echo htmlspecialchars($p['description'] ?: '—'); ?></td>
                    <td>
                      <span class="fim-badge <?php echo $p['recursive'] ? 'badge-create' : 'badge-modify'; ?>">
                        <?php echo $p['recursive'] ? 'Recursive' : 'Top Level Only'; ?>
                      </span>
                    </td>
                    <td class="font-mono"><?php echo number_format($p['files_count']); ?> files</td>
                    <td>
                      <span class="status-pill">
                        <span class="status-dot <?php echo $p['is_active'] ? 'dot-resolved' : 'dot-investigating'; ?>"></span>
                        <span><?php echo $p['is_active'] ? 'Active' : 'Disabled'; ?></span>
                      </span>
                    </td>
                    <?php if ($isAdmin): ?>
                      <td style="text-align: right;">
                        <!-- Toggle Button -->
                        <form method="POST" style="display: inline; margin: 0;">
                          <input type="hidden" name="action" value="toggle_folder">
                          <input type="hidden" name="path_id" value="<?php echo $p['id']; ?>">
                          <input type="hidden" name="new_status" value="<?php echo $p['is_active'] ? '0' : '1'; ?>">
                          <button type="submit" class="action-btn-sm <?php echo $p['is_active'] ? 'action-btn-danger' : 'action-btn-success'; ?>" title="<?php echo $p['is_active'] ? 'Disable monitoring' : 'Enable monitoring'; ?>">
                            <?php echo $p['is_active'] ? 'Disable' : 'Enable'; ?>
                          </button>
                        </form>

                        <!-- Remove Button -->
                        <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Remove folder and all its tracked file records?');">
                          <input type="hidden" name="action" value="remove_folder">
                          <input type="hidden" name="path_id" value="<?php echo $p['id']; ?>">
                          <button type="submit" class="action-btn-sm action-btn-danger" title="Remove monitored folder">
                            Remove
                          </button>
                        </form>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- CLI Service Quick Start Note -->
        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-card); font-size: 12px; color: var(--text-muted);">
          <span><strong>CLI Monitoring Runner:</strong> Run <code>fim_monitor.bat</code> or <code>php service/fim_monitor.php</code> in terminal to start the live file observer.</span>
          <div class="cli-cmd-box">
            <span>&gt; C:\xampp\Final21\fim_monitor.bat</span>
            <span style="color: #64748b; font-size: 11px;">Starts background CLI loop</span>
          </div>
        </div>
      </div>

      <!-- ==================================================================
           SECTION 2: RECENT FILE ACTIVITY LOGS (AUDIT STREAM)
           ================================================================== -->
      <div class="soc-card table-card" style="margin-bottom: 24px;">
        <div class="soc-card-header">
          <span class="soc-card-title">Recent File Activity Telemetry</span>
          <span class="text-muted" style="font-size: 11px;">Live stream of file creation, modification, deletion, and rename events.</span>
        </div>

        <div class="table-responsive">
          <table class="soc-table" aria-label="Recent File Activity Logs">
            <thead>
              <tr>
                <th style="width: 80px;">Log ID</th>
                <th style="width: 110px;">Event Type</th>
                <th>File Path</th>
                <th style="width: 180px;">Cryptographic Hash (SHA-256)</th>
                <th style="width: 100px;">Severity</th>
                <th style="width: 150px;">Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($activityLogs)): ?>
                <tr>
                  <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                    No file events detected yet. Start the CLI service and create/modify files in a monitored directory to see telemetry.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($activityLogs as $log): ?>
                  <?php
                    $evt = strtoupper($log['event_type']);
                    $badgeClass = 'badge-modify';
                    if ($evt === 'CREATE') $badgeClass = 'badge-create';
                    elseif ($evt === 'DELETE') $badgeClass = 'badge-delete';
                    elseif ($evt === 'RENAME') $badgeClass = 'badge-rename';

                    $sev = strtolower($log['severity']);
                    $sevBadge = 'badge-low';
                    if ($sev === 'critical') $sevBadge = 'badge-critical';
                    elseif ($sev === 'high') $sevBadge = 'badge-high';
                    elseif ($sev === 'medium') $sevBadge = 'badge-medium';
                  ?>
                  <tr>
                    <td class="font-mono">#<?php echo $log['id']; ?></td>
                    <td><span class="fim-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($evt); ?></span></td>
                    <td style="font-size: 12.5px; color: #ffffff;"><?php echo htmlspecialchars($log['file_path']); ?></td>
                    <td>
                      <span class="hash-mono" title="<?php echo htmlspecialchars($log['new_hash'] ?: ($log['previous_hash'] ?: 'None')); ?>">
                        <?php echo htmlspecialchars($log['new_hash'] ?: ($log['previous_hash'] ?: '—')); ?>
                      </span>
                    </td>
                    <td><span class="badge-severity <?php echo $sevBadge; ?>"><?php echo strtoupper($log['severity']); ?></span></td>
                    <td class="font-mono text-muted"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================================================================
           SECTION 3: MONITORED FILES INVENTORY
           ================================================================== -->
      <div class="soc-card table-card">
        <div class="soc-card-header">
          <span class="soc-card-title">Monitored Files Catalog (Top 50)</span>
          <span class="text-muted" style="font-size: 11px;">Tracked file inventory with cryptographic SHA-256 integrity hashes.</span>
        </div>

        <div class="table-responsive">
          <table class="soc-table" aria-label="Monitored Files Table">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th>File Name & Path</th>
                <th style="width: 100px;">Size</th>
                <th style="width: 220px;">SHA-256 Cryptographic Hash</th>
                <th style="width: 110px;">Status</th>
                <th style="width: 150px;">Last Scanned</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filesList)): ?>
                <tr>
                  <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                    No files cataloged yet. Run the FIM background CLI service to index files in active folders.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($filesList as $f): ?>
                  <tr>
                    <td class="font-mono">#<?php echo $f['id']; ?></td>
                    <td>
                      <div style="font-weight: 600; color: #ffffff; font-size: 13px;"><?php echo htmlspecialchars($f['file_name']); ?></div>
                      <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($f['file_path']); ?></div>
                    </td>
                    <td class="font-mono"><?php echo number_format($f['file_size']); ?> B</td>
                    <td>
                      <span class="hash-mono" title="<?php echo htmlspecialchars($f['current_hash']); ?>">
                        <?php echo htmlspecialchars($f['current_hash']); ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-pill">
                        <span class="status-dot <?php echo $f['status'] === 'normal' ? 'dot-resolved' : ($f['status'] === 'modified' ? 'dot-investigating' : 'dot-open'); ?>"></span>
                        <span><?php echo ucfirst($f['status']); ?></span>
                      </span>
                    </td>
                    <td class="font-mono text-muted"><?php echo htmlspecialchars($f['last_scanned_at'] ?: '—'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>

  </div>

</body>
</html>
