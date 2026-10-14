<?php
/**
 * StudentHub - Feedback Processor (Practical 7)
 * Appends student feedback to data/feedback.csv
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method Not Allowed.");
}

$name     = sanitizeInput($_POST['name'] ?? 'Anonymous Student');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$category = sanitizeInput($_POST['category'] ?? 'General');
$rating   = intval($_POST['rating'] ?? 5);
$comments = sanitizeInput($_POST['comments'] ?? '');

$timestamp = date("Y-m-d H:i:s");
$csvFile = DATA_PATH . DIRECTORY_SEPARATOR . 'feedback.csv';

$csvLine = sprintf(
    "\"%s\",\"%s\",\"%s\",\"%s\",%d,\"%s\"\n",
    $timestamp,
    addslashes($name),
    addslashes($email),
    addslashes($category),
    $rating,
    addslashes($comments)
);

@file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Received - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">
    <div class="form-card" style="text-align: center; max-width: 500px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🌟</div>
        <h2 style="color: var(--success); margin-bottom: 10px;">Feedback Received!</h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 20px;">
            Thank you for sharing your thoughts on <strong><?= htmlspecialchars($category) ?></strong>. 
            Your response has been securely archived in <code>data/feedback.csv</code>.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <a href="../pages/feedback.html" class="btn btn-secondary">Submit Another</a>
            <a href="../pages/dashboard.html" class="btn btn-primary">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
