<?php
$formAction = $formAction ?? 'add_student.php';
$submitLabel = $submitLabel ?? 'Save Student';
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label for="usn">USN *</label>
            <input type="text" id="usn" name="usn" maxlength="20" required value="<?= e($student['usn']) ?>" placeholder="1AB23CS001">
        </div>
        <div class="form-group">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" required value="<?= e($student['full_name']) ?>">
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth *</label>
            <input type="date" id="dob" name="dob" required value="<?= e($student['dob']) ?>">
        </div>
        <div class="form-group">
            <label for="gender">Gender *</label>
            <select id="gender" name="gender" required>
                <option value="">Select gender</option>
                <?php foreach (['Male', 'Female', 'Other'] as $genderOption): ?>
                    <option value="<?= e($genderOption) ?>" <?= $student['gender'] === $genderOption ? 'selected' : '' ?>><?= e($genderOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" maxlength="120" required value="<?= e($student['email']) ?>">
        </div>
        <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="tel" id="phone" name="phone" maxlength="15" required value="<?= e($student['phone']) ?>" placeholder="10 digit number">
        </div>
        <div class="form-group">
            <label for="department_id">Department *</label>
            <select id="department_id" name="department_id" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>" <?= (int) $student['department_id'] === (int) $department['id'] ? 'selected' : '' ?>>
                        <?= e($department['department_name']) ?> (<?= e($department['department_code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="course_id">Course *</label>
            <select id="course_id" name="course_id" required>
                <option value="">Select course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= (int) $course['id'] ?>" data-department-id="<?= (int) $course['department_id'] ?>" <?= (int) $student['course_id'] === (int) $course['id'] ? 'selected' : '' ?>>
                        <?= e($course['course_name']) ?> (<?= e($course['course_code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="semester">Semester *</label>
            <select id="semester" name="semester" required>
                <option value="">Select semester</option>
                <?php for ($semesterNumber = 1; $semesterNumber <= 8; $semesterNumber++): ?>
                    <option value="<?= $semesterNumber ?>" <?= (int) $student['semester'] === $semesterNumber ? 'selected' : '' ?>><?= $semesterNumber ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="admission_year">Admission Year *</label>
            <input type="number" id="admission_year" name="admission_year" min="2000" max="<?= date('Y') ?>" required value="<?= e($student['admission_year']) ?>">
        </div>
        <div class="form-group">
            <label for="photo">Profile Photo (JPG, PNG or WEBP, max 2 MB)</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="form-group full-width">
            <label for="address">Address *</label>
            <textarea id="address" name="address" required><?= e($student['address']) ?></textarea>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success"><?= e($submitLabel) ?></button>
        <a href="students.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>
