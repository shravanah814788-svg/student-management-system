<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/student_helpers.php';
require_login();

$studentId = (int) ($_REQUEST['id'] ?? 0);
$student = fetch_row($conn, 'SELECT id, usn, full_name, photo FROM students WHERE id = ?', 'i', [$studentId]);

if (!$student) {
    set_flash('danger', 'Student not found.');
    redirect('students.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        set_flash('danger', 'Your session expired. Please try again.');
        redirect('students.php');
    }

    $conn->begin_transaction();
    $deleted = run_query($conn, 'DELETE FROM attendance WHERE student_id = ?', 'i', [$studentId])
        && run_query($conn, 'DELETE FROM marks WHERE student_id = ?', 'i', [$studentId])
        && run_query($conn, 'DELETE FROM students WHERE id = ?', 'i', [$studentId]);

    if ($deleted) {
        $conn->commit();
        delete_student_photo($student['photo']);
        set_flash('success', 'Student deleted successfully.');
    } else {
        $conn->rollback();
        set_flash('danger', 'Unable to delete student. Please try again.');
    }

    redirect('students.php');
}

$activeMenu = 'students';
$pageTitle = 'Delete Student';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card confirm-box">
    <h2>Are you sure you want to delete this student?</h2>
    <p>
        <strong><?= e($student['full_name']) ?></strong> (USN: <?= e($student['usn']) ?>)<br>
        All attendance and marks records of this student will also be deleted.
    </p>
    <form method="POST" action="delete_student.php?id=<?= (int) $student['id'] ?>">
        <?= csrf_field() ?>
        <div class="button-row" style="justify-content: center;">
            <button type="submit" class="btn btn-danger">Yes, Delete</button>
            <a href="students.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
