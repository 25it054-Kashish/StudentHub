<?php
/**
 * StudentHub - Secure Authentication Handler (Practical 10)
 * Uses password_verify(), session_regenerate_id(), Remember-Me Tokens & Role Redirection
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method Not Allowed.");
}

$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password   = $_POST['password'] ?? '';
$role       = sanitizeInput($_POST['role'] ?? 'student');
$rememberMe = isset($_POST['remember_me']);

$pdo = Database::getPDO();

// Fallback demo credentials if database connection is offline during local test
$mockUsers = [
    'admin@charusat.ac.in' => [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@charusat.ac.in',
        'password_hash' => '$2y$10$C0o.BH8iqvN95OAqwg14gODVFO.dSqphJJQIj94MqCikBvFeor43u', // Admin@123
        'role' => 'admin',
        'status' => 'active'
    ],
    'student@charusat.ac.in' => [
        'id' => 2,
        'username' => '23ce001',
        'email' => 'student@charusat.ac.in',
        'password_hash' => '$2y$10$NvRQ4bm52i4TxzbWLmBrfuiUEoQS7pz8BOPcVNDyhM0U0PUx5AOC6', // Student@123
        'role' => 'student',
        'status' => 'active'
    ]
];

$user = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("DB Auth Query Error: " . $e->getMessage());
    }
}

// Check mock store if DB didn't find or is offline
if (!$user && isset($mockUsers[$email])) {
    $user = $mockUsers[$email];
}

// Verify Password using constant-time password_verify (Practical 10)
if (!$user || !password_verify($password, $user['password_hash'])) {
    logAudit(null, 'LOGIN_FAILED', "Failed login attempt for email: {$email}");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login Failed - StudentHub</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="form-card" style="text-align: center; max-width: 440px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">❌</div>
            <h2 style="color: var(--danger); margin-bottom: 12px;">Authentication Failed</h2>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                Invalid email address or password. Please verify your credentials and try again.
            </p>
            <a href="../pages/login.html" class="btn btn-primary">Try Again</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 1. Session ID Regeneration to Prevent Session Fixation (Practical 10)
session_regenerate_id(true);

$_SESSION['user_id']       = $user['id'];
$_SESSION['username']      = $user['username'];
$_SESSION['email']         = $user['email'];
$_SESSION['role']          = $user['role'];
$_SESSION['last_activity'] = time();

// 2. Update Last Login Timestamp & Audit Log (Practical 10 Intermediate)
if ($pdo) {
    try {
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);

        // 3. Remember-Me Token Generation (Practical 10 Advanced)
        if ($rememberMe) {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = password_hash($rawToken, PASSWORD_BCRYPT);
            $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 Days

            $tokenStmt = $pdo->prepare("
                INSERT INTO remember_tokens (user_id, token_hash, expires_at)
                VALUES (:uid, :thash, :exp)
            ");
            $tokenStmt->execute([
                ':uid'   => $user['id'],
                ':thash' => $tokenHash,
                ':exp'   => $expiresAt
            ]);

            // Set secure HTTP-only cookie
            setcookie('studenthub_remember', $rawToken, time() + (86400 * 30), '/', '', false, true);
        }
    } catch (PDOException $e) {
        error_log("Login post-processing error: " . $e->getMessage());
    }
}

logAudit($user['id'], 'LOGIN_SUCCESS', "User successfully authenticated with role: {$user['role']}");

// 4. Role-Based Redirection (Practical 10)
if ($user['role'] === 'admin') {
    header("Location: ../pages/admin.html");
} else {
    header("Location: ../pages/dashboard.html");
}
exit;
