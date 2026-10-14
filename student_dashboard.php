<?php
/**
 * StudentHub - Authenticated Student Dashboard (Practical 10 & 14)
 * Displays personalized profile metrics, registered passes, and timetable
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth_check.php';

requireRole('student');

$pdo = Database::getPDO();
$userId = $_SESSION['user_id'] ?? 2;
$student = null;
$myEvents = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $student = $stmt->fetch();

        if ($student) {
            $regStmt = $pdo->prepare("
                SELECT r.*, e.title, e.category, e.event_date, e.venue 
                FROM registrations r 
                JOIN events e ON r.event_id = e.id 
                WHERE r.student_id = :sid 
                ORDER BY e.event_date ASC
            ");
            $regStmt->execute([':sid' => $student['id']]);
            $myEvents = $regStmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Student Dashboard Error: " . $e->getMessage());
    }
}

// Fallback student profile if offline
if (!$student) {
    $student = [
        'full_name'  => 'Kashish Patel',
        'enrollment' => '23CE001',
        'email'      => '23ce001@charusat.edu.in',
        'course'     => 'B.Tech Computer Engineering',
        'department' => 'CSPIT-CE',
        'year'       => '2nd Year',
        'semester'   => 3,
        'gpa'        => '9.42'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - <?= htmlspecialchars($student['full_name']) ?> | StudentHub</title>
    <link rel="icon" type="image/png" href="images/favStudentIcon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header role="banner">
        <div class="header-top">
            <img src="images/StudentHubLogo.png" alt="StudentHub Official Portal Logo" class="header-logo">
            <div class="header-brand">
                <h1>Student Academic Dashboard</h1>
                <p class="tagline">Faculty of Technology and Engineering (FTE) &bull; CHARUSAT</p>
            </div>
        </div>
        <div class="header-meta">
            <span class="live-badge">Student Session Active</span>
            <span id="date">📅 Date</span>
            <span id="clock">⏰ Clock</span>
        </div>
    </header>

    <nav class="main-nav">
        <div class="nav-brand">
            <img src="images/favStudentIcon.png" alt="Icon">
            <span>StudentHub</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="student_dashboard.php" class="active">📊 My Dashboard</a></li>
            <li><a href="pages/events.html">🎯 Events</a></li>
            <li><a href="pages/profile.html">👤 My Profile</a></li>
            <li><a href="pages/faq.html">❓ FAQ</a></li>
            <li><a href="pages/contact.html">📞 Contact</a></li>
            <li><a href="php/logout.php" class="btn btn-danger btn-sm" style="color: #fff;">🚪 Logout</a></li>
        </ul>
        <div class="nav-actions">
            <button id="themeBtn" class="theme-toggle-btn">🌙 Dark Theme</button>
        </div>
    </nav>

    <main>
        <section class="hero-section" style="padding: 24px 30px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 18px;">
                    <img src="images/avatar.svg" alt="Avatar" style="width: 72px; height: 72px; border-radius: 50%; border: 3px solid var(--primary);">
                    <div>
                        <h2 style="font-size: 1.6rem; color: var(--primary); margin-bottom: 2px;"><?= htmlspecialchars($student['full_name']) ?></h2>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">
                            <strong>Enrollment:</strong> <?= htmlspecialchars($student['enrollment']) ?> &bull; 
                            <strong>Department:</strong> <?= htmlspecialchars($student['department']) ?> &bull; 
                            <strong>Semester:</strong> <?= htmlspecialchars($student['semester']) ?> (<?= htmlspecialchars($student['year']) ?>)
                        </div>
                    </div>
                </div>
                <div>
                    <a href="pages/events.html" class="btn btn-primary btn-sm">+ Register for Events</a>
                </div>
            </div>
        </section>

        <!-- Metrics Grid -->
        <section class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">📈</div>
                <div class="metric-info">
                    <h4>Current CGPA</h4>
                    <div class="metric-number"><?= htmlspecialchars($student['gpa']) ?></div>
                    <span class="metric-trend">Top 5% in Department</span>
                </div>
            </div>
            <div class="metric-card success">
                <div class="metric-icon">✅</div>
                <div class="metric-info">
                    <h4>Attendance</h4>
                    <div class="metric-number">88.5%</div>
                    <span class="metric-trend">Above 80% Requirement</span>
                </div>
            </div>
            <div class="metric-card warning">
                <div class="metric-icon">🎫</div>
                <div class="metric-info">
                    <h4>Registered Events</h4>
                    <div class="metric-number"><?= count($myEvents) ?: 2 ?> Active</div>
                    <span class="metric-trend">Confirmed Passes</span>
                </div>
            </div>
            <div class="metric-card danger">
                <div class="metric-icon">📚</div>
                <div class="metric-info">
                    <h4>Semester CIE</h4>
                    <div class="metric-number">92%</div>
                    <span class="metric-trend">Grade Standing: A+</span>
                </div>
            </div>
        </section>

        <!-- Registered Passes Table -->
        <section style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.25rem; color: var(--primary);">🎟️ My Registered Campus Activities &amp; Passes</h3>
                <a href="pages/events.html" class="btn btn-secondary btn-sm">Explore More Events ›</a>
            </div>
            <div class="table-responsive" style="margin-top: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Activity / Event</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Pass Code</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myEvents)): ?>
                            <tr>
                                <td><strong>Full-Stack Web Dev Bootcamp</strong></td>
                                <td><span class="badge badge-primary">Workshop</span></td>
                                <td>25 Oct 2026</td>
                                <td>CSPIT Hall 1</td>
                                <td><code>PASS-WEB-23CE001-984</code></td>
                                <td><span class="badge badge-success">Confirmed</span></td>
                            </tr>
                            <tr>
                                <td><strong>Charusat Innovate 36-Hr Hackathon</strong></td>
                                <td><span class="badge badge-warning">Hackathon</span></td>
                                <td>28 Nov 2026</td>
                                <td>Innovation Complex</td>
                                <td><code>PASS-HACK-23CE001-441</code></td>
                                <td><span class="badge badge-success">Team Verified</span></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myEvents as $ev): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                                    <td><span class="badge badge-primary"><?= htmlspecialchars($ev['category']) ?></span></td>
                                    <td><?= htmlspecialchars($ev['event_date']) ?></td>
                                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                                    <td><code><?= htmlspecialchars($ev['pass_code']) ?></code></td>
                                    <td><span class="badge badge-success"><?= htmlspecialchars($ev['attendance_status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="js/script.js"></script>
</body>
</html>
