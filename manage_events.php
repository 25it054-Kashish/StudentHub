<?php
/**
 * StudentHub - Event Management CRUD with Poster Upload & File Validation (Practical 12)
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth_check.php';

requireRole('admin');

$pdo = Database::getPDO();
$events = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
        $events = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Events Fetch Error: " . $e->getMessage());
    }
} else {
    $json = @file_get_contents(DATA_PATH . '/events.json');
    $events = json_decode($json, true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Practical 12 | StudentHub</title>
    <link rel="icon" type="image/png" href="images/favStudentIcon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header role="banner">
        <div class="header-top">
            <img src="images/StudentHubLogo.png" alt="StudentHub Logo" class="header-logo">
            <div class="header-brand">
                <h1>Event Management CRUD &amp; Poster Upload (Practical 12)</h1>
                <p class="tagline">Faculty of Technology and Engineering &bull; CHARUSAT</p>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="nav-brand">
            <span>StudentHub [Admin]</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="admin_dashboard.php">⚙️ Admin Dashboard</a></li>
            <li><a href="manage_students.php">👥 Students CRUD</a></li>
            <li><a href="manage_events.php" class="active">🎯 Events CRUD</a></li>
            <li><a href="php/logout.php" class="btn btn-danger btn-sm" style="color: #fff;">🚪 Logout</a></li>
        </ul>
        <div class="nav-actions">
            <button id="themeBtn" class="theme-toggle-btn">🌙 Dark Theme</button>
        </div>
    </nav>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
            <div>
                <h2 style="color: var(--primary);">Campus Events Directory</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    Validated Multi-Part Poster Upload &bull; File Size &lt; 2MB &bull; MIME Type Verification
                </p>
            </div>
            <button class="btn btn-primary" onclick="window.openModal('addEventModal')">+ Create New Event</button>
        </div>

        <div class="cards-grid">
            <?php foreach ($events as $ev): ?>
                <div class="card">
                    <div class="card-media">
                        <img src="<?= htmlspecialchars($ev['poster_path'] ?? $ev['poster'] ?? 'images/event-web.svg') ?>" alt="<?= htmlspecialchars($ev['title']) ?>">
                        <span class="card-badge"><?= htmlspecialchars($ev['category']) ?></span>
                    </div>
                    <div class="card-body">
                        <h3><?= htmlspecialchars($ev['title']) ?></h3>
                        <div class="card-meta">
                            <span>📅 <?= htmlspecialchars($ev['event_date'] ?? $ev['date'] ?? '') ?></span>
                            <span>🏛️ <?= htmlspecialchars($ev['venue']) ?></span>
                        </div>
                        <p><?= htmlspecialchars($ev['description']) ?></p>
                        <div class="card-footer">
                            <span class="badge <?= ($ev['status'] ?? 'Open') === 'Open' ? 'badge-success' : 'badge-danger' ?>">
                                <?= htmlspecialchars($ev['status'] ?? 'Open') ?>
                            </span>
                            <div style="display: flex; gap: 6px;">
                                <a href="php/event_crud.php?action=delete&id=<?= $ev['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?');">🗑️ Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Create Event Modal (Practical 12 Multipart Upload) -->
    <div class="modal-backdrop" id="addEventModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>Create Campus Event with Poster</h3>
                <button type="button" class="modal-close-btn" data-close-modal>&times;</button>
            </div>
            <form action="php/event_crud.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Event Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Next-Gen DevOps Summit" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control" required>
                                <option value="Workshop">Workshop</option>
                                <option value="Hackathon">Hackathon</option>
                                <option value="Competition">Competition</option>
                                <option value="Robotics">Robotics &amp; IoT</option>
                                <option value="Conference">Conference</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Scheduled Date</label>
                            <input type="date" name="event_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Venue Location</label>
                            <input type="text" name="venue" class="form-control" placeholder="e.g. CSPIT Hall 2" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keynote Speaker</label>
                            <input type="text" name="speaker" class="form-control" placeholder="e.g. Industry Expert" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload Event Poster Image (Max 2MB: JPG, PNG, WebP) <span class="required">*</span></label>
                        <input type="file" name="poster" class="form-control" accept="image/png, image/jpeg, image/webp" required>
                        <div class="form-help">Strictly verified using PHP finfo MIME-inspection.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Event Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief synopsis of technical topics covered..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Event</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
