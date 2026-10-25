<?php
function read_student_input()
{
    return [
        'usn' => strtoupper(trim($_POST['usn'] ?? '')),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'dob' => trim($_POST['dob'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => str_replace(' ', '', trim($_POST['phone'] ?? '')),
        'address' => trim($_POST['address'] ?? ''),
        'department_id' => (int) ($_POST['department_id'] ?? 0),
        'course_id' => (int) ($_POST['course_id'] ?? 0),
        'semester' => (int) ($_POST['semester'] ?? 0),
        'admission_year' => (int) ($_POST['admission_year'] ?? 0),
    ];
}

function validate_student(mysqli $conn, array $input, $ignoreId = null)
{
    $errors = [];

    if ($input['usn'] === '') {
        $errors[] = 'USN is required.';
    } elseif (!preg_match('/^[A-Z0-9]{5,20}$/', $input['usn'])) {
        $errors[] = 'USN must be 5 to 20 letters or numbers.';
    } else {
        $existing = fetch_row($conn, 'SELECT id FROM students WHERE usn = ? AND id <> ? LIMIT 1', 'si', [$input['usn'], (int) $ignoreId]);
        if ($existing) {
            $errors[] = 'This USN is already registered.';
        }
    }

    if ($input['full_name'] === '') {
        $errors[] = 'Full name is required.';
    } elseif (!preg_match("/^[A-Za-z][A-Za-z .'-]{1,99}$/", $input['full_name'])) {
        $errors[] = 'Full name can only contain letters, spaces, dots, hyphens and apostrophes.';
    }

    if ($input['dob'] === '') {
        $errors[] = 'Date of birth is required.';
    } else {
        $dateOfBirth = DateTime::createFromFormat('Y-m-d', $input['dob']);
        if (!$dateOfBirth || $dateOfBirth->format('Y-m-d') !== $input['dob']) {
            $errors[] = 'Date of birth is not a valid date.';
        } elseif ($dateOfBirth > new DateTime('today')) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
    }

    if (!in_array($input['gender'], ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if ($input['email'] === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is not valid.';
    }

    if ($input['phone'] === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $input['phone'])) {
        $errors[] = 'Phone number must be exactly 10 digits.';
    }

    if ($input['address'] === '') {
        $errors[] = 'Address is required.';
    }

    $department = $input['department_id'] > 0
        ? fetch_row($conn, 'SELECT id FROM departments WHERE id = ?', 'i', [$input['department_id']])
        : null;
    if (!$department) {
        $errors[] = 'Please select a valid department.';
    }

    $course = $input['course_id'] > 0
        ? fetch_row($conn, 'SELECT id, department_id FROM courses WHERE id = ?', 'i', [$input['course_id']])
        : null;
    if (!$course) {
        $errors[] = 'Please select a valid course.';
    } elseif ((int) $course['department_id'] !== $input['department_id']) {
        $errors[] = 'The selected course does not belong to the selected department.';
    }

    if ($input['semester'] < 1 || $input['semester'] > 8) {
        $errors[] = 'Semester must be between 1 and 8.';
    }

    $currentYear = (int) date('Y');
    if ($input['admission_year'] < 2000 || $input['admission_year'] > $currentYear) {
        $errors[] = 'Admission year must be between 2000 and ' . $currentYear . '.';
    }

    return $errors;
}

function save_student_photo(array $file)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Photo upload failed. Please try again.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'Photo must be 2 MB or smaller.'];
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        return [null, 'Photo must be a JPG, PNG or WEBP image.'];
    }

    $directory = __DIR__ . '/../uploads/students/';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $fileName = uniqid('student_', true) . '.' . $allowedTypes[$mimeType];

    if (!move_uploaded_file($file['tmp_name'], $directory . $fileName)) {
        return [null, 'Unable to save the photo. Check the uploads/students folder.'];
    }

    return [$fileName, null];
}

function delete_student_photo($fileName)
{
    if (!empty($fileName)) {
        $path = __DIR__ . '/../uploads/students/' . basename($fileName);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
