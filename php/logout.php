<?php
/**
 * StudentHub - Logout Handler (Practical 10)
 * Destroys session, deletes remember-me cookies/tokens, logs audit event
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    logAudit($userId, 'USER_LOGOUT', 'User logged out securely.');
}

// Destroy session and clean tokens
destroySession();

header("Location: ../pages/login.html?logout=1");
exit;
