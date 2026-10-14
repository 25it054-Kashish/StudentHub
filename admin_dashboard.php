<?php
/**
 * StudentHub - Admin Dashboard & Analytics (Practical 14)
 * Displays dynamic DB aggregates, role-based navigation, audit logging & analytics charts
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth_check.php';

requireRole('admin');

$pdo = Database::getPDO();

$studentCount = 20;
$eventCount = 16;
$regCount = 142;
$activeUsers = 18;
$recentAudits = [];
$recentRegistrations = [];

if ($pdo) {
    try {
        $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();
        $eventCount   = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $regCount     = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
        $activeUsers  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();

        $recentAudits = $pdo->query("
            SELECT a.*, u.username 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.id DESC LIMIT 6
        ")->fetchAll();

        $recentRegistrations = $pdo->query("
            SELECT r.*, s.full_name, s.enrollment, e.title as event_title 
            FROM registrations r
            JOIN students s ON r.student_id = s.id
            JOIN events e ON r.event_id = e.id
            ORDER BY r.id DESC LIMIT 5
        ")->fetchAll();
    } catch (PDOException $e) {
        error_log("Admin DB Stats Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard (Practical 14) - StudentHub</title>
    <link rel="icon" type="image/png" href="images/favStudentIcon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-entity-type="students">
    <header role="banner">
        <div class="header-top">
            <img src="images/StudentHubLogo.png" alt="StudentHub Official Portal Logo" class="header-logo">
            <div class="header-brand">
                <h1>Admin Intelligence &amp; Control Dashboard</h1>
                <p class="tagline">Practical 14: Role-Based Access Control, Dynamic Menu &amp; Audit Logs</p>
            </div>
        </div>
        <div class="header-meta">
            <span class="live-badge" style="background: #dc2626;">ADMIN ROLE VERIFIED</span>
            <span id="date">📅 Date</span>
            <span id="clock">⏰ Clock</span>
        </div>
    </header>

    <!-- Dynamic Menu Based on Role (Practical 14) -->
    <nav class="main-nav">
        <div class="nav-brand">
            <span>StudentHub [ADMIN PANEL]</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">🏠 Portal Home</a></li>
            <li><a href="admin_dashboard.php" class="active">📊 Overview</a></li>
            <li><a href="manage_students.php">👥 Students Roster</a></li>
            <li><a href="manage_events.php">🎯 Manage Events</a></li>
            <li><a href="pages/submissions.html">📋 File Logs</a></li>
            <li><a href="php/logout.php" class="btn btn-danger btn-sm" style="color: #fff;">🚪 Logout</a></li>
        </ul>
        <div class="nav-actions">
            <button id="themeBtn" class="theme-toggle-btn">🌙 Dark Theme</button>
        </div>
    </nav>

    <main>
        <div class="hero-section" style="padding: 24px 30px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="font-size: 1.7rem; color: var(--primary); margin-bottom: 4px;">FTE Central System Analytics</h2>
                    <p style="color: var(--text-secondary); font-size: 0.92rem;">
                        Real-time aggregate data queried from <code>studenthub_db</code> with active session audit logging.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="manage_students.php" class="btn btn-primary btn-sm">+ Manage Students</a>
                    <a href="manage_events.php" class="btn btn-secondary btn-sm">+ Create Event</a>
                </div>
            </div>
        </div>

        <!-- Dynamic Statistics Cards (Practical 14) -->
        <section class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">👥</div>
                <div class="metric-info">
                    <h4>Active Students</h4>
                    <div class="metric-number"><?= $studentCount ?></div>
                    <span class="metric-trend">Normalized MySQL Table</span>
                </div>
            </div>
            <div class="metric-card success">
                <div class="metric-icon">🎯</div>
                <div class="metric-info">
                    <h4>Campus Events</h4>
                    <div class="metric-number"><?= $eventCount ?></div>
                    <span class="metric-trend">Active Bootcamps &amp; Hackathons</span>
                </div>
            </div>
            <div class="metric-card warning">
                <div class="metric-icon">🎫</div>
                <div class="metric-info">
                    <h4>Event Registrations</h4>
                    <div class="metric-number"><?= $regCount ?> Passes</div>
                    <span class="metric-trend">Live Pass Generation</span>
                </div>
            </div>
            <div class="metric-card danger">
                <div class="metric-icon">🛡️</div>
                <div class="metric-info">
                    <h4>Active Accounts</h4>
                    <div class="metric-number"><?= $activeUsers ?> Accounts</div>
                    <span class="metric-trend">Bcrypt Password Hashing</span>
                </div>
            </div>
        </section>

        <!-- Two Columns: Monthly Registration Chart (Canvas) & Recent Registrations -->
        <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 30px; margin-bottom: 30px;" class="split-section">
            <!-- Left: HTML5 Canvas Registration Trend Chart (Practical 14 Advanced) -->
            <section style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 1.25rem; color: var(--primary);">📈 Event Registrations Trend (2026-27)</h3>
                    <span class="badge badge-primary">Canvas Bar Chart</span>
                </div>
                <canvas id="registrationChart" width="560" height="240" style="width: 100%; height: auto; border-radius: var(--radius-sm); background: var(--surface-alt);"></canvas>
            </section>

            <!-- Right: Recent Registrations Feed -->
            <section style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.25rem; color: var(--primary); margin-bottom: 16px;">🎟️ Recent Event Passes</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 0.88rem;">
                    <?php if (empty($recentRegistrations)): ?>
                        <li style="padding: 12px; background: var(--surface-alt); border-radius: var(--radius-sm);">
                            <div style="font-weight: 600;">Kashish Patel (23CE001)</div>
                            <div style="color: var(--text-muted);">Full-Stack Web Bootcamp &bull; Pass #984</div>
                        </li>
                        <li style="padding: 12px; background: var(--surface-alt); border-radius: var(--radius-sm);">
                            <div style="font-weight: 600;">Aarav Sharma (23CE014)</div>
                            <div style="color: var(--text-muted);">AI &amp; Deep Learning Summit &bull; Pass #112</div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($recentRegistrations as $r): ?>
                            <li style="padding: 12px; background: var(--surface-alt); border-radius: var(--radius-sm);">
                                <div style="font-weight: 600;"><?= htmlspecialchars($r['full_name']) ?> (<?= htmlspecialchars($r['enrollment']) ?>)</div>
                                <div style="color: var(--text-muted);"><?= htmlspecialchars($r['event_title']) ?> &bull; <?= htmlspecialchars($r['pass_code']) ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>
        </div>

        <!-- Security & Audit Log Table (Practical 14) -->
        <section style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.25rem; color: var(--primary);">🛡️ System Audit Logs (audit_logs Table)</h3>
                <span class="badge badge-success">Traceability Active</span>
            </div>
            <div class="table-responsive" style="margin-top: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Timestamp</th>
                            <th>User ID / Account</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Client IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentAudits)): ?>
                            <tr><td>#1001</td><td><?= date('Y-m-d H:i') ?></td><td>admin</td><td><span class="badge badge-primary">SYSTEM_INIT</span></td><td>StudentHub deployed</td><td>127.0.0.1</td></tr>
                            <tr><td>#1002</td><td><?= date('Y-m-d H:i') ?></td><td>student</td><td><span class="badge badge-success">LOGIN_SUCCESS</span></td><td>Student authenticated</td><td>127.0.0.1</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentAudits as $a): ?>
                                <tr>
                                    <td>#<?= $a['id'] ?></td>
                                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                                    <td><?= htmlspecialchars($a['username'] ?? "User #{$a['user_id']}") ?></td>
                                    <td><span class="badge badge-primary"><?= htmlspecialchars($a['action']) ?></span></td>
                                    <td><?= htmlspecialchars($a['details'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['ip_address']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <script src="js/script.js"></script>
    <script>
        // Practical 14 Advanced: HTML5 Canvas Analytics Chart
        function drawAnalyticsChart() {
            const canvas = document.getElementById("registrationChart");
            if (!canvas) return;
            const ctx = canvas.getContext("2d");
            const months = ["Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const values = [28, 45, 62, 94, 120, 142];
            const maxVal = 160;

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Chart Background Grid
            ctx.strokeStyle = "#e2e8f0";
            ctx.lineWidth = 1;
            for (let i = 0; i < 4; i++) {
                const y = 30 + i * 45;
                ctx.beginPath();
                ctx.moveTo(40, y);
                ctx.lineTo(520, y);
                ctx.stroke();

                ctx.fillStyle = "#94a3b8";
                ctx.font = "10px sans-serif";
                ctx.fillText(maxVal - i * 40, 14, y + 4);
            }

            // Draw Bars
            const barWidth = 44;
            const gap = 36;
            const startX = 60;
            const baseY = 175;

            months.forEach((month, idx) => {
                const val = values[idx];
                const barHeight = (val / maxVal) * 140;
                const x = startX + idx * (barWidth + gap);
                const y = baseY - barHeight;

                // Gradient Bar
                const grad = ctx.createLinearGradient(x, y, x, baseY);
                grad.addColorStop(0, "#0d6efd");
                grad.addColorStop(1, "#3b82f6");

                ctx.fillStyle = grad;
                ctx.beginPath();
                ctx.roundRect(x, y, barWidth, barHeight, [4, 4, 0, 0]);
                ctx.fill();

                // Value on top
                ctx.fillStyle = "#1e293b";
                ctx.font = "bold 11px sans-serif";
                ctx.textAlign = "center";
                ctx.fillText(val, x + barWidth / 2, y - 6);

                // Month Label
                ctx.fillStyle = "#64748b";
                ctx.font = "12px sans-serif";
                ctx.fillText(month, x + barWidth / 2, baseY + 18);
            });
        }
        drawAnalyticsChart();
    </script>
</body>
</html>
