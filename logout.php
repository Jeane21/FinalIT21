<?php
/**
 * RansomGuard - User Logout Handler
 */

require_once __DIR__ . '/includes/auth.php';

// Execute secure session termination and redirect to the dashboard.
logout('logout', 'dashboard.html');
