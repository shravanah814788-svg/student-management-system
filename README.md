# Student Management System

A web-based college mini project built with HTML5, CSS3, JavaScript, PHP and MySQL. It runs locally on XAMPP.

## Sample Login

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

The password is stored as a secure hash in the `admins` table, created by the seed in `database.sql`.

## Step 1: Install XAMPP

Download and install XAMPP from https://www.apachefriends.org

## Step 2: Start Apache and MySQL

1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

## Step 3: Copy the project folder

Copy the whole `student-management-system` folder into:

```
C:\xampp\htdocs\
```

After copying, the path should be `C:\xampp\htdocs\student-management-system\`.

## Step 4: Open phpMyAdmin

Open this link in your browser:

```
http://localhost/phpmyadmin
```

## Step 5: Import database.sql

1. In phpMyAdmin, click the **Import** tab.
2. Click **Choose File** and select `database.sql` from the project folder.
3. Click **Go**.

This creates the database `student_management`, all tables, and the sample data.

## Step 6: Check config/db.php

Open `config/db.php`. With default XAMPP settings you do not need to change anything:

| Setting  | Default value        |
|----------|----------------------|
| Host     | `localhost`          |
| Username | `root`               |
| Password | (empty)              |
| Database | `student_management` |

If you changed your MySQL password, update it in this file.

## Step 7: Open the project in your browser

```
http://localhost/student-management-system/
```

## Step 8: Login

Use the sample login:

- Username: `admin`
- Password: `admin123`

## Folder Structure

```
student-management-system/
    config/            Database connection
    includes/          Header, sidebar, footer, login check and helpers
    assets/            CSS, JavaScript and images
    uploads/students/  Student profile photos
    index.php          Redirects to login or dashboard
    login.php          Admin login
    logout.php         Logs out and destroys the session
    dashboard.php      Admin dashboard
    students.php       Student list, search and filter
    add_student.php    Add student
    edit_student.php   Edit student
    view_student.php   Student details, attendance and marks
    delete_student.php Delete confirmation and delete
    departments.php    Department list
    add_department.php / edit_department.php / delete_department.php
    courses.php        Course list
    add_course.php / edit_course.php / delete_course.php
    attendance.php     Mark attendance and view percentages
    marks.php          Enter marks and view grades
    reports.php        Student, attendance and marks reports (printable)
    database.sql       Database, tables and sample data
```

## Common Problems

| Problem | Solution |
|---------|----------|
| "Database connection failed!" | Start MySQL in XAMPP and check the username and password in `config/db.php`. |
| Page not found (404) | Make sure the folder is inside `C:\xampp\htdocs\` and Apache is running. |
| Photo upload fails | Check that the folder `uploads/students/` exists. Photos must be JPG, PNG or WEBP and 2 MB or less. |
| Table or database missing | Import `database.sql` again in phpMyAdmin. |
| Port 80 already in use | Stop Skype or other programs using port 80, or change the Apache port in XAMPP. |
