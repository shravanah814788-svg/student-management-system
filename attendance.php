<?php

date_default_timezone_set('Asia/Kolkata');

session_start();

require_once "config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

$today = date("Y-m-d");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = isset($_POST["student_id"]) ? (int) $_POST["student_id"] : 0;
    $subject = trim($_POST["subject"] ?? "");
    $attendance_date = $_POST["attendance_date"] ?? "";
    $status = $_POST["status"] ?? "";

    if ($student_id <= 0) {
        $error = "Please select a student.";
    } elseif ($subject === "") {
        $error = "Please enter the subject.";
    } elseif ($attendance_date === "") {
        $error = "Please select the attendance date.";
    } elseif ($status !== "Present" && $status !== "Absent") {
        $error = "Please select a valid attendance status.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendance_date)) {
        $error = "Invalid attendance date.";
    } elseif ($attendance_date > $today) {
        $error = "Attendance date cannot be in the future.";
    } else {

        $checkStudent = $conn->prepare(
            "SELECT id FROM students WHERE id = ?"
        );

        $checkStudent->bind_param("i", $student_id);
        $checkStudent->execute();

        $studentResult = $checkStudent->get_result();

        if ($studentResult->num_rows !== 1) {

            $error = "Selected student does not exist.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO attendance 
                (student_id, subject, attendance_date, status)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isss",
                $student_id,
                $subject,
                $attendance_date,
                $status
            );

            if ($stmt->execute()) {
                $message = "Attendance saved successfully.";

                $student_id = 0;
                $subject = "";
                $attendance_date = $today;
                $status = "Present";
            } else {
                $error = "Failed to save attendance. " . $conn->error;
            }

            $stmt->close();
        }

        $checkStudent->close();
    }
}

$students = [];

$studentQuery = $conn->query(
    "SELECT id, usn, full_name
     FROM students
     ORDER BY full_name ASC"
);

if ($studentQuery) {
    while ($row = $studentQuery->fetch_assoc()) {
        $students[] = $row;
    }
}

$attendanceRecords = [];

$attendanceQuery = $conn->query(
    "SELECT 
        attendance.id,
        attendance.subject,
        attendance.attendance_date,
        attendance.status,
        students.usn,
        students.full_name
     FROM attendance
     INNER JOIN students
        ON attendance.student_id = students.id
     ORDER BY attendance.attendance_date DESC, attendance.id DESC
     LIMIT 100"
);

if ($attendanceQuery) {
    while ($row = $attendanceQuery->fetch_assoc()) {
        $attendanceRecords[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Attendance - Student Management System</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
        }

        .back-btn {
            text-decoration: none;
            background: #333;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h2 {
            margin-top: 0;
        }

        .alert {
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .btn {
            border: none;
            background: #16a34a;
            color: white;
            padding: 12px 22px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background: #15803d;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f5f9;
        }

        .present {
            color: #15803d;
            font-weight: bold;
        }

        .absent {
            color: #dc2626;
            font-weight: bold;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="page-header">

        <h1>Mark Attendance</h1>

        <a href="dashboard.php" class="back-btn">
            Dashboard
        </a>

    </div>

    <?php if ($message !== ""): ?>

        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <div class="card">

        <h2>Add Attendance</h2>

        <form method="POST" action="attendance.php">

            <div class="form-grid">

                <div class="form-group">

                    <label for="student_id">
                        Student *
                    </label>

                    <select
                        id="student_id"
                        name="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>

                        <?php foreach ($students as $student): ?>

                            <option
                                value="<?php echo (int) $student["id"]; ?>"
                                <?php
                                if (
                                    isset($student_id) &&
                                    $student_id == $student["id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $student["usn"] . " - " . $student["full_name"]
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="subject">
                        Subject *
                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        placeholder="Enter subject"
                        required
                        value="<?php
                            echo htmlspecialchars($subject ?? "");
                        ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="attendance_date">
                        Date *
                    </label>

                    <input
                        type="date"
                        id="attendance_date"
                        name="attendance_date"
                        required
                        max="<?php echo $today; ?>"
                        value="<?php
                            echo htmlspecialchars(
                                $attendance_date ?? $today
                            );
                        ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="status">
                        Status *
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Present"
                            <?php
                            echo (
                                ($status ?? "Present") === "Present"
                                ? "selected"
                                : ""
                            );
                            ?>
                        >
                            Present
                        </option>

                        <option
                            value="Absent"
                            <?php
                            echo (
                                ($status ?? "") === "Absent"
                                ? "selected"
                                : ""
                            );
                            ?>
                        >
                            Absent
                        </option>

                    </select>

                </div>


                <div class="full-width">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Save Attendance
                    </button>

                </div>

            </div>

        </form>

    </div>


    <div class="card">

        <h2>Attendance Records</h2>

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>USN</th>

                        <th>Student Name</th>

                        <th>Subject</th>

                        <th>Date</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php if (count($attendanceRecords) > 0): ?>

                    <?php foreach ($attendanceRecords as $record): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars($record["usn"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($record["full_name"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($record["subject"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    date(
                                        "d-m-Y",
                                        strtotime($record["attendance_date"])
                                    )
                                );
                                ?>
                            </td>

                            <td>

                                <?php if ($record["status"] === "Present"): ?>

                                    <span class="present">
                                        Present
                                    </span>

                                <?php else: ?>

                                    <span class="absent">
                                        Absent
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5">
                            No attendance records found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>