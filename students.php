<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$searchText = trim($_GET['q'] ?? '');
$departmentFilter = (int) ($_GET['department_id'] ?? 0);

$departments = fetch_rows($conn, 'SELECT id, department_name, department_code FROM departments ORDER BY department_name');

$sql = "SELECT s.id, s.usn, s.full_name, s.email, s.phone, s.semester, s.photo, d.department_code, c.course_code
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE 1=1";
$types = '';
$params = [];

if ($searchText !== '') {
    $likeText = '%' . $searchText . '%';
    $sql .= " AND (s.full_name LIKE ? OR s.usn LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
    $types .= 'ssss';
    array_push($params, $likeText, $likeText, $likeText, $likeText);
}

if ($departmentFilter > 0) {
    $sql .= " AND s.department_id = ?";
    $types .= 'i';
    $params[] = $departmentFilter;
}

$sql .= " ORDER BY s.usn";
$students = fetch_rows($conn, $sql, $types, $params);

$activeMenu = 'students';
$pageTitle = 'Students';
require_once __DIR__ . '/includes/header.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <h2>All Students (<?= count($students) ?>)</h2>
    <a href="add_student.php" class="btn btn-success">+ Add Student</a>
</div>

<div class="card">
    <form method="GET" action="students.php" class="filter-form">
        <div class="form-group">
            <label for="q">Search by name, USN, phone or email</label>
            <input type="text" id="q" name="q" value="<?= e($searchText) ?>" placeholder="Type to search">
        </div>
        <div class="form-group">
            <label for="department_id">Filter by department</label>
            <select id="department_id" name="department_id">
                <option value="0">All departments</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>" <?= $departmentFilter === (int) $department['id'] ? 'selected' : '' ?>>
                        <?= e($department['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="button-row">
            <button type="submit" class="btn">Search</button>
            <a href="students.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Photo</th>
                <th>USN</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <th>Course</th>
                <th>Sem</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="9" class="empty-state">No students found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php render_avatar($student['photo'], $student['full_name']); ?></td>
                    <td><strong><?= e($student['usn']) ?></strong></td>
                    <td><?= e($student['full_name']) ?></td>
                    <td><?= e($student['email']) ?></td>
                    <td><?= e($student['phone']) ?></td>
                    <td><?= e($student['department_code'] ?? '-') ?></td>
                    <td><?= e($student['course_code'] ?? '-') ?></td>
                    <td><?= e($student['semester']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="view_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-small btn-outline">View</a>
                            <a href="edit_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-small btn-secondary">Edit</a>
                            <a href="delete_student.php?id=<?= (int) $student['id'] ?>" class="btn btn-small btn-danger">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
