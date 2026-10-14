<?php
/**
 * StudentHub - Event Management CRUD & Secure Poster Upload (Practical 12)
 * Handles File Type, MIME, and Size Validation, Safe Image Renaming & Audit Logs
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

// Only admins can execute event modifications
requireRole('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$pdo = Database::getPDO();

$uploadDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'events';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// -----------------------------------------------------------------------------
// ACTION: CREATE / UPDATE EVENT
// -----------------------------------------------------------------------------
if ($action === 'create' || $action === 'update') {
    $id          = intval($_POST['id'] ?? 0);
    $title       = sanitizeInput($_POST['title'] ?? '');
    $category    = sanitizeInput($_POST['category'] ?? 'Workshop');
    $eventDate   = sanitizeInput($_POST['event_date'] ?? date('Y-m-d'));
    $eventTime   = sanitizeInput($_POST['event_time'] ?? '10:00 AM');
    $venue       = sanitizeInput($_POST['venue'] ?? 'CSPIT Seminar Hall');
    $speaker     = sanitizeInput($_POST['speaker'] ?? 'Guest Faculty');
    $description = sanitizeInput($_POST['description'] ?? '');
    $seats       = intval($_POST['seats'] ?? 60);
    $fee         = floatval($_POST['fee'] ?? 0.0);
    $status      = in_array($_POST['status'] ?? '', ['Open', 'Closed', 'Upcoming']) ? $_POST['status'] : 'Open';
    
    $posterPath = $_POST['existing_poster'] ?? 'images/event-web.svg';

    // File Upload Validation (Practical 12)
    if (!empty($_FILES['poster']['name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poster'];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB Limit
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];

        $fileSize = $file['size'];
        $fileTmp  = $file['tmp_name'];
        $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // 1. File Size Validation
        if ($fileSize > $maxSizeBytes) {
            die("Error: Uploaded poster exceeds maximum allowed file size of 2MB.");
        }

        // 2. Extension Validation
        if (!in_array($fileExt, $allowedExts)) {
            die("Error: Invalid file format. Only JPG, PNG, and WebP images are permitted.");
        }

        // 3. True MIME Type Inspection using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            die("Error: Security Alert! Detected invalid image MIME type: {$mimeType}");
        }

        // 4. Secure File Renaming (Practical 12 Advanced)
        $uniqueFilename = 'event_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $uniqueFilename;

        if (move_uploaded_file($fileTmp, $destination)) {
            $posterPath = 'uploads/events/' . $uniqueFilename;
        } else {
            die("Error: Failed to save uploaded image to destination folder.");
        }
    }

    if ($pdo) {
        if ($action === 'create') {
            $stmt = $pdo->prepare("
                INSERT INTO events (title, category, event_date, event_time, venue, speaker, description, seats, available_seats, fee, poster_path, status)
                VALUES (:title, :cat, :edate, :etime, :venue, :speaker, :desc, :seats, :seats, :fee, :poster, :status)
            ");
            $stmt->execute([
                ':title'   => $title,
                ':cat'     => $category,
                ':edate'   => $eventDate,
                ':etime'   => $eventTime,
                ':venue'   => $venue,
                ':speaker' => $speaker,
                ':desc'    => $description,
                ':seats'   => $seats,
                ':fee'     => $fee,
                ':poster'  => $posterPath,
                ':status'  => $status
            ]);
            logAudit($_SESSION['user_id'] ?? null, 'EVENT_CREATE', "Created event: {$title}");
        } else {
            $stmt = $pdo->prepare("
                UPDATE events 
                SET title = :title, category = :cat, event_date = :edate, event_time = :etime, venue = :venue, 
                    speaker = :speaker, description = :desc, seats = :seats, fee = :fee, poster_path = :poster, status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                ':id'      => $id,
                ':title'   => $title,
                ':cat'     => $category,
                ':edate'   => $eventDate,
                ':etime'   => $eventTime,
                ':venue'   => $venue,
                ':speaker' => $speaker,
                ':desc'    => $description,
                ':seats'   => $seats,
                ':fee'     => $fee,
                ':poster'  => $posterPath,
                ':status'  => $status
            ]);
            logAudit($_SESSION['user_id'] ?? null, 'EVENT_UPDATE', "Updated event #{$id}: {$title}");
        }
    }

    header("Location: ../pages/admin.html?msg=event_saved");
    exit;
}

// -----------------------------------------------------------------------------
// ACTION: DELETE EVENT
// -----------------------------------------------------------------------------
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($pdo && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
        $stmt->execute([':id' => $id]);
        logAudit($_SESSION['user_id'] ?? null, 'EVENT_DELETE', "Deleted event ID #{$id}");
    }
    header("Location: ../pages/admin.html?msg=event_deleted");
    exit;
}
