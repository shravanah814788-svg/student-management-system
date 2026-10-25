<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$departmentId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$department = fetch_row($conn, 'SELECT * FROM departments WHERE id = ?', 'i', [$departmentId]);

if (!$department) {
    set_flash('danger', 'Department not found.');
    redirect('departments.php');
}

$errors = [];
$departmentName = $department['department_name'];
$departmentCode = $department['department_code'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departmentName = trim($_POST['department_name'] ?? '');
    $departmentCode = strtoupper(trim($_POST['department_code'] ?? ''));

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        if ($departmentName === '') {
            $errors[] = 'Department name is required.';
        } elseif (strlen($departmentName) > 100) {
            $errors[] = 'Department name must be 100 characters or less.';
        }

        if ($departmentCode === '') {
            $errors[] = 'Department code is required.';
        } elseif (!preg_match('/^[A-Z0-9]{2,10}$/', $departmentCode)) {
            $errors[] = 'Department code must be 2 to 10 letters or numbers.';
        } elseif (fetch_row($conn, 'SELECT id FROM departments WHERE department_code = ? AND id <> ? LIMIT 1', 'si', [$departmentCode, $departmentId])) {
            $errors[] = 'This department code already exists.';
        }

        if (empty($errors)) {
            if (run_query($conn, 'UPDATE departments SET department_name = ?, department_code = ? WHERE id = ?', 'ssi', [$departmentName, $departmentCode, $departmentId])) {
                set_flash('success', 'Department updated successfully.');
                redirect('departments.php');
            }
            $errors[] = 'Unable to update department.';
        }
    }
}

$activeMenu = 'departments';
$pageTitle = 'Edit Department';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <h2>Edit Department</h2>
    <?php show_errors($errors); ?>
    <form method="POST" action="edit_department.php?id=<?= $departmentId ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="department_name">Department Name *</label>
                <input type="text" id="department_name" name="department_name" maxlength="100" required value="<?= e($departmentName) ?>">
            </div>
            <div class="form-group">
                <label for="department_code">Department Code *</label>
                <input type="text" id="department_code" name="department_code" maxlength="10" required value="<?= e($departmentCode) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Update Department</button>
            <a href="departments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
