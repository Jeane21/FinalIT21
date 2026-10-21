<?php
/**
 * RansomGuard - Authentication Middleware & Session Management
 * Enforces role-based access control, session timeouts, and password verification.
 */

// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Session Timeout: 30 minutes of inactivity (1800 seconds)
if (!defined('SESSION_TIMEOUT_SECONDS')) {
    define('SESSION_TIMEOUT_SECONDS', 1800);
}

// -----------------------------------------------------------------------------
// Secure Session Initialization
// -----------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie attributes prior to starting the session
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0, // Until browser closes
        'path'     => $cookieParams['path'] ?: '/',
        'domain'   => $cookieParams['domain'] ?: '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true, // Mitigate XSS cookie theft
        'samesite' => 'Lax' // Mitigate CSRF
    ]);
    session_start();
}

// -----------------------------------------------------------------------------
// Enforce Session Inactivity Timeout
// -----------------------------------------------------------------------------
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
        // Inactivity timeout exceeded - terminate session
        logout('timeout');
        exit;
    }
    // Update last activity timestamp on each active request
    $_SESSION['last_activity'] = time();
}

// -----------------------------------------------------------------------------
// Authentication Helper Functions
// -----------------------------------------------------------------------------

/**
 * Check if a user is currently authenticated.
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !empty($_SESSION['user_id']);
}

/**
 * Retrieve the currently authenticated user's session payload.
 *
 * @return array|null
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
        'role'      => $_SESSION['role'] ?? '',
    ];
}

/**
 * Middleware: Enforces that the current visitor must be logged in.
 * If unauthenticated, redirects to the login page immediately.
 *
 * @param string $loginUrl
 * @return void
 */
function require_auth($loginUrl = 'dashboard.html') {
    if (!is_logged_in()) {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $redirectParam = !empty($currentUri) && strpos($currentUri, 'logout.php') === false 
            ? '?redirect=' . urlencode($currentUri) 
            : '';
        header('Location: ' . $loginUrl . $redirectParam);
        exit;
    }
}

/**
 * Check if the authenticated user has a specific role or one of multiple roles.
 *
 * Roles supported:
 * - 'Administrator'
 * - 'Security Analyst'
 *
 * @param string|array $roles
 * @return bool
 */
function has_role($roles) {
    if (!is_logged_in()) {
        return false;
    }
    $userRole = $_SESSION['role'] ?? '';
    if (is_array($roles)) {
        return in_array($userRole, $roles, true);
    }
    return $userRole === $roles;
}

/**
 * Check if user has Administrator privileges (full system management).
 *
 * @return bool
 */
function is_admin() {
    return has_role('Administrator');
}

/**
 * Check if user has Security Analyst privileges (monitoring, detections, alerts, reports).
 *
 * @return bool
 */
function is_analyst() {
    return has_role(['Administrator', 'Security Analyst']);
}

/**
 * Middleware: Enforces specific role access.
 * If unauthorized, returns HTTP 403 Forbidden or redirects.
 *
 * @param string|array $requiredRoles
 * @param string $fallbackUrl
 * @return void
 */
