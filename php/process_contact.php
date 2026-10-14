<?php
/**
 * StudentHub - Contact Form Processor (Practical 7)
 * Sanitizes input, validates CSRF token, stores records in CSV & JSON
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method Not Allowed.");
}

$name       = sanitizeInput($_POST['name'] ?? '');
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$subject    = sanitizeInput($_POST['subject'] ?? '');
$department = sanitizeInput($_POST['department'] ?? 'General');
$message    = sanitizeInput($_POST['message'] ?? '');

$errors = [];

if (strlen($name) < 2) {
    $errors[] = "Please provide your full name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please provide a valid email address.";
}

if (strlen($subject) < 3) {
    $errors[] = "Subject must be at least 3 characters long.";
}

if (strlen($message) < 5) {
    $errors[] = "Message must be at least 5 characters long.";
}

if (!empty($errors)) {
    renderContactResponse(false, "Inquiry Validation Failed", $errors);
    exit;
}

// Write to contacts.csv safely
$timestamp = date("Y-m-d H:i:s");
$csvFile = DATA_PATH . DIRECTORY_SEPARATOR . 'contacts.csv';
$csvLine = sprintf(
    "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
    $timestamp,
    addslashes($name),
    addslashes($email),
    addslashes($subject),
    addslashes($department),
    addslashes($message)
);
@file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX);

// Also append to submissions.json
$jsonFile = DATA_PATH . DIRECTORY_SEPARATOR . 'submissions.json';
$jsonRecords = [];
if (file_exists($jsonFile)) {
    $existing = @file_get_contents($jsonFile);
    $decoded = json_decode($existing, true);
    if (is_array($decoded)) $jsonRecords = $decoded;
}

$jsonRecords[] = [
    'type' => 'contact',
    'timestamp' => $timestamp,
    'data' => [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'department' => $department,
        'message' => $message
    ]
];
@file_put_contents($jsonFile, json_encode($jsonRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

renderContactResponse(true, "Inquiry Dispatched Successfully!", [
    "From: " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")",
    "Subject: " . htmlspecialchars($subject),
    "Department: " . htmlspecialchars($department),
    "Storage Status: Logged in data/contacts.csv and data/submissions.json",
    "Estimated Response: Academic coordinator will reply within 24 working hours."
]);

function renderContactResponse(bool $success, string $title, array $items): void {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($title) ?> - StudentHub</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">
        <div class="form-card" style="text-align: center; max-width: 540px;">
            <div style="font-size: 3rem; margin-bottom: 10px;"><?= $success ? '✉️' : '⚠️' ?></div>
            <h2 style="color: <?= $success ? 'var(--success)' : 'var(--danger)' ?>; margin-bottom: 12px;"><?= htmlspecialchars($title) ?></h2>
            <div style="text-align: left; background: var(--surface-alt); padding: 16px; border-radius: var(--radius-sm); margin: 20px 0; border: 1px solid var(--border);">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem;">
                    <?php foreach ($items as $it): ?>
                        <li><?= $success ? '✔️' : '❌' ?> <?= htmlspecialchars($it) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="../pages/contact.html" class="btn btn-secondary">Back to Contact</a>
                <a href="../pages/submissions.html" class="btn btn-primary">View CSV Log</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
