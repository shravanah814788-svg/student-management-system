<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid()) {
    set_flash('danger', 'Invalid delete request.');
    redirect('courses.php');
}

$courseId = (int) ($_POST['id'] ?? 0);
$usage = fetch_row($conn, 'SELECT COUNT(*) AS student_count FROM students WHERE course_id = ?', 'i', [$courseId]);

if (!$usage) {
    set_flash('danger', 'Course not found.');
} elseif ((int) $usage['student_count'] > 0) {
    set_flash('danger', 'This course cannot be deleted because students are enrolled in it.');
} elseif (run_query($conn, 'DELETE FROM courses WHERE id = ?', 'i', [$courseId])) {
    set_flash('success', 'Course deleted successfully.');
} else {
    set_flash('danger', 'Unable to delete course.');
}

redirect('courses.php');
