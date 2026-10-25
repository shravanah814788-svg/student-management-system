<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$reportType = $_GET['type'] ?? 'student';
if (!in_array($reportType, ['student', 'attendance', 'marks'], true)) {
    $reportType = 'student';
}

$students = fetch_rows($conn, 'SELECT id, usn, full_name FROM students ORDER BY usn');
$selectedStudentId = (int) ($_GET['student_id'] ?? 0);

$studentReport = null;
$attendance = null;
$marksList = [];

if ($reportType === 'student' && $selectedStudentId > 0) {
    $studentReport = fetch_row($conn, "SELECT s.*, d.department_name, c.course_name, c.course_code
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        LEFT JOIN courses c ON s.course_id = c.id
        WHERE s.id = ?", 'i', [$selectedStudentId]);

    if ($studentReport) {
        $attendance = fetch_row($conn, "SELECT COUNT(*) AS total_classes,
            COALESCE(SUM(status = 'Present'), 0) AS present_count,
            COALESCE(SUM(status = 'Absent'), 0) AS absent_count
            FROM attendance WHERE student_id = ?", 'i', [$selectedStudentId]);

        $marksList = fetch_rows($conn, 'SELECT subject, exam_type, internal_marks, external_marks, total_marks, grade
            FROM marks WHERE student_id = ? ORDER BY subject, id', 'i', [$selectedStudentId]);
    }
}

$attendanceReport = [];
if ($reportType === 'attendance') {
    $attendanceReport = fetch_rows($conn, "SELECT s.usn, s.full_name, d.department_code,
        COUNT(a.id) AS total_classes,
        COALESCE(SUM(a.status = 'Present'), 0) AS present_count,
        COALESCE(SUM(a.status = 'Absent'), 0) AS absent_count
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        LEFT JOIN attendance a ON a.student_id = s.id
        GROUP BY s.id, s.usn, s.full_name, d.department_code
        ORDER BY s.usn");
}

$marksReport = [];
if ($reportType === 'marks') {
    $marksReport = fetch_rows($conn, "SELECT s.usn, s.full_name, m.subject, m.exam_type, m.internal_marks, m.external_marks, m.total_marks, m.grade
        FROM marks m
        JOIN students s ON m.student_id = s.id
        ORDER BY s.usn, m.subject, m.id");
}

$activeMenu = 'reports';
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card no-print">
    <div class="report-tabs">
        <a href="reports.php?type=student" class="<?= $reportType === 'student' ? 'active' : '' ?>">Student Report</a>
        <a href="reports.php?type=attendance" class="<?= $reportType === 'attendance' ? 'active' : '' ?>">Attendance Report</a>
        <a href="reports.php?type=marks" class="<?= $reportType === 'marks' ? 'active' : '' ?>">Marks Report</a>
    </div>

    <?php if ($reportType === 'student'): ?>
        <form method="GET" action="reports.php" class="filter-form">
            <input type="hidden" name="type" value="student">
            <div class="form-group">
                <label for="student_id">Select student</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Choose student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int) $student['id'] ?>" <?= $selectedStudentId === (int) $student['id'] ? 'selected' : '' ?>>
                            <?= e($student['usn']) ?> - <?= e($student['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="button-row">
                <button type="submit" class="btn">Show Report</button>
            </div>
        </form>
    <?php endif; ?>

    <div class="button-row">
        <button type="button" class="btn btn-success" onclick="window.print()">Print Report</button>
    </div>
</div>

<?php if ($reportType === 'student' && $studentReport): ?>
    <?php
    $totalClasses = (int) $attendance['total_classes'];
    $presentCount = (int) $attendance['present_count'];
    $absentCount = (int) $attendance['absent_count'];
    $attendancePercentage = percentage($presentCount, $totalClasses);
    ?>
    <div class="card printable-report">
        <div class="report-header">
            <h2>Student Management System</h2>
            <p>Student Report</p>
        </div>
        <div class="report-date">Date: <?= e(date('d M Y')) ?></div>

        <div class="report-section">
            <h3>Student Information</h3>
            <div class="info-grid">
                <div class="info-item"><span>Student ID</span><strong><?= (int) $studentReport['id'] ?></strong></div>
                <div class="info-item"><span>USN</span><strong><?= e($studentReport['usn']) ?></strong></div>
                <div class="info-item"><span>Name</span><strong><?= e($studentReport['full_name']) ?></strong></div>
                <div class="info-item"><span>Date of Birth</span><strong><?= e($studentReport['dob']) ?></strong></div>
                <div class="info-item"><span>Gender</span><strong><?= e($studentReport['gender']) ?></strong></div>
                <div class="info-item"><span>Email</span><strong><?= e($studentReport['email']) ?></strong></div>
                <div class="info-item"><span>Phone</span><strong><?= e($studentReport['phone']) ?></strong></div>
                <div class="info-item"><span>Department</span><strong><?= e($studentReport['department_name'] ?? '-') ?></strong></div>
                <div class="info-item"><span>Course</span><strong><?= e($studentReport['course_name'] ?? '-') ?></strong></div>
                <div class="info-item"><span>Semester</span><strong><?= e($studentReport['semester']) ?></strong></div>
                <div class="info-item"><span>Admission Year</span><strong><?= e($studentReport['admission_year']) ?></strong></div>
            </div>
        </div>

        <div class="report-section">
            <h3>Attendance Summary</h3>
            <div class="summary-cards">
                <div class="info-item"><span>Total Classes</span><strong><?= $totalClasses ?></strong></div>
                <div class="info-item"><span>Present</span><strong><?= $presentCount ?></strong></div>
                <div class="info-item"><span>Absent</span><strong><?= $absentCount ?></strong></div>
                <div class="info-item"><span>Attendance %</span><strong><?= e($attendancePercentage) ?>%</strong></div>
            </div>
        </div>

        <div class="report-section">
            <h3>Marks Summary</h3>
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
                            <td colspan="6" class="empty-state">No marks recorded.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($marksList as $mark): ?>
                        <tr>
                            <td><?= e($mark['subject']) ?></td>
                            <td><?= e($mark['exam_type']) ?></td>
                            <td><?= e($mark['internal_marks']) ?></td>
                            <td><?= e($mark['external_marks']) ?></td>
                            <td><?= e($mark['total_marks']) ?></td>
                            <td><?= e($mark['grade']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($reportType === 'student'): ?>
    <div class="card printable-report">
        <p class="empty-state">Select a student above to view the student report.</p>
    </div>
<?php endif; ?>

<?php if ($reportType === 'attendance'): ?>
    <div class="card printable-report">
        <div class="report-header">
            <h2>Student Management System</h2>
            <p>Attendance Report</p>
        </div>
        <div class="report-date">Date: <?= e(date('d M Y')) ?></div>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>USN</th>
                    <th>Student</th>
                    <th>Department</th>
                    <th>Total Classes</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Percentage</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($attendanceReport)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No students found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($attendanceReport as $row): ?>
                    <?php $rowPercent = percentage($row['present_count'], $row['total_classes']); ?>
                    <tr>
                        <td><?= e($row['usn']) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['department_code'] ?? '-') ?></td>
                        <td><?= (int) $row['total_classes'] ?></td>
                        <td><?= (int) $row['present_count'] ?></td>
                        <td><?= (int) $row['absent_count'] ?></td>
                        <td class="<?= $rowPercent >= 75 ? 'percentage-good' : 'percentage-low' ?>"><?= e($rowPercent) ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($reportType === 'marks'): ?>
    <div class="card printable-report">
        <div class="report-header">
            <h2>Student Management System</h2>
            <p>Marks Report</p>
        </div>
        <div class="report-date">Date: <?= e(date('d M Y')) ?></div>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>USN</th>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Exam Type</th>
                    <th>Internal</th>
                    <th>External</th>
                    <th>Total</th>
                    <th>Grade</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($marksReport)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">No marks records found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($marksReport as $row): ?>
                    <tr>
                        <td><?= e($row['usn']) ?></td>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['subject']) ?></td>
                        <td><?= e($row['exam_type']) ?></td>
                        <td><?= e($row['internal_marks']) ?></td>
                        <td><?= e($row['external_marks']) ?></td>
                        <td><?= e($row['total_marks']) ?></td>
                        <td><?= e($row['grade']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
