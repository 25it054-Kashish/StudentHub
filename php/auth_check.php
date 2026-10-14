<?php
/**
 * StudentHub - Authentication, RBAC & Session Timeout Middleware (Practical 10)
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Check if the user is authenticated, handling session timeout & remember-me token
 */
function checkAuth(): bool {
    // 1. Check Session Inactivity Timeout (15 minutes)
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
            // Session expired due to inactivity
            logAudit($_SESSION['user_id'] ?? null, 'SESSION_TIMEOUT', 'Session expired after 15 minutes of inactivity.');
            destroySession();
            header("Location: ../pages/login.html?timeout=1");
            exit;
        }
        $_SESSION['last_activity'] = time(); // Reset activity timestamp
        return true;
    }

    // 2. Check Remember-Me Cookie (Practical 10 Advanced)
    if (!empty($_COOKIE['studenthub_remember'])) {
        $token = $_COOKIE['studenthub_remember'];
        $pdo = Database::getPDO();

        if ($pdo) {
            $stmt = $pdo->prepare("
                SELECT r.user_id, u.email, u.role, u.username, u.status 
                FROM remember_tokens r
                JOIN users u ON r.user_id = u.id
                WHERE r.expires_at > NOW()
            ");
            $stmt->execute();
            $tokens = $stmt->fetchAll();

            foreach ($tokens as $row) {
                // Verify token using constant-time hash comparison
                if (password_verify($token, $row['token_hash'] ?? '')) {
                    if ($row['status'] === 'active') {
                        // Re-establish session
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['last_activity'] = time();
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Require specific role (e.g. 'admin' or 'student')
 */
function requireRole(string $requiredRole): void {
    if (!checkAuth()) {
        header("Location: ../pages/login.html?auth=required");
        exit;
    }

    if ($_SESSION['role'] !== $requiredRole) {
        logAudit($_SESSION['user_id'], 'UNAUTHORIZED_ACCESS_ATTEMPT', "Attempted to access {$requiredRole} restricted resource.");
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>Access Forbidden</title><link rel='stylesheet' href='../css/style.css'></head>
        <body style='text-align: center; padding: 60px;'>
            <h1 style='color: var(--danger);'>403 - Access Forbidden</h1>
            <p>Your current role (<strong>" . htmlspecialchars($_SESSION['role']) . "</strong>) does not have privileges to access this administrative portal.</p>
            <a href='../pages/dashboard.html' class='btn btn-primary' style='margin-top: 15px;'>Return to Dashboard</a>
        </body></html>";
        exit;
    }
}

/**
 * Helper to record actions in audit_logs (Practical 14)
 */
function logAudit(?int $userId, string $action, string $details = ''): void {
    try {
        $pdo = Database::getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, details, ip_address, user_agent)
                VALUES (:uid, :act, :det, :ip, :ua)
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':act' => $action,
                ':det' => $details,
                ':ip'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ':ua'  => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250)
            ]);
        }
    } catch (Exception $e) {
        // Silently continue if audit log table not initialized yet
    }
}

/**
 * Safely destroy session and clear remember cookie
 */
function destroySession(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Clear remember-me cookie
    if (isset($_COOKIE['studenthub_remember'])) {
        setcookie('studenthub_remember', '', time() - 3600, '/');
    }
}
