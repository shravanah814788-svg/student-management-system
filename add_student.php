<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/student_helpers.php';
require_login();

$errors = [];
$departments = fetch_rows($conn, 'SELECT id, department_name, department_code FROM departments ORDER BY department_name');
$courses = fetch_rows($conn, 'SELECT id, course_name, course_code, department_id FROM courses ORDER BY course_name');

$student = [
    'usn' => '',
    'full_name' => '',
    'dob' => '',
    'gender' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'department_id' => 0,
    'course_id' => 0,
    'semester' => 0,
    'admission_year' => (int) date('Y'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = read_student_input();

    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $errors = validate_student($conn, $student);

        $photoName = null;
        if (empty($errors) && !empty($_FILES['photo']['name'])) {
            [$photoName, $photoError] = save_student_photo($_FILES['photo']);
            if ($photoError !== null) {
                $errors[] = $photoError;
            }
        }

        if (empty($errors)) {
            $saved = run_query(
                $conn,
                'INSERT INTO students (usn, full_name, dob, gender, email, phone, address, department_id, course_id, semester, admission_year, photo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'sssssssiiiis',
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
                ]
            );

            if ($saved) {
                set_flash('success', 'Student added successfully.');
                redirect('students.php');
            }

            delete_student_photo($photoName);
            $errors[] = 'Unable to save student.';
        }
    }
}

$formAction = 'add_student.php';
$submitLabel = 'Add Student';
$activeMenu = 'add_student';
$pageTitle = 'Add Student';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <h2>New Student Details</h2>
    <?php show_errors($errors); ?>
    <?php require __DIR__ . '/includes/student_form.php'; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
