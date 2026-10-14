<?php
/**
 * StudentHub - Student Management Module (Practical 11)
 * Complete MySQLi CRUD with Search, Department Filter, Soft Delete & CSV Export
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth_check.php';

// Role restriction
requireRole('admin');

$mysqli = Database::getMySQLi();
$message = '';
$messageType = 'success';

// -----------------------------------------------------------------------------
// POST ACTIONS: INSERT / UPDATE / SOFT-DELETE / HARD-DELETE
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD STUDENT
    if ($action === 'add' && $mysqli) {
        $enrollment = sanitizeInput($_POST['enrollment'] ?? '');
        $name       = sanitizeInput($_POST['full_name'] ?? '');
        $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $mobile     = sanitizeInput($_POST['mobile'] ?? '9876543210');
        $course     = sanitizeInput($_POST['course'] ?? 'B.Tech Computer Engineering');
        $department = sanitizeInput($_POST['department'] ?? 'CSPIT-CE');
        $year       = sanitizeInput($_POST['year'] ?? '2nd Year');
        $semester   = intval($_POST['semester'] ?? 3);
        $gender     = sanitizeInput($_POST['gender'] ?? 'Male');
        $gpa        = floatval($_POST['gpa'] ?? 8.50);

        $stmt = $mysqli->prepare("
            INSERT INTO students (enrollment, full_name, email, mobile, course, department, year, semester, gender, gpa, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')
        ");
        $stmt->bind_param("sssssssisd", $enrollment, $name, $email, $mobile, $course, $department, $year, $semester, $gender, $gpa);

        if ($stmt->execute()) {
            $message = "Student {$name} ({$enrollment}) added successfully!";
            logAudit($_SESSION['user_id'] ?? null, 'STUDENT_ADD', "Added {$enrollment}");
        } else {
            $message = "Failed to add student: " . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    // SOFT-DELETE TOGGLE (Practical 11 Advanced)
    if ($action === 'toggle_status' && $mysqli) {
        $studentId = intval($_POST['student_id'] ?? 0);
        $newStatus = ($_POST['current_status'] === 'Active') ? 'Inactive' : 'Active';

        $stmt = $mysqli->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $studentId);
        if ($stmt->execute()) {
            $message = "Student status updated to {$newStatus} (Soft Delete).";
            logAudit($_SESSION['user_id'] ?? null, 'STUDENT_STATUS_TOGGLE', "ID: {$studentId} -> {$newStatus}");
        }
        $stmt->close();
    }

    // HARD DELETE
    if ($action === 'delete' && $mysqli) {
        $studentId = intval($_POST['student_id'] ?? 0);
        $stmt = $mysqli->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $studentId);
        if ($stmt->execute()) {
            $message = "Student record deleted successfully.";
            logAudit($_SESSION['user_id'] ?? null, 'STUDENT_DELETE', "Deleted ID: {$studentId}");
        }
        $stmt->close();
    }
}

// -----------------------------------------------------------------------------
// QUERY: SELECT WITH SEARCH & FILTER (Practical 11)
// -----------------------------------------------------------------------------
$search = sanitizeInput($_GET['search'] ?? '');
$deptFilter = sanitizeInput($_GET['department'] ?? 'All');

$students = [];

if ($mysqli) {
    $query = "SELECT * FROM students WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (full_name LIKE ? OR enrollment LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if ($deptFilter !== 'All') {
        $query .= " AND department = ?";
        $params[] = $deptFilter;
        $types .= "s";
    }

    $query .= " ORDER BY enrollment ASC";

    $stmt = $mysqli->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Fallback to static JSON if DB offline
    $json = @file_get_contents(DATA_PATH . '/students.json');
    $students = json_decode($json, true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Practical 11 | StudentHub</title>
    <link rel="icon" type="image/png" href="images/favStudentIcon.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header role="banner">
        <div class="header-top">
            <img src="images/StudentHubLogo.png" alt="StudentHub Official Portal Logo" class="header-logo">
            <div class="header-brand">
                <h1>Student Management CRUD (Practical 11)</h1>
                <p class="tagline">Faculty of Technology &amp; Engineering &bull; CHARUSAT</p>
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
            <li><a href="manage_students.php" class="active">👥 Students CRUD</a></li>
            <li><a href="manage_events.php">🎯 Events CRUD</a></li>
            <li><a href="php/logout.php" class="btn btn-danger btn-sm" style="color: #fff;">🚪 Logout</a></li>
        </ul>
        <div class="nav-actions">
            <button id="themeBtn" class="theme-toggle-btn">🌙 Dark Theme</button>
        </div>
    </nav>

    <main>
        <?php if (!empty($message)): ?>
            <div style="padding: 12px 18px; margin-bottom: 20px; border-radius: var(--radius-sm); background: <?= $messageType === 'success' ? 'var(--success-light)' : 'var(--danger-light)' ?>; color: <?= $messageType === 'success' ? 'var(--success)' : 'var(--danger)' ?>; font-weight: 600;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
            <div>
                <h2 style="color: var(--primary);">Student Records Roster</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    MySQLi Prepared Statements &bull; Search, Filter &bull; Soft Delete &bull; CSV Export
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-primary btn-sm" onclick="window.openModal('addStudentModal')">+ Add New Student</button>
                <a href="php/export_students.php" class="btn btn-secondary btn-sm">⬇️ Export to CSV</a>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <form method="GET" class="toolbar" style="margin-bottom: 24px;">
            <div class="toolbar-search">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by enrollment, name, or email...">
            </div>
            <div class="toolbar-filter">
                <select name="department" onchange="this.form.submit()">
                    <option value="All" <?= $deptFilter === 'All' ? 'selected' : '' ?>>All Departments</option>
                    <option value="CSPIT-CE" <?= $deptFilter === 'CSPIT-CE' ? 'selected' : '' ?>>CSPIT-CE</option>
                    <option value="CSPIT-IT" <?= $deptFilter === 'CSPIT-IT' ? 'selected' : '' ?>>CSPIT-IT</option>
                    <option value="CSPIT-AIML" <?= $deptFilter === 'CSPIT-AIML' ? 'selected' : '' ?>>CSPIT-AIML</option>
                    <option value="DEPSTAR-CSE" <?= $deptFilter === 'DEPSTAR-CSE' ? 'selected' : '' ?>>DEPSTAR-CSE</option>
                    <option value="DEPSTAR-IT" <?= $deptFilter === 'DEPSTAR-IT' ? 'selected' : '' ?>>DEPSTAR-IT</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if (!empty($search) || $deptFilter !== 'All'): ?>
                <a href="manage_students.php" class="btn btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Records Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Enrollment</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Program</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>GPA</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="9" style="text-align: center; padding: 30px;">No student records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['enrollment']) ?></strong></td>
                                <td><?= htmlspecialchars($s['full_name'] ?? $s['name']) ?></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($s['department']) ?></span></td>
                                <td><?= htmlspecialchars($s['year']) ?></td>
                                <td><span style="font-weight: 700; color: var(--success);"><?= htmlspecialchars($s['gpa'] ?? '8.5') ?></span></td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Toggle status for this student?');">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $s['status'] ?? 'Active' ?>">
                                        <button type="submit" class="badge <?= ($s['status'] ?? 'Active') === 'Active' ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;">
                                            <?= htmlspecialchars($s['status'] ?? 'Active') ?> (Toggle)
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Confirm permanent deletion of <?= htmlspecialchars($s['full_name'] ?? $s['name']) ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Student Modal -->
    <div class="modal-backdrop" id="addStudentModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>Add New Student Record</h3>
                <button type="button" class="modal-close-btn" data-close-modal>&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Enrollment Number</label>
                        <input type="text" name="enrollment" class="form-control" placeholder="e.g. 23CE120" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Priyansh Shah" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. 23ce120@charusat.edu.in" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Academic Program</label>
                            <select name="course" class="form-control" required>
                                <option value="B.Tech Computer Engineering">B.Tech Computer Engineering</option>
                                <option value="B.Tech Information Technology">B.Tech Information Technology</option>
                                <option value="B.Tech Artificial Intelligence & ML">B.Tech AI &amp; ML</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-control" required>
                                <option value="CSPIT-CE">CSPIT-CE</option>
                                <option value="CSPIT-IT">CSPIT-IT</option>
                                <option value="CSPIT-AIML">CSPIT-AIML</option>
                                <option value="DEPSTAR-CSE">DEPSTAR-CSE</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label">Year of Study</label>
                            <select name="year" class="form-control" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year" selected>2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Current CGPA</label>
                            <input type="number" step="0.01" min="0" max="10" name="gpa" class="form-control" value="8.50" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Save to Database</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
