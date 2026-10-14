<?php
/**
 * StudentHub - Server-Side Registration Processor (Practicals 7 & 9)
 * Validates Inputs, Stores in CSV/JSON, Checks Duplicates, Hashes Password & Inserts into DB
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method Not Allowed. Please submit the form properly.");
}

// 1. Sanitize and Collect Inputs
$fullName   = sanitizeInput($_POST['fullName'] ?? '');
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$mobile     = sanitizeInput($_POST['mobile'] ?? '');
$course     = sanitizeInput($_POST['course'] ?? '');
$department = sanitizeInput($_POST['department'] ?? 'CSPIT');
$year       = sanitizeInput($_POST['year'] ?? '');
$semester   = intval($_POST['semester'] ?? 3);
$gender     = sanitizeInput($_POST['gender'] ?? '');
$password   = $_POST['password'] ?? '';
$confirmPwd = $_POST['confirmPassword'] ?? '';
$terms      = isset($_POST['terms']);

$errors = [];

// 2. Comprehensive Server-Side Validation (Practical 7 & 9)
if (!preg_match("/^[a-zA-Z\s]{3,50}$/", $fullName)) {
    $errors[] = "Full Name must be between 3 and 50 characters, containing only alphabets.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format. Please provide a valid university email address.";
}

if (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
    $errors[] = "Mobile number must be exactly 10 digits starting with 6, 7, 8, or 9.";
}

if (empty($course)) {
    $errors[] = "Academic program must be selected.";
}

if (empty($year)) {
    $errors[] = "Year of study must be selected.";
}

if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = "Please select a valid gender option.";
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
    $errors[] = "Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special symbols.";
}

if ($password !== $confirmPwd) {
    $errors[] = "Password and confirmation password do not match.";
}

if (!$terms) {
    $errors[] = "You must agree to the Student Code of Conduct.";
}

// If validation errors exist, render clean error page
if (!empty($errors)) {
    renderResultPage(false, "Registration Validation Failed", $errors);
    exit;
}

// 3. Duplicate Check & Database Insertion (Practical 9)
$dbInserted = false;
$pdo = Database::getPDO();

if ($pdo) {
    try {
        // Check for duplicate email in users table
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            renderResultPage(false, "Duplicate Registration", ["An account with email '{$email}' already exists in StudentHub."]);
            exit;
        }

        // Hash password securely using Bcrypt (Practical 9)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $username = strtolower(explode('@', $email)[0]);

        // Insert into users table
        $userStmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, status)
            VALUES (:username, :email, :hash, 'student', 'active')
        ");
        $userStmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':hash'     => $passwordHash
        ]);
        $newUserId = (int)$pdo->lastInsertId();

        // Generate synthetic enrollment ID if not passed
        $enrollment = strtoupper(substr($username, 0, 8));
        if (strlen($enrollment) < 4) $enrollment = "23CE" . rand(100, 999);

        // Insert into students table
        $studentStmt = $pdo->prepare("
            INSERT INTO students (user_id, enrollment, full_name, email, mobile, course, department, year, semester, gender, gpa, status)
            VALUES (:uid, :enroll, :name, :email, :mobile, :course, :dept, :yr, :sem, :gender, 8.50, 'Active')
        ");
        $studentStmt->execute([
            ':uid'    => $newUserId,
            ':enroll' => $enrollment,
            ':name'   => $fullName,
            ':email'  => $email,
            ':mobile' => $mobile,
            ':course' => $course,
            ':dept'   => $department,
            ':yr'     => $year,
            ':sem'    => $semester,
            ':gender' => $gender
        ]);

        $dbInserted = true;
        logAudit($newUserId, 'STUDENT_REGISTER', "New registration completed for {$fullName} ({$enrollment})");

    } catch (PDOException $e) {
        error_log("DB Registration Error: " . $e->getMessage());
    }
}

// 4. File-Based Storage in CSV & JSON (Practical 7)
$timestamp = date("Y-m-d H:i:s");
$csvFile = DATA_PATH . DIRECTORY_SEPARATOR . 'registrations.csv';
$jsonFile = DATA_PATH . DIRECTORY_SEPARATOR . 'submissions.json';

// Append to CSV
$csvLine = sprintf(
    "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
    $timestamp,
    addslashes($fullName),
    addslashes($email),
    addslashes($mobile),
    addslashes($course),
    addslashes($department),
    addslashes($year),
    addslashes($gender)
);
@file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX);

// Append to JSON
$jsonRecords = [];
if (file_exists($jsonFile)) {
    $existing = @file_get_contents($jsonFile);
    $decoded = json_decode($existing, true);
    if (is_array($decoded)) $jsonRecords = $decoded;
}

$jsonRecords[] = [
    'type' => 'registration',
    'timestamp' => $timestamp,
    'data' => [
        'name' => $fullName,
        'email' => $email,
        'mobile' => $mobile,
        'course' => $course,
        'department' => $department,
        'year' => $year,
        'gender' => $gender,
        'db_synced' => $dbInserted
    ]
];
@file_put_contents($jsonFile, json_encode($jsonRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

// 5. Render Success Page
renderResultPage(true, "Registration Completed Successfully!", [
    "Student Name: " . htmlspecialchars($fullName),
    "Email ID: " . htmlspecialchars($email),
    "Academic Program: " . htmlspecialchars($course),
    "CSV File Record: Saved to data/registrations.csv",
    "JSON File Record: Saved to data/submissions.json",
    "Database Record: " . ($dbInserted ? "Inserted into MySQL with Bcrypt Password Hash" : "File backup stored (MySQL offline)")
]);

function renderResultPage(bool $success, string $title, array $messages): void {
    $themeColor = $success ? "#198754" : "#dc3545";
    $icon = $success ? "🎉" : "⚠️";
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - StudentHub</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">
        <div class="form-card" style="text-align: center; max-width: 580px;">
            <div style="font-size: 3rem; margin-bottom: 12px;"><?= $icon ?></div>
            <h2 style="color: <?= $themeColor ?>; margin-bottom: 14px;"><?= htmlspecialchars($title) ?></h2>
            
            <div style="text-align: left; background: var(--surface-alt); padding: 18px; border-radius: var(--radius-sm); margin: 20px 0; border: 1px solid var(--border);">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 0.92rem;">
                    <?php foreach ($messages as $msg): ?>
                        <li style="display: flex; gap: 8px;">
                            <span><?= $success ? '✔️' : '❌' ?></span>
                            <span><?= htmlspecialchars($msg) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center;">
                <?php if ($success): ?>
                    <a href="../pages/login.html" class="btn btn-primary">Proceed to Login</a>
                    <a href="../pages/submissions.html" class="btn btn-secondary">View CSV Records</a>
                <?php else: ?>
                    <a href="javascript:history.back()" class="btn btn-primary">Go Back &amp; Fix Errors</a>
                    <a href="../pages/register.html" class="btn btn-secondary">Start Over</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
