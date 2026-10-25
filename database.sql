CREATE DATABASE IF NOT EXISTS student_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_management;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS marks;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(15) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    duration VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_department FOREIGN KEY (department_id)
        REFERENCES departments (id) ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usn VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    department_id INT NOT NULL,
    course_id INT NOT NULL,
    semester TINYINT NOT NULL,
    admission_year INT NOT NULL,
    photo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_department FOREIGN KEY (department_id)
        REFERENCES departments (id) ON DELETE RESTRICT,
    CONSTRAINT fk_students_course FOREIGN KEY (course_id)
        REFERENCES courses (id) ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    internal_marks INT NOT NULL,
    external_marks INT NOT NULL,
    total_marks INT NOT NULL,
    grade VARCHAR(2) NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_marks_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE
) ENGINE = InnoDB;

INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$uDqSwHu55kE0XPlCXB/sIOav4..ie.zwxTkQ9Y7KNYlZB498rRnIC');

INSERT INTO departments (id, department_name, department_code) VALUES
(1, 'Computer Science', 'CS'),
(2, 'Information Science', 'IS'),
(3, 'Electronics and Communication', 'EC');

INSERT INTO courses (id, course_name, course_code, department_id, duration) VALUES
(1, 'Bachelor of Computer Applications', 'BCA', 1, '3 Years'),
(2, 'Bachelor of Engineering - Information Science', 'BEIS', 2, '4 Years'),
(3, 'Bachelor of Engineering - Electronics and Communication', 'BEEC', 3, '4 Years');

INSERT INTO students (id, usn, full_name, dob, gender, email, phone, address, department_id, course_id, semester, admission_year) VALUES
(1, '1AB23CS001', 'Rahul Kumar', '2005-03-14', 'Male', 'rahul.kumar@example.com', '9876543210', '12, MG Road, Davangere, Karnataka', 1, 1, 5, 2023),
(2, '1AB23CS002', 'Priya Sharma', '2005-07-22', 'Female', 'priya.sharma@example.com', '9876543211', '45, Station Road, Davangere, Karnataka', 1, 1, 5, 2023),
(3, '1AB23CS003', 'Arun Kumar', '2004-11-02', 'Male', 'arun.kumar@example.com', '9876543212', '8, Gandhi Nagar, Davangere, Karnataka', 1, 1, 5, 2023),
(4, '1AB23CS004', 'Sneha Patil', '2005-01-09', 'Female', 'sneha.patil@example.com', '9876543213', '22, Teachers Colony, Davangere, Karnataka', 1, 1, 5, 2023),
(5, '1AB23IS001', 'Kiran Gowda', '2005-05-30', 'Male', 'kiran.gowda@example.com', '9876543214', '3, Vidya Nagar, Davangere, Karnataka', 2, 2, 3, 2024),
(6, '1AB23EC001', 'Meera Nair', '2004-09-18', 'Female', 'meera.nair@example.com', '9876543215', '17, Lake View Layout, Davangere, Karnataka', 3, 3, 5, 2023);

INSERT INTO attendance (student_id, subject, attendance_date, status) VALUES
(1, 'Database Management Systems', '2026-09-01', 'Present'),
(1, 'Database Management Systems', '2026-09-02', 'Present'),
(1, 'Database Management Systems', '2026-09-03', 'Absent'),
(1, 'Database Management Systems', '2026-09-04', 'Present'),
(2, 'Database Management Systems', '2026-09-01', 'Present'),
(2, 'Database Management Systems', '2026-09-02', 'Present'),
(2, 'Database Management Systems', '2026-09-03', 'Present'),
(2, 'Database Management Systems', '2026-09-04', 'Present'),
(3, 'Database Management Systems', '2026-09-01', 'Absent'),
(3, 'Database Management Systems', '2026-09-02', 'Present'),
(3, 'Database Management Systems', '2026-09-03', 'Absent'),
(3, 'Database Management Systems', '2026-09-04', 'Present'),
(4, 'Java Programming', '2026-09-01', 'Present'),
(4, 'Java Programming', '2026-09-02', 'Present'),
(4, 'Java Programming', '2026-09-03', 'Present'),
(5, 'Operating Systems', '2026-09-01', 'Present'),
(5, 'Operating Systems', '2026-09-02', 'Absent'),
(6, 'Digital Electronics', '2026-09-01', 'Present'),
(6, 'Digital Electronics', '2026-09-02', 'Present'),
(6, 'Digital Electronics', '2026-09-03', 'Present');

INSERT INTO marks (student_id, subject, internal_marks, external_marks, total_marks, grade, exam_type) VALUES
(1, 'Database Management Systems', 27, 62, 89, 'A', 'Semester Exam'),
(1, 'Java Programming', 24, 58, 82, 'A', 'Semester Exam'),
(2, 'Database Management Systems', 29, 66, 95, 'A+', 'Semester Exam'),
(2, 'Java Programming', 26, 60, 86, 'A', 'Semester Exam'),
(3, 'Database Management Systems', 18, 40, 58, 'C', 'Semester Exam'),
(3, 'Java Programming', 20, 35, 55, 'C', 'Semester Exam'),
(4, 'Database Management Systems', 22, 50, 72, 'B+', 'Internal Test 1'),
(4, 'Java Programming', 25, 48, 73, 'B+', 'Semester Exam'),
(5, 'Operating Systems', 15, 30, 45, 'D', 'Semester Exam'),
(6, 'Digital Electronics', 21, 55, 76, 'B+', 'Semester Exam'),
(6, 'Signals and Systems', 19, 35, 54, 'C', 'Semester Exam');
