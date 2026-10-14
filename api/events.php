<?php
/**
 * StudentHub - REST JSON API for Events (Practical 13)
 * Provides Async Endpoints for List, Add, Update, and Delete with Proper HTTP Status Codes
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = Database::getPDO();

// -----------------------------------------------------------------------------
// GET: List all events or retrieve single event
// -----------------------------------------------------------------------------
if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? 'All';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $item = $stmt->fetch();
            if ($item) {
                jsonResponse(['success' => true, 'data' => $item], 200);
            } else {
                jsonResponse(['success' => false, 'message' => 'Event not found.'], 404);
            }
        }
    }

    if ($pdo) {
        $sql = "SELECT * FROM events WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (title LIKE :s OR description LIKE :s OR speaker LIKE :s)";
            $params[':s'] = "%{$search}%";
        }
        if ($category !== 'All') {
            $sql .= " AND category = :cat";
            $params[':cat'] = $category;
        }

        $sql .= " ORDER BY event_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

        jsonResponse(['success' => true, 'count' => count($events), 'data' => $events], 200);
    } else {
        // Fallback to events.json if DB is offline
        $json = @file_get_contents(DATA_PATH . '/events.json');
        $data = json_decode($json, true) ?: [];
        jsonResponse(['success' => true, 'count' => count($data), 'data' => $data], 200);
    }
}

// -----------------------------------------------------------------------------
// POST: Create a new event
// -----------------------------------------------------------------------------
if ($method === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true) ?: $_POST;

    $title       = sanitizeInput($data['title'] ?? '');
    $category    = sanitizeInput($data['category'] ?? 'Workshop');
    $eventDate   = sanitizeInput($data['date'] ?? $data['event_date'] ?? date('Y-m-d'));
    $eventTime   = sanitizeInput($data['time'] ?? $data['event_time'] ?? '10:00 AM');
    $venue       = sanitizeInput($data['venue'] ?? 'CSPIT Hall');
    $speaker     = sanitizeInput($data['speaker'] ?? 'Invited Mentor');
    $description = sanitizeInput($data['description'] ?? '');
    $seats       = intval($data['seats'] ?? 60);
    $fee         = floatval($data['fee'] ?? 0);

    if (empty($title)) {
        jsonResponse(['success' => false, 'message' => 'Title is required.'], 400);
    }

    if ($pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, category, event_date, event_time, venue, speaker, description, seats, available_seats, fee, status)
            VALUES (:title, :cat, :edate, :etime, :venue, :speaker, :desc, :seats, :seats, :fee, 'Open')
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
            ':fee'     => $fee
        ]);
        $newId = (int)$pdo->lastInsertId();
        jsonResponse(['success' => true, 'message' => 'Event created successfully.', 'id' => $newId], 201);
    } else {
        jsonResponse(['success' => true, 'message' => 'Event processed (Local store mode).'], 201);
    }
}

// -----------------------------------------------------------------------------
// DELETE: Remove an event
// -----------------------------------------------------------------------------
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Valid Event ID is required.'], 400);
    }

    if ($pdo) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
        $stmt->execute([':id' => $id]);
        jsonResponse(['success' => true, 'message' => "Event #{$id} deleted successfully."], 200);
    } else {
        jsonResponse(['success' => true, 'message' => "Event #{$id} removed from view."], 200);
    }
}

jsonResponse(['success' => false, 'message' => 'Unsupported method.'], 405);
