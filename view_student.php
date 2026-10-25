<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$studentId = (int) ($_GET['id'] ?? 0);

$student = fetch_row($conn, "SELECT s.*, d.department_name, c.course_name, c.course_code
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE s.id = ?", 'i', [$studentId]);

if (!$student) {
    set_flash('danger', 'Student not found.');
    redirect('students.php');
}

$attendance = fetch_row($conn, "SELECT COUNT(*) AS total_classes,
    COALESCE(SUM(status = 'Present'), 0) AS present_count,
    COALESCE(SUM(status = 'Absent'), 0) AS absent_count
    FROM attendance WHERE student_id = ?", 'i', [$studentId]);

$totalClasses = (int) ($attendance['total_classes'] ?? 0);
$presentCount = (int) ($attendance['present_count'] ?? 0);
$absentCount = (int) ($attendance['absent_count'] ?? 0);
$attendancePercentage = percentage($presentCount, $totalClasses);

$marksList = fetch_rows($conn, 'SELECT subject, exam_type, internal_marks, external_marks, total_marks, grade
    FROM marks WHERE student_id = ? ORDER BY subject, id', 'i', [$studentId]);

$activeMenu = 'students';
$pageTitle = 'Student Details';
require_once __DIR__ . '/includes/header.php';
?>

<?php show_flash(); ?>

<div class="card">
    <div class="profile-header">
        <?php render_avatar($student['photo'], $student['full_name'], 'avatar avatar-large'); ?>
        <div>
            <h2 style="font-size: 26px;"><?= e($student['full_name']) ?></h2>
            <p class="stat-label">USN: <strong><?= e($student['usn']) ?></strong> &middot; Student ID: <?= (int) $student['id'] ?></p>
            <div class="button-row" style="margin-top: 12px;">
                <a href="edit_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-secondary btn-small">Edit</a>
                <a href="delete_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-danger btn-small">Delete</a>
                <a href="reports.php?type=student&amp;student_id=<?= (int) $student['id'] ?>" class="btn btn-outline btn-small">Student Report</a>
                <a href="students.php" class="btn btn-outline btn-small">Back to Students</a>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item"><span>Date of Birth</span><strong><?= e($student['dob']) ?></strong></div>
        <div class="info-item"><span>Gender</span><strong><?= e($student['gender']) ?></strong></div>
        <div class="info-item"><span>Email</span><strong><?= e($student['email']) ?></strong></div>
        <div class="info-item"><span>Phone</span><strong><?= e($student['phone']) ?></strong></div>
        <div class="info-item"><span>Address</span><strong><?= e($student['address']) ?></strong></div>
        <div class="info-item"><span>Department</span><strong><?= e($student['department_name'] ?? '-') ?></strong></div>
        <div class="info-item"><span>Course</span><strong><?= e(($student['course_name'] ?? '-') . ($student['course_code'] ? ' (' . $student['course_code'] . ')' : '')) ?></strong></div>
        <div class="info-item"><span>Semester</span><strong><?= e($student['semester']) ?></strong></div>
        <div class="info-item"><span>Admission Year</span><strong><?= e($student['admission_year']) ?></strong></div>
    </div>
</div>

<div class="card">
    <h2>Attendance Summary</h2>
    <div class="summary-cards">
        <div class="stat-card"><div class="stat-label">Total Classes</div><div class="stat-value"><?= $totalClasses ?></div></div>
        <div class="stat-card green"><div class="stat-label">Present</div><div class="stat-value"><?= $presentCount ?></div></div>
        <div class="stat-card red"><div class="stat-label">Absent</div><div class="stat-value"><?= $absentCount ?></div></div>
        <div class="stat-card orange"><div class="stat-label">Attendance %</div><div class="stat-value"><?= e($attendancePercentage) ?>%</div></div>
    </div>
</div>

<div class="card">
    <h2>Marks Summary</h2>
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Subject</th>
                <th>Exam Type</th>
                <th>Internal</th>
                <th>External</th>
                <th>Total</th>
                <th>Grade</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($marksList)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No marks recorded yet.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($marksList as $mark): ?>
                <tr>
                    <td><?= e($mark['subject']) ?></td>
                    <td><?= e($mark['exam_type']) ?></td>
                    <td><?= e($mark['internal_marks']) ?></td>
                    <td><?= e($mark['external_marks']) ?></td>
                    <td><?= e($mark['total_marks']) ?></td>
                    <td><strong><?= e($mark['grade']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
