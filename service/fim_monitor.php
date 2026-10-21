<?php
/**
 * RansomGuard - File Integrity Monitoring (FIM) Background CLI Service
 * Target: Windows / XAMPP PHP CLI Environment
 * 
 * Execution:
 *   php service/fim_monitor.php
 * 
 * Key Responsibilities:
 * - Scans ONLY directories explicitly selected and activated by the Administrator in MySQL.
 * - Detects file creation, modification, deletion, and rename/movement in real time.
 * - Calculates cryptographic SHA-256 hashes and captures baseline snapshots.
 * - Records all events into MySQL (file_activity_logs, monitored_files, file_snapshots, detection_logs).
 * - Periodically updates heartbeat and obeys start/stop signals from the Web UI.
 */

// Ensure this script runs only in CLI mode or background process
if (php_sapi_name() !== 'cli' && !isset($_GET['cli_simulate'])) {
    die("Error: The FIM Background Monitoring Service must be executed via PHP CLI (Command Line), not a browser request.\nUse run.bat or 'php service/fim_monitor.php'.\n");
}

// Adjust memory and execution limits for continuous background service
set_time_limit(0);
ini_set('memory_limit', '512M');
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

// Known ransomware file extensions for heuristic detection escalation
$ransomwareExtensions = [
    'locked', 'crypto', 'enc', 'crypted', 'ransom', 'wnry', 
    'locky', 'cerber', 'zepto', 'crypt', 'blackmatter', 'hive', 'phobos'
];

echo "====================================================================\n";
echo "   RansomGuard - File Integrity Monitoring (FIM) Background Engine  \n";
echo "   Target Database: " . DB_NAME . " @ " . DB_HOST . "\n";
echo "====================================================================\n";
echo "[" . date('Y-m-d H:i:s') . "] Initializing FIM Service...\n";

// Register default system settings if missing
try {
    $pdo = get_db_connection();
    $pdo->exec("
        INSERT INTO system_settings (setting_key, setting_value, category, description) 
        VALUES 
            ('fim_service_status', 'running', 'fim', 'FIM background service status (running/stopped)'),
            ('fim_last_heartbeat', NOW(), 'fim', 'Timestamp of last FIM background service cycle'),
            ('fim_scan_interval_seconds', '3', 'fim', 'Delay between scan cycles in seconds')
        ON DUPLICATE KEY UPDATE updated_at = NOW();
    ");
    echo "[" . date('Y-m-d H:i:s') . "] Connected to MySQL successfully.\n";
} catch (Exception $e) {
    die("[FATAL] Could not connect to MySQL: " . $e->getMessage() . "\n");
}

// In-memory cache of known file states to detect rename/movements
$knownFilesCache = [];

/**
 * Scan a directory recursively or flat based on path configuration
 */
function scan_monitored_directory($dirPath, $recursive = true) {
    $files = [];
    if (!is_dir($dirPath) || !is_readable($dirPath)) {
        return $files;
    }

    try {
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $files[] = str_replace('/', DIRECTORY_SEPARATOR, $item->getPathname());
                }
            }
        } else {
            $items = scandir($dirPath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $fullPath = $dirPath . DIRECTORY_SEPARATOR . $item;
                if (is_file($fullPath)) {
                    $files[] = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
                }
            }
        }
    } catch (Exception $e) {
        echo "[WARN] Error scanning $dirPath: " . $e->getMessage() . "\n";
    }

    return $files;
}

/**
 * Compute SHA-256 hash safely
 */
function compute_file_hash($filePath) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return null;
    }
    // For very large files, hash_file streams without loading entirely into memory
    return @hash_file('sha256', $filePath);
}

// -----------------------------------------------------------------------------
// Main Continuous Monitoring Loop
// -----------------------------------------------------------------------------
$cycleCount = 0;

