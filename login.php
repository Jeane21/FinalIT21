<?php
/**
 * RansomGuard - Security & File Integrity Defense Platform
 * Login Page
 * Reuses existing theme.css and dashboard.css
 */

require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect straight to the dashboard
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';
$successMessage = '';

// Handle timeout parameter
if (isset($_GET['error']) && $_GET['error'] === 'timeout') {
    $errorMessage = 'Your session has timed out due to inactivity. Please log in again.';
} elseif (isset($_GET['logged_out'])) {
    $successMessage = 'You have been safely logged out.';
}

// Destination after login
$redirectTarget = !empty($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['username'] ?? '';
    $password        = $_POST['password'] ?? '';
    $target          = $_POST['redirect_target'] ?? 'index.php';

    $result = attempt_login($usernameOrEmail, $password);

    if ($result['success']) {
        // Safe redirect to target or dashboard
        $safeTarget = (strpos($target, 'http') === false && strpos($target, '//') === false) 
            ? $target 
            : 'index.php';
        header('Location: ' . $safeTarget);
        exit;
    } else {
        $errorMessage = $result['error'] ?? 'Invalid authentication credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RansomGuard - Security Platform Login</title>
  <meta name="description" content="Sign in to the RansomGuard Endpoint & File Integrity Defense Platform.">

  <!-- Reusing existing application theme and dashboard styling -->
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
    /* Scoped login page layout that harmonizes with theme.css */
    body.login-body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      margin: 0;
      background-color: var(--bg-body, var(--body-bg, #0b132b));
      color: var(--text-primary, var(--text-color, #e0e6ed));
      font-family: var(--font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
      position: relative;
      overflow-x: hidden;
    }

    /* Ambient cyber grid backdrop */
    .cyber-backdrop {
      position: fixed;
      inset: 0;
      pointer-events: none;
      background-image: 
        radial-gradient(circle at 50% 15%, rgba(37, 99, 235, 0.15) 0%, transparent 60%),
        radial-gradient(circle, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
      background-size: 100% 100%, 32px 32px;
      z-index: 0;
    }

    .login-container {
      width: 100%;
      max-width: 440px;
      position: relative;
      z-index: 1;
      animation: fadeInScale 0.35s ease-out;
    }

    .login-card {
      background: var(--card-bg, var(--bg-card, #1c2541));
      border: 1px solid var(--border-color, var(--card-border, rgba(255, 255, 255, 0.1)));
      border-radius: var(--border-radius-lg, var(--card-radius, 16px));
      padding: 40px 36px 32px;
      box-shadow: var(--card-shadow, 0 20px 45px -10px rgba(0, 0, 0, 0.5));
      backdrop-filter: blur(12px);
    }

    /* Header & Branding */
    .brand-header {
      text-align: center;
      margin-bottom: 28px;
    }

    .brand-icon-wrapper {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 58px;
      height: 58px;
      border-radius: 14px;
      background: var(--accent-light, rgba(37, 99, 235, 0.18));
      color: var(--accent-color, var(--primary, #3b82f6));
      margin-bottom: 14px;
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
    }

    .brand-icon-wrapper svg {
      width: 30px;
      height: 30px;
    }

    .brand-title {
      font-size: 26px;
      font-weight: 800;
      color: var(--text-heading, #ffffff);
      margin: 0 0 6px 0;
      letter-spacing: -0.02em;
    }

    .brand-tagline {
      font-size: 13px;
      color: var(--text-muted, #94a3b8);
      margin: 0;
    }

    /* System Status Pill */
    .system-status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 10px;
      padding: 3px 10px;
      border-radius: 9999px;
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.3);
      color: #34d399;
      font-size: 11px;
      font-weight: 600;
    }

    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 8px #10b981;
    }

    /* Alerts */
    .alert-banner {
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
      line-height: 1.4;
    }

    .alert-banner.error {
      background-color: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #fca5a5;
    }

    .alert-banner.success {
      background-color: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #6ee7b7;
    }

    .alert-icon {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }

    /* Form Fields */
    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--text-secondary, #cbd5e1);
      margin-bottom: 7px;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      width: 18px;
      height: 18px;
      color: var(--text-muted, #64748b);
      pointer-events: none;
      transition: color 0.15s ease;
    }

    .form-input {
      width: 100%;
      height: 46px;
      padding: 0 14px 0 42px;
      background: var(--input-bg, rgba(255, 255, 255, 0.05));
      border: 1px solid var(--border-color, rgba(255, 255, 255, 0.15));
      border-radius: var(--border-radius-md, 8px);
      color: #ffffff;
      font-size: 14px;
      font-family: inherit;
      box-sizing: border-box;
      outline: none;
      transition: all 0.15s ease;
    }

    .form-input:focus {
      border-color: var(--accent-color, #3b82f6);
      background: var(--input-bg-focus, rgba(255, 255, 255, 0.08));
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }

    .input-wrapper:focus-within .input-icon {
      color: var(--accent-color, #3b82f6);
    }

    /* Toggle Password Button */
    .password-toggle-btn {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      color: var(--text-muted, #64748b);
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .password-toggle-btn:hover {
      color: #ffffff;
    }

    /* Submit Button */
    .btn-login {
      width: 100%;
      height: 48px;
      background: var(--accent-color, var(--primary, #2563eb));
      color: #ffffff;
      border: none;
      border-radius: var(--border-radius-md, 8px);
      font-size: 14.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 6px;
      box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
      transition: all 0.15s ease;
    }

    .btn-login:hover {
      background: var(--accent-hover, #1d4ed8);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    /* Card Footer & Role Notice */
    .card-footer {
      margin-top: 22px;
      padding-top: 16px;
      border-top: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
      text-align: center;
      font-size: 11.5px;
      color: var(--text-muted, #64748b);
      line-height: 1.5;
    }

    .role-tags {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 6px;
    }

    .role-pill {
      font-size: 10.5px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 9999px;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #cbd5e1;
    }

    @keyframes fadeInScale {
      from { opacity: 0; transform: scale(0.97); }
      to { opacity: 1; transform: scale(1); }
    }
  </style>
</head>
<body class="login-body">

  <div class="cyber-backdrop" aria-hidden="true"></div>

  <div class="login-container">
    <div class="login-card">

      <!-- Branding Header -->
      <header class="brand-header">
        <div class="brand-icon-wrapper" aria-hidden="true">
          <!-- Cybersecurity Shield SVG -->
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="m9 12 2 2 4-4"/>
          </svg>
        </div>
        <h1 class="brand-title">RansomGuard</h1>
        <p class="brand-tagline">Endpoint & File Integrity Defense Platform</p>
        <div class="system-status-pill">
          <span class="status-dot"></span>
          <span>Authentication Gateway Active</span>
        </div>
      </header>

      <!-- Alert Banners -->
      <?php if (!empty($errorMessage)): ?>
        <div class="alert-banner error" role="alert">
          <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <span><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMessage)): ?>
        <div class="alert-banner success" role="status">
          <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="login.php" method="POST" autocomplete="on">
        <input type="hidden" name="redirect_target" value="<?php echo htmlspecialchars($redirectTarget); ?>">

        <!-- Username / Email -->
        <div class="form-group">
          <label for="username" class="form-label">Username or Email</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input" 
              placeholder="admin or user@domain.com" 
              required 
              autofocus 
              autocomplete="username"
            >
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              placeholder="••••••••••••" 
              required 
              autocomplete="current-password"
            >
            <button 
              type="button" 
              id="toggle-password" 
              class="password-toggle-btn" 
              aria-label="Toggle password visibility" 
              title="Show / Hide Password"
            >
              <svg id="eye-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn-login">
          <span>Sign In to RansomGuard</span>
          <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>

      </form>

      <!-- Role-Based Authorization Notice -->
      <footer class="card-footer">
        <div>Authorized Access Only &bull; Role-Enforced Security</div>
        <div class="role-tags">
          <span class="role-pill">Administrator</span>
          <span class="role-pill">Security Analyst</span>
        </div>
      </footer>

    </div>
  </div>

  <script>
    // Password visibility toggle
    document.getElementById('toggle-password').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const isPassword = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
      this.innerHTML = isPassword 
        ? `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    });
  </script>
</body>
</html>
