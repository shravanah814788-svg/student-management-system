<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

function calculate_grade($totalMarks)
{
    if ($totalMarks >= 90) {
        return 'A+';
    }
    if ($totalMarks >= 80) {
        return 'A';
    }
    if ($totalMarks >= 70) {
        return 'B+';
    }
    if ($totalMarks >= 60) {
        return 'B';
    }
    if ($totalMarks >= 50) {
        return 'C';
    }
    if ($totalMarks >= 40) {
        return 'D';
    }
    return 'F';
}

$errors = [];
$students = fetch_rows($conn, 'SELECT id, usn, full_name FROM students ORDER BY usn');
$examTypes = ['Internal Test 1', 'Internal Test 2', 'Semester Exam'];

$formStudentId = (int) ($_POST['student_id'] ?? 0);
$formSubject = trim($_POST['subject'] ?? '');
$formExamType = $_POST['exam_type'] ?? 'Internal Test 1';
$formInternal = $_POST['internal_marks'] ?? '';
$formExternal = $_POST['external_marks'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        set_flash('danger', 'Your session expired. Please try again.');
        redirect('marks.php');
    }

    if (($_POST['action'] ?? '') === 'delete') {
        $markId = (int) ($_POST['mark_id'] ?? 0);
        if (run_query($conn, 'DELETE FROM marks WHERE id = ?', 'i', [$markId])) {
            set_flash('success', 'Marks record deleted.');
        } else {
            set_flash('danger', 'Unable to delete marks record.');
        }
        redirect('marks.php');
    }

    if ($formStudentId <= 0 || !fetch_row($conn, 'SELECT id FROM students WHERE id = ?', 'i', [$formStudentId])) {
        $errors[] = 'Please select a valid student.';
    }

    if ($formSubject === '') {
        $errors[] = 'Subject is required.';
    } elseif (strlen($formSubject) > 100) {
        $errors[] = 'Subject must be 100 characters or less.';
    }

    if (!in_array($formExamType, $examTypes, true)) {
        $errors[] = 'Please select a valid exam type.';
    }

    if ($formInternal === '' || !is_numeric($formInternal) || $formInternal < 0 || $formInternal > 30) {
        $errors[] = 'Internal marks must be a number between 0 and 30.';
    }

    if ($formExternal === '' || !is_numeric($formExternal) || $formExternal < 0 || $formExternal > 70) {
        $errors[] = 'External marks must be a number between 0 and 70.';
    }

    if (empty($errors)) {
        $internalMarks = (int) $formInternal;
        $externalMarks = (int) $formExternal;
        $totalMarks = $internalMarks + $externalMarks;
        $grade = calculate_grade($totalMarks);

        $saved = run_query(
            $conn,
            'INSERT INTO marks (student_id, subject, internal_marks, external_marks, total_marks, grade, exam_type) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'isiiiss',
            [$formStudentId, $formSubject, $internalMarks, $externalMarks, $totalMarks, $grade, $formExamType]
        );

        if ($saved) {
            set_flash('success', 'Marks saved successfully.');
            redirect('marks.php');
        }
        $errors[] = 'Unable to save marks.';
    }
}

$filterStudentId = (int) ($_GET['student_id'] ?? 0);

$sql = "SELECT m.id, m.subject, m.exam_type, m.internal_marks, m.external_marks, m.total_marks, m.grade, s.usn, s.full_name
    FROM marks m
    JOIN students s ON m.student_id = s.id
    WHERE 1=1";
$types = '';
$params = [];

if ($filterStudentId > 0) {
    $sql .= " AND m.student_id = ?";
    $types .= 'i';
    $params[] = $filterStudentId;
}

$sql .= " ORDER BY s.usn, m.subject, m.id";
$records = fetch_rows($conn, $sql, $types, $params);

$activeMenu = 'marks';
$pageTitle = 'Marks';
require_once __DIR__ . '/includes/header.php';
?>

<?php show_flash(); ?>

<div class="card">
    <h2>Enter Marks</h2>
    <?php show_errors($errors); ?>
    <form method="POST" action="marks.php">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="student_id">Student *</label>
                <select id="student_id" name="student_id" required>
                    <option value="">Select student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int) $student['id'] ?>" <?= $formStudentId === (int) $student['id'] ? 'selected' : '' ?>>
                            <?= e($student['usn']) ?> - <?= e($student['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Subject *</label>
                <input type="text" id="subject" name="subject" maxlength="100" required value="<?= e($formSubject) ?>" placeholder="Data Structures">
            </div>
            <div class="form-group">
                <label for="exam_type">Exam Type *</label>
                <select id="exam_type" name="exam_type" required>
                    <?php foreach ($examTypes as $examType): ?>
                        <option value="<?= e($examType) ?>" <?= $formExamType === $examType ? 'selected' : '' ?>><?= e($examType) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="internal_marks">Internal Marks (0 to 30) *</label>
                <input type="number" id="internal_marks" name="internal_marks" min="0" max="30" step="1" required value="<?= e($formInternal) ?>">
            </div>
            <div class="form-group">
                <label for="external_marks">External Marks (0 to 70) *</label>
                <input type="number" id="external_marks" name="external_marks" min="0" max="70" step="1" required value="<?= e($formExternal) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Save Marks</button>
        </div>
    </form>
    <p class="stat-label" style="margin-top: 14px;">Total = Internal + External (out of 100). Grade: 90-100 A+, 80-89 A, 70-79 B+, 60-69 B, 50-59 C, 40-49 D, below 40 F.</p>
</div>

<div class="card">
    <h2>Marks Records</h2>
    <form method="GET" action="marks.php" class="filter-form">
        <div class="form-group">
            <label for="filter_student">Filter by student</label>
            <select id="filter_student" name="student_id">
                <option value="0">All students</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= (int) $student['id'] ?>" <?= $filterStudentId === (int) $student['id'] ? 'selected' : '' ?>>
                        <?= e($student['usn']) ?> - <?= e($student['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="button-row">
            <button type="submit" class="btn">Filter</button>
            <a href="marks.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

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
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="9" class="empty-state">No marks records found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= e($record['usn']) ?></td>
                    <td><?= e($record['full_name']) ?></td>
                    <td><?= e($record['subject']) ?></td>
                    <td><?= e($record['exam_type']) ?></td>
                    <td><?= e($record['internal_marks']) ?></td>
                    <td><?= e($record['external_marks']) ?></td>
                    <td><strong><?= e($record['total_marks']) ?></strong></td>
                    <td><span class="badge"><?= e($record['grade']) ?></span></td>
                    <td>
                        <form method="POST" action="marks.php" data-confirm="Delete this marks record?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="mark_id" value="<?= (int) $record['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
