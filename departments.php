<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$departments = fetch_rows($conn, "SELECT d.id, d.department_name, d.department_code, d.created_at,
    (SELECT COUNT(*) FROM courses c WHERE c.department_id = d.id) AS course_count,
    (SELECT COUNT(*) FROM students s WHERE s.department_id = d.id) AS student_count
    FROM departments d
    ORDER BY d.department_name");

$activeMenu = 'departments';
$pageTitle = 'Departments';
require_once __DIR__ . '/includes/header.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <h2>All Departments (<?= count($departments) ?>)</h2>
    <a href="add_department.php" class="btn btn-success">+ Add Department</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Department Name</th>
                <th>Code</th>
                <th>Courses</th>
                <th>Students</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($departments)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No departments yet. Add your first department.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($departments as $department): ?>
                <tr>
                    <td><strong><?= e($department['department_name']) ?></strong></td>
                    <td><span class="badge"><?= e($department['department_code']) ?></span></td>
                    <td><?= (int) $department['course_count'] ?></td>
                    <td><?= (int) $department['student_count'] ?></td>
                    <td><?= e(date('d M Y', strtotime($department['created_at']))) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="edit_department.php?id=<?= (int) $department['id'] ?>" class="btn btn-small btn-secondary">Edit</a>
                            <form method="POST" action="delete_department.php" data-confirm="Are you sure you want to delete this department?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $department['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
