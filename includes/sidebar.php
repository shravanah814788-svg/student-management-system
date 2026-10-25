<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon">SM</span>
        <span>Student<br>Management</span>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="students.php" class="<?= $activeMenu === 'students' ? 'active' : '' ?>">Students</a>
        <a href="add_student.php" class="<?= $activeMenu === 'add_student' ? 'active' : '' ?>">Add Student</a>
        <a href="departments.php" class="<?= $activeMenu === 'departments' ? 'active' : '' ?>">Departments</a>
        <a href="courses.php" class="<?= $activeMenu === 'courses' ? 'active' : '' ?>">Courses</a>
        <a href="attendance.php" class="<?= $activeMenu === 'attendance' ? 'active' : '' ?>">Attendance</a>
        <a href="marks.php" class="<?= $activeMenu === 'marks' ? 'active' : '' ?>">Marks</a>
        <a href="reports.php" class="<?= $activeMenu === 'reports' ? 'active' : '' ?>">Reports</a>
        <a href="logout.php" class="sidebar-logout">Logout</a>
    </nav>
</aside>
