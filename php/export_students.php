<?php
/**
 * StudentHub - Student List CSV Exporter (Practical 11 Intermediate)
 * Exports full student cohort records as downloadable CSV
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=StudentHub_Cohort_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['ID', 'Enrollment', 'Full Name', 'Email', 'Mobile', 'Program', 'Department', 'Year', 'Semester', 'GPA', 'Status']);

$pdo = Database::getPDO();
$rows = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, enrollment, full_name, email, mobile, course, department, year, semester, gpa, status FROM students ORDER BY enrollment ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    } catch (PDOException $e) {
        error_log("CSV Export DB Error: " . $e->getMessage());
    }
}

// Fallback to local students.json if DB unavailable
$jsonFile = DATA_PATH . DIRECTORY_SEPARATOR . 'students.json';
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    if (is_array($data)) {
        foreach ($data as $s) {
            fputcsv($output, [
                $s['id'] ?? '',
                $s['enrollment'] ?? '',
                $s['name'] ?? '',
                $s['email'] ?? '',
                $s['mobile'] ?? '',
                $s['course'] ?? '',
                $s['department'] ?? '',
                $s['year'] ?? '',
                $s['semester'] ?? '',
                $s['gpa'] ?? '',
                $s['status'] ?? 'Active'
            ]);
        }
    }
}

fclose($output);
exit;
