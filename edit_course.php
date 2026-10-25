<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$courseId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$course = fetch_row($conn, 'SELECT * FROM courses WHERE id = ?', 'i', [$courseId]);

if (!$course) {
    set_flash('danger', 'Course not found.');
    redirect('courses.php');
}

$errors = [];
$departments = fetch_rows($conn, 'SELECT id, department_name, department_code FROM departments ORDER BY department_name');
$courseName = $course['course_name'];
$courseCode = $course['course_code'];
$departmentId = (int) $course['department_id'];
$duration = $course['duration'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = trim($_POST['course_name'] ?? '');
    $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
    $departmentId = (int) ($_POST['department_id'] ?? 0);
    $duration = trim($_POST['duration'] ?? '');

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        if ($courseName === '') {
            $errors[] = 'Course name is required.';
        } elseif (strlen($courseName) > 100) {
            $errors[] = 'Course name must be 100 characters or less.';
        }

        if ($courseCode === '') {
            $errors[] = 'Course code is required.';
        } elseif (!preg_match('/^[A-Z0-9]{2,15}$/', $courseCode)) {
            $errors[] = 'Course code must be 2 to 15 letters or numbers.';
        } elseif (fetch_row($conn, 'SELECT id FROM courses WHERE course_code = ? AND id <> ? LIMIT 1', 'si', [$courseCode, $courseId])) {
            $errors[] = 'This course code already exists.';
        }

        if ($departmentId <= 0 || !fetch_row($conn, 'SELECT id FROM departments WHERE id = ?', 'i', [$departmentId])) {
            $errors[] = 'Please select a valid department.';
        }

        if ($duration === '') {
            $errors[] = 'Duration is required.';
        } elseif (strlen($duration) > 30) {
            $errors[] = 'Duration must be 30 characters or less.';
        }

        if (empty($errors)) {
            if (run_query($conn, 'UPDATE courses SET course_name = ?, course_code = ?, department_id = ?, duration = ? WHERE id = ?', 'ssisi', [$courseName, $courseCode, $departmentId, $duration, $courseId])) {
                set_flash('success', 'Course updated successfully.');
                redirect('courses.php');
            }
            $errors[] = 'Unable to update course.';
        }
    }
}

$activeMenu = 'courses';
$pageTitle = 'Edit Course';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <h2>Edit Course</h2>
    <?php show_errors($errors); ?>
    <form method="POST" action="edit_course.php?id=<?= $courseId ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="course_name">Course Name *</label>
                <input type="text" id="course_name" name="course_name" maxlength="100" required value="<?= e($courseName) ?>">
            </div>
            <div class="form-group">
                <label for="course_code">Course Code *</label>
                <input type="text" id="course_code" name="course_code" maxlength="15" required value="<?= e($courseCode) ?>">
            </div>
            <div class="form-group">
                <label for="department_id">Department *</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= (int) $department['id'] ?>" <?= $departmentId === (int) $department['id'] ? 'selected' : '' ?>>
                            <?= e($department['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="duration">Duration *</label>
                <input type="text" id="duration" name="duration" maxlength="30" required value="<?= e($duration) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Update Course</button>
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
