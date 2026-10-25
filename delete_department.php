<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid()) {
    set_flash('danger', 'Invalid delete request.');
    redirect('departments.php');
}

$departmentId = (int) ($_POST['id'] ?? 0);
$usage = fetch_row($conn, 'SELECT
    (SELECT COUNT(*) FROM courses WHERE department_id = ?) AS course_count,
    (SELECT COUNT(*) FROM students WHERE department_id = ?) AS student_count', 'ii', [$departmentId, $departmentId]);

if (!$usage) {
    set_flash('danger', 'Department not found.');
} elseif ((int) $usage['course_count'] > 0 || (int) $usage['student_count'] > 0) {
    set_flash('danger', 'This department cannot be deleted because it has courses or students.');
} elseif (run_query($conn, 'DELETE FROM departments WHERE id = ?', 'i', [$departmentId])) {
    set_flash('success', 'Department deleted successfully.');
} else {
    set_flash('danger', 'Unable to delete department.');
}

redirect('departments.php');