function require_role($requiredRoles, $fallbackUrl = 'dashboard.html') {
    require_auth();

    if (!has_role($requiredRoles)) {
        http_response_code(403);
        die('
            <div style="font-family: sans-serif; padding: 32px; max-width: 600px; margin: 50px auto; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; color: #9f1239;">
                <h2 style="margin-top:0;">403 - Access Forbidden</h2>
                <p>Your current role (<strong>' . htmlspecialchars($_SESSION['role'] ?? 'None') . '</strong>) does not have permission to access this module.</p>
                <p><a href="' . htmlspecialchars($fallbackUrl) . '" style="color: #2563eb; font-weight: bold;">&larr; Return to Dashboard</a></p>
            </div>
        ');
    }
}

// -----------------------------------------------------------------------------
// Authentication Actions (Login & Logout)
// -----------------------------------------------------------------------------

/**
 * Attempt user authentication using username/email and password.
 * Enforces status checking and password_verify().
 *
 * @param string $usernameOrEmail
 * @param string $password
 * @return array ['success' => bool, 'error' => string|null, 'user' => array|null]
 */
function attempt_login($usernameOrEmail, $password) {
    $usernameOrEmail = trim($usernameOrEmail);
    $password = trim($password);

    if (empty($usernameOrEmail) || empty($password)) {
        return [
            'success' => false,
            'error'   => 'Please provide both username/email and password.',
            'user'    => null
        ];
    }

    try {
        $pdo = get_db_connection();

        // Query user by username OR email
        $stmt = $pdo->prepare('
            SELECT id, full_name, username, email, password_hash, role, status
            FROM users
            WHERE username = :uname OR email = :uemail
            LIMIT 1
        ');
        $stmt->execute([
            ':uname'  => $usernameOrEmail,
            ':uemail' => $usernameOrEmail
        ]);
        $user = $stmt->fetch();

        // 1. Verify user exists and verify password hash
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'error'   => 'Invalid username or password.',
                'user'    => null
            ];
        }

        // 2. Check Account Status
        if ($user['status'] === 'suspended') {
            return [
                'success' => false,
                'error'   => 'Your account has been suspended. Please contact an Administrator.',
                'user'    => null
            ];
        }

        if ($user['status'] === 'inactive') {
            return [
                'success' => false,
                'error'   => 'Your account is currently inactive. Please contact an Administrator.',
                'user'    => null
            ];
        }

        // 3. Prevent Session Fixation by regenerating session ID
        session_regenerate_id(true);

        // 4. Populate Session Payload
        $_SESSION['user_id']       = (int)$user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['email']         = $user['email'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['logged_in']     = true;
        $_SESSION['last_activity'] = time();

        // 5. Update last_login timestamp in database
        $updateStmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = :id');
        $updateStmt->execute([':id' => $user['id']]);

        // 6. Log system activity
        log_system_activity(
            $user['id'],
            'USER_LOGIN',
            'Authentication',
            'User ' . $user['username'] . ' (' . $user['role'] . ') successfully logged in.'
        );

        return [
            'success' => true,
            'error'   => null,
            'user'    => $user
        ];

    } catch (Exception $e) {
        error_log('Login processing error: ' . $e->getMessage());
        return [
            'success' => false,
            'error'   => 'An internal system error occurred. Please try again.',
            'user'    => null
        ];
    }
}

/**
 * Terminate user session and redirect to the dashboard.
 *
 * @param string|null $reason Reason code: 'logout', 'timeout'
 * @param string $loginUrl
 * @return void
 */
function logout($reason = 'logout', $loginUrl = 'dashboard.html') {
    // Log user logout if previously authenticated
    if (isset($_SESSION['user_id'])) {
        log_system_activity(
            $_SESSION['user_id'],
            'USER_LOGOUT',
            'Authentication',
            'User logged out (Reason: ' . ($reason ?: 'voluntary') . ').'
        );
    }

    // Unset all session variables
    $_SESSION = [];

    // Delete session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Destroy session storage
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // Redirect with reason parameter
    $query = $reason === 'timeout' ? '?error=timeout' : '?logged_out=1';
    header('Location: ' . $loginUrl . $query);
    exit;
}

/**
 * Helper to record administrative/user action to system_activity_logs.
 *
 * @param int|null $userId
 * @param string $action
 * @param string $module
 * @param string $details
 * @return void
 */
function log_system_activity($userId, $action, $module, $details = '') {
    try {
        $pdo = get_db_connection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $stmt = $pdo->prepare('
            INSERT INTO system_activity_logs (user_id, action, module, ip_address, user_agent, details, timestamp)
            VALUES (:uid, :act, :mod, :ip, :ua, :details, NOW())
        ');
        $stmt->execute([
            ':uid'     => $userId,
            ':act'     => $action,
            ':mod'     => $module,
            ':ip'      => $ip,
            ':ua'      => $agent,
            ':details' => $details
        ]);
    } catch (Exception $e) {
        // Non-blocking log failure
        error_log('Failed to log system activity: ' . $e->getMessage());
    }
}
