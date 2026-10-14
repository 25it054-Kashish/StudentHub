<?php
/**
 * StudentHub - Global Configuration & Security Utilities
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

// Report all errors in dev; can be suppressed in production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Database Connection Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub_db');

// Path Constants
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'data');
define('UPLOAD_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');

// Inactivity Session Timeout Limit (15 minutes in seconds - Practical 10)
define('SESSION_TIMEOUT_SECONDS', 900);

/* ==========================================================================
   CSRF Security Helpers (Practical 7 & 9 Advanced)
   ========================================================================== */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/* ==========================================================================
   Input Sanitization & Output Formatting
   ========================================================================== */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
