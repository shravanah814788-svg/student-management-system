<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$courses = fetch_rows($conn, "SELECT c.id, c.course_name, c.course_code, c.duration, c.created_at, d.department_name, d.department_code,
    (SELECT COUNT(*) FROM students s WHERE s.course_id = c.id) AS student_count
    FROM courses c
    LEFT JOIN departments d ON c.department_id = d.id
    ORDER BY c.course_name");

$activeMenu = 'courses';
$pageTitle = 'Courses';
require_once __DIR__ . '/includes/header.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <h2>All Courses (<?= count($courses) ?>)</h2>
    <a href="add_course.php" class="btn btn-success">+ Add Course</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Course Name</th>
                <th>Code</th>
                <th>Department</th>
                <th>Duration</th>
                <th>Students</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($courses)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No courses yet. Add your first course.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><strong><?= e($course['course_name']) ?></strong></td>
                    <td><span class="badge"><?= e($course['course_code']) ?></span></td>
                    <td><?= e($course['department_name'] ?? '-') ?></td>
                    <td><?= e($course['duration']) ?></td>
                    <td><?= (int) $course['student_count'] ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="edit_course.php?id=<?= (int) $course['id'] ?>" class="btn btn-small btn-secondary">Edit</a>
                            <form method="POST" action="delete_course.php" data-confirm="Are you sure you want to delete this course?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $course['id'] ?>">
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