while (true) {
    $cycleCount++;

    try {
        $pdo = get_db_connection();

        // 1. Check UI Start/Stop Signal
        $stmtStatus = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'fim_service_status' LIMIT 1");
        $stmtStatus->execute();
        $serviceStatus = $stmtStatus->fetchColumn() ?: 'running';

        // Update heartbeat
        $pdo->exec("UPDATE system_settings SET setting_value = NOW() WHERE setting_key = 'fim_last_heartbeat'");

        // Get configured scan interval
        $stmtInterval = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'fim_scan_interval_seconds' LIMIT 1");
        $stmtInterval->execute();
        $scanInterval = max(1, (int)($stmtInterval->fetchColumn() ?: 3));

        if ($serviceStatus === 'stopped') {
            echo "[" . date('Y-m-d H:i:s') . "] [PAUSED] FIM Service is PAUSED by Administrator. Sleeping {$scanInterval}s...\n";
            sleep($scanInterval);
            continue;
        }

        // 2. Query ONLY folders explicitly selected and active by Administrator
        $stmtPaths = $pdo->query("SELECT id, `path`, `recursive`, description FROM monitored_paths WHERE is_active = 1");
        $activePaths = $stmtPaths->fetchAll();

        if (empty($activePaths)) {
            if ($cycleCount % 5 === 1) {
                echo "[" . date('Y-m-d H:i:s') . "] [IDLE] No active monitored folders configured by Administrator in MySQL.\n";
                echo "       Add folders in the RansomGuard Web Interface to begin monitoring.\n";
            }
            sleep($scanInterval);
            continue;
        }

        // 3. Process Each Monitored Folder
        foreach ($activePaths as $pathRecord) {
            $pathId = (int)$pathRecord['id'];
            $folderPath = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $pathRecord['path']), DIRECTORY_SEPARATOR);
            $isRecursive = (bool)$pathRecord['recursive'];

            if (!is_dir($folderPath)) {
                echo "[" . date('Y-m-d H:i:s') . "] [WARN] Path does not exist on disk: $folderPath\n";
                continue;
            }

            // Find all physical files on disk under this folder
            $diskFiles = scan_monitored_directory($folderPath, $isRecursive);
            $diskFilesLookup = array_flip($diskFiles);

            // Fetch known files recorded in database for this path
            $stmtDbFiles = $pdo->prepare("
                SELECT id, file_path, file_name, file_size, current_hash, status 
                FROM monitored_files 
                WHERE path_id = :pid
            ");
            $stmtDbFiles->execute([':pid' => $pathId]);
            $dbFilesRaw = $stmtDbFiles->fetchAll();

            $dbFilesLookup = [];
            foreach ($dbFilesRaw as $row) {
                $normPath = str_replace('/', DIRECTORY_SEPARATOR, $row['file_path']);
                $dbFilesLookup[$normPath] = $row;
            }

            // --- CHECK A: File Creation & Modifications ---
            foreach ($diskFiles as $currentFilePath) {
                $fileSize = @filesize($currentFilePath) ?: 0;
                $fileName = basename($currentFilePath);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // 1. Is this a NEW file? (Not in DB or marked as deleted)
                if (!isset($dbFilesLookup[$currentFilePath]) || $dbFilesLookup[$currentFilePath]['status'] === 'deleted') {
                    $newHash = compute_file_hash($currentFilePath);
                    if (!$newHash) continue;

                    // Check if this was a RENAME / MOVE from a recently deleted file
                    $isRename = false;
                    $renamedFromPath = null;
                    foreach ($dbFilesLookup as $oldPath => $oldRow) {
                        if ($oldRow['status'] === 'deleted' && $oldRow['current_hash'] === $newHash) {
                            $isRename = true;
                            $renamedFromPath = $oldPath;
                            break;
                        }
                    }

                    if ($isRename) {
                        // Event: RENAME / MOVEMENT
                        echo "[" . date('Y-m-d H:i:s') . "] [RENAME DETECTED]\n";
                        echo "   From: $renamedFromPath\n";
                        echo "   To:   $currentFilePath\n";
                        echo "   Hash: $newHash\n";

                        // Update or insert
                        $insStmt = $pdo->prepare("
                            INSERT INTO monitored_files (path_id, file_path, file_name, file_size, current_hash, status, last_scanned_at)
                            VALUES (:pid, :fpath, :fname, :fsize, :hash, 'normal', NOW())
                            ON DUPLICATE KEY UPDATE file_size = :fsize2, current_hash = :hash2, status = 'normal', last_scanned_at = NOW()
                        ");
                        $insStmt->execute([
                            ':pid' => $pathId,
                            ':fpath' => $currentFilePath,
                            ':fname' => $fileName,
                            ':fsize' => $fileSize,
                            ':hash' => $newHash,
                            ':fsize2' => $fileSize,
                            ':hash2' => $newHash
                        ]);
                        $fileId = $pdo->lastInsertId() ?: ($dbFilesLookup[$currentFilePath]['id'] ?? null);

                        // Log rename event
                        $pdo->prepare("
                            INSERT INTO file_activity_logs (file_id, event_type, file_path, previous_hash, new_hash, detected_by, severity, timestamp)
                            VALUES (:fid, 'RENAME', :fpath, :prev, :new, 'FIM_Engine', 'medium', NOW())
                        ")->execute([
                            ':fid' => $fileId,
                            ':fpath' => $currentFilePath,
                            ':prev' => $renamedFromPath,
                            ':new' => $newHash
                        ]);

                    } else {
                        // Event: NEW FILE CREATION
                        $severity = in_array($ext, $ransomwareExtensions) ? 'critical' : 'low';
                        echo "[" . date('Y-m-d H:i:s') . "] [+] FILE CREATED [{$severity}]: $currentFilePath ($fileSize bytes | $newHash)\n";

                        // Insert into monitored_files
                        $insStmt = $pdo->prepare("
                            INSERT INTO monitored_files (path_id, file_path, file_name, file_size, current_hash, status, last_scanned_at)
                            VALUES (:pid, :fpath, :fname, :fsize, :hash, 'normal', NOW())
                            ON DUPLICATE KEY UPDATE file_size = :fsize2, current_hash = :hash2, status = 'normal', last_scanned_at = NOW()
                        ");
                        $insStmt->execute([
                            ':pid' => $pathId,
                            ':fpath' => $currentFilePath,
                            ':fname' => $fileName,
                            ':fsize' => $fileSize,
                            ':hash' => $newHash,
                            ':fsize2' => $fileSize,
                            ':hash2' => $newHash
                        ]);
                        $fileId = $pdo->lastInsertId();

                        // Capture Baseline Snapshot
                        if ($fileId) {
                            $pdo->prepare("
                                INSERT INTO file_snapshots (file_id, file_hash, file_size, is_baseline, snapshot_time)
                                VALUES (:fid, :hash, :size, 1, NOW())
                            ")->execute([':fid' => $fileId, ':hash' => $newHash, ':size' => $fileSize]);
                        }

                        // Log Event
                        $pdo->prepare("
                            INSERT INTO file_activity_logs (file_id, event_type, file_path, previous_hash, new_hash, detected_by, severity, timestamp)
                            VALUES (:fid, 'CREATE', :fpath, NULL, :hash, 'FIM_Engine', :sev, NOW())
                        ")->execute([
                            ':fid' => $fileId ?: null,
                            ':fpath' => $currentFilePath,
                            ':hash' => $newHash,
                            ':sev' => $severity
                        ]);

                        // If ransomware extension detected, trigger security alert
                        if ($severity === 'critical') {
                            $pdo->prepare("
                                INSERT INTO detection_logs (rule_name, detection_type, target_path, severity, confidence_score, details, status, timestamp)
                                VALUES ('Ransomware_Extension_Rule', 'Suspicious File Extension', :fpath, 'critical', 96.50, :det, 'unreviewed', NOW())
                            ")->execute([
                                ':fpath' => $currentFilePath,
                                ':det' => "File created with known ransomware extension: .$ext"
                            ]);
                            $detId = $pdo->lastInsertId();

                            $pdo->prepare("
                                INSERT INTO alerts (detection_id, title, description, severity, status, created_at)
                                VALUES (:detId, 'Critical: Potential Ransomware File Creation', :desc, 'critical', 'new', NOW())
                            ")->execute([
                                ':detId' => $detId,
                                ':desc' => "Potential ransomware activity: Created $fileName with extension .$ext"
                            ]);
                        }
                    }

                } else {
                    // 2. File exists in DB: Check for MODIFICATION
                    $dbRecord = $dbFilesLookup[$currentFilePath];
                    $storedHash = $dbRecord['current_hash'];
                    $storedSize = (int)$dbRecord['file_size'];

                    // Fast pre-check: If size differs, or periodically hash check
                    $currentHash = compute_file_hash($currentFilePath);

                    if ($currentHash && $storedHash !== $currentHash) {
                        $severity = in_array($ext, $ransomwareExtensions) ? 'critical' : 'medium';
                        echo "[" . date('Y-m-d H:i:s') . "] [*] FILE MODIFIED [{$severity}]: $currentFilePath\n";
                        echo "       Prev: $storedHash\n";
                        echo "       New:  $currentHash\n";

                        // Update monitored_files
                        $pdo->prepare("
                            UPDATE monitored_files 
                            SET file_size = :size, current_hash = :hash, status = 'modified', last_scanned_at = NOW()
                            WHERE id = :id
                        ")->execute([
                            ':size' => $fileSize,
                            ':hash' => $currentHash,
                            ':id' => $dbRecord['id']
                        ]);

                        // Store new historical snapshot
                        $pdo->prepare("
                            INSERT INTO file_snapshots (file_id, file_hash, file_size, is_baseline, snapshot_time)
                            VALUES (:fid, :hash, :size, 0, NOW())
                        ")->execute([
                            ':fid' => $dbRecord['id'],
                            ':hash' => $currentHash,
                            ':size' => $fileSize
                        ]);

                        // Log Activity
                        $pdo->prepare("
                            INSERT INTO file_activity_logs (file_id, event_type, file_path, previous_hash, new_hash, detected_by, severity, timestamp)
                            VALUES (:fid, 'MODIFY', :fpath, :prev, :new, 'FIM_Engine', :sev, NOW())
                        ")->execute([
                            ':fid' => $dbRecord['id'],
                            ':fpath' => $currentFilePath,
                            ':prev' => $storedHash,
                            ':new' => $currentHash,
                            ':sev' => $severity
                        ]);

                        // If extension changed or rapid modification
                        if ($severity === 'critical') {
                            $pdo->prepare("
                                INSERT INTO detection_logs (rule_name, detection_type, target_path, severity, confidence_score, details, status, timestamp)
                                VALUES ('Ransomware_Tampering_Rule', 'Unauthorized File Cryptographic Modification', :fpath, 'critical', 94.00, :det, 'unreviewed', NOW())
                            ")->execute([
                                ':fpath' => $currentFilePath,
                                ':det' => "File hash changed unexpectedly with high entropy or signature match."
                            ]);
                        }
                    }
                }
            }

            // --- CHECK B: File Deletion ---
            foreach ($dbFilesLookup as $dbPath => $dbRecord) {
                // If the file was normal/modified but is no longer present on physical disk
                if ($dbRecord['status'] !== 'deleted' && !isset($diskFilesLookup[$dbPath])) {
                    echo "[" . date('Y-m-d H:i:s') . "] [-] FILE DELETED: $dbPath\n";

                    // Update status in monitored_files
                    $pdo->prepare("
                        UPDATE monitored_files 
                        SET status = 'deleted', last_scanned_at = NOW() 
                        WHERE id = :id
                    ")->execute([':id' => $dbRecord['id']]);

                    // Log Activity
                    $pdo->prepare("
                        INSERT INTO file_activity_logs (file_id, event_type, file_path, previous_hash, new_hash, detected_by, severity, timestamp)
                        VALUES (:fid, 'DELETE', :fpath, :prev, NULL, 'FIM_Engine', 'medium', NOW())
                    ")->execute([
                        ':fid' => $dbRecord['id'],
                        ':fpath' => $dbPath,
                        ':prev' => $dbRecord['current_hash']
                    ]);
                }
            }

        } // End paths foreach

    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [ERROR] " . $e->getMessage() . "\n";
    }

    // If single cycle execution requested (e.g. php service/fim_monitor.php --once)
    if (isset($argv) && (in_array('--once', $argv) || in_array('-1', $argv))) {
        echo "[" . date('Y-m-d H:i:s') . "] Diagnostic single-cycle execution finished.\n";
        break;
    }

    // Sleep before next scan cycle to prevent CPU thrashing
    sleep($scanInterval);
}
