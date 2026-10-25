<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/student_helpers.php';
require_login();

$studentId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$existingStudent = fetch_row($conn, 'SELECT * FROM students WHERE id = ?', 'i', [$studentId]);

if (!$existingStudent) {
    set_flash('danger', 'Student not found.');
    redirect('students.php');
}

$errors = [];
$departments = fetch_rows($conn, 'SELECT id, department_name, department_code FROM departments ORDER BY department_name');
$courses = fetch_rows($conn, 'SELECT id, course_name, course_code, department_id FROM courses ORDER BY course_name');

$student = [
    'usn' => $existingStudent['usn'],
    'full_name' => $existingStudent['full_name'],
    'dob' => $existingStudent['dob'],
    'gender' => $existingStudent['gender'],
    'email' => $existingStudent['email'],
    'phone' => $existingStudent['phone'],
    'address' => $existingStudent['address'],
    'department_id' => (int) $existingStudent['department_id'],
    'course_id' => (int) $existingStudent['course_id'],
    'semester' => (int) $existingStudent['semester'],
    'admission_year' => (int) $existingStudent['admission_year'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = read_student_input();

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $errors = validate_student($conn, $student, $studentId);

        $photoName = $existingStudent['photo'];
        $newPhotoUploaded = false;
        if (empty($errors) && !empty($_FILES['photo']['name'])) {
            [$uploadedPhoto, $photoError] = save_student_photo($_FILES['photo']);
            if ($photoError !== null) {
                $errors[] = $photoError;
            } else {
                $photoName = $uploadedPhoto;
                $newPhotoUploaded = true;
            }
        }

        if (empty($errors)) {
            $updated = run_query(
                $conn,
                'UPDATE students SET usn = ?, full_name = ?, dob = ?, gender = ?, email = ?, phone = ?, address = ?,
                 department_id = ?, course_id = ?, semester = ?, admission_year = ?, photo = ?
                 WHERE id = ?',
                'sssssssiiiisi',
                [
                    $student['usn'],
                    $student['full_name'],
                    $student['dob'],
                    $student['gender'],
                    $student['email'],
                    $student['phone'],
                    $student['address'],
                    $student['department_id'],
                    $student['course_id'],
                    $student['semester'],
                    $student['admission_year'],
                    $photoName,
                    $studentId,
                ]
            );

            if ($updated) {
                if ($newPhotoUploaded) {
                    delete_student_photo($existingStudent['photo']);
                }
                set_flash('success', 'Student updated successfully.');
                redirect('view_student.php?id=' . $studentId);
            }

            if ($newPhotoUploaded) {
                delete_student_photo($photoName);
            }
            $errors[] = 'Unable to save student.';
        }
    }
}

$formAction = 'edit_student.php?id=' . $studentId;
$submitLabel = 'Update Student';
$activeMenu = 'students';
$pageTitle = 'Edit Student';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <h2>Edit Student: <?= e($existingStudent['full_name']) ?></h2>
    <?php show_errors($errors); ?>
    <?php require __DIR__ . '/includes/student_form.php'; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
