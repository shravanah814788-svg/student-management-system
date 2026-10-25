```php
<?php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$summary = fetch_row($conn, "SELECT
    (SELECT COUNT(*) FROM students) AS total_students,
    (SELECT COUNT(*) FROM departments) AS total_departments,
    (SELECT COUNT(*) FROM courses) AS total_courses,
    (SELECT COUNT(*) FROM attendance) AS total_attendance,
    (SELECT COUNT(*) FROM marks) AS total_marks") ?? [];

$recentStudents = fetch_rows($conn, "SELECT
    s.id,
    s.usn,
    s.full_name,
    s.semester,
    s.photo,
    d.department_code
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    ORDER BY s.created_at DESC, s.id DESC
    LIMIT 5");

$adminName = $_SESSION['admin_username'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));

$activeMenu = 'dashboard';
$pageTitle = 'Dashboard';

$icon = function ($body) {
    return '<svg class="icon" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round"
        aria-hidden="true">' . $body . '</svg>';
};

$navItems = [

    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'href' => 'dashboard.php',
        'icon' => $icon('<rect x="3" y="3" width="7" height="7"/>
        <rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/>')
    ],

    [
        'key' => 'students',
        'label' => 'Students',
        'href' => 'students.php',
        'icon' => $icon('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>')
    ],

    [
        'key' => 'departments',
        'label' => 'Departments',
        'href' => 'departments.php',
        'icon' => $icon('<rect x="2" y="7" width="20" height="14" rx="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>')
    ],

    [
        'key' => 'courses',
        'label' => 'Courses',
        'href' => 'courses.php',
        'icon' => $icon('<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>')
    ],

    [
        'key' => 'attendance',
        'label' => 'Attendance',
        'href' => 'attendance.php',
        'icon' => $icon('<rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>')
    ],

    [
        'key' => 'marks',
        'label' => 'Marks',
        'href' => 'marks.php',
        'icon' => $icon('<circle cx="12" cy="8" r="7"/>
        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>')
    ],

    [
        'key' => 'reports',
        'label' => 'Reports',
        'href' => 'reports.php',
        'icon' => $icon('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>')
    ]

];

$statCards = [

    [
        'label' => 'Total Students',
        'value' => (int)($summary['total_students'] ?? 0),
        'variant' => '',
        'icon' => $icon('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>')
    ],

    [
        'label' => 'Total Departments',
        'value' => (int)($summary['total_departments'] ?? 0),
        'variant' => 'green',
        'icon' => $icon('<rect x="2" y="7" width="20" height="14" rx="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>')
    ],

    [
        'label' => 'Total Courses',
        'value' => (int)($summary['total_courses'] ?? 0),
        'variant' => 'orange',
        'icon' => $icon('<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>')
    ],

    [
        'label' => 'Attendance Records',
        'value' => (int)($summary['total_attendance'] ?? 0),
        'variant' => 'purple',
        'icon' => $icon('<rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>')
    ],

    [
        'label' => 'Marks Records',
        'value' => (int)($summary['total_marks'] ?? 0),
        'variant' => 'red',
        'icon' => $icon('<circle cx="12" cy="8" r="7"/>
        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>')
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | Student Management System</title>

<style>

:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --sidebar: #0f172a;
    --sidebar-hover: #1e293b;
    --bg: #f1f5f9;
    --card: #ffffff;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --success: #16a34a;
    --warning: #ea580c;
    --purple: #7c3aed;
    --danger: #dc2626;
    --radius: 14px;
    --shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    line-height: 1.5;
}

a {
    text-decoration: none;
    color: inherit;
}

.icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.app {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 250px;
    background: var(--sidebar);
    color: #cbd5e1;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 100;
    transition: transform 0.3s ease;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.brand-name {
    color: #fff;
    font-weight: 600;
    font-size: 15px;
}

.brand-sub {
    font-size: 12px;
    color: #94a3b8;
}

.nav {
    flex: 1;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow-y: auto;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: #cbd5e1;
    font-size: 14px;
    font-weight: 500;
    transition: 0.2s;
}

.nav-link:hover {
    background: var(--sidebar-hover);
    color: #fff;
}

.nav-link.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
}

.sidebar-footer {
    padding: 12px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.nav-link.logout {
    color: #fca5a5;
}

.nav-link.logout:hover {
    background: rgba(220,38,38,0.15);
    color: #fecaca;
}

.overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.45);
    z-index: 90;
}

.main {
    flex: 1;
    min-width: 0;
    margin-left: 250px;
}

.topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #fff;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 28px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.topbar-title {
    font-weight: 600;
    font-size: 16px;
}

.menu-toggle {
    display: none;
    background: none;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 8px;
    cursor: pointer;
    color: var(--text);
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.admin-name {
    font-weight: 600;
    font-size: 14px;
}

.admin-role {
    font-size: 12px;
    color: var(--muted);
}

.content {
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.page-header h1 {
    font-size: 24px;
}

.page-header p {
    color: var(--muted);
    font-size: 14px;
    margin-top: 4px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    background: var(--primary);
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}

.btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.btn-outline {
    background: #fff;
    color: var(--primary);
    border: 1px solid #bfdbfe;
}

.btn-outline:hover {
    background: #eff6ff;
}

.btn-small {
    padding: 6px 10px;
    font-size: 12px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
}

.stat-card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 16px;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15,23,42,0.1);
}

.stat-card.green {
    border-left-color: var(--success);
}

.stat-card.orange {
    border-left-color: var(--warning);
}

.stat-card.purple {
    border-left-color: var(--purple);
}

.stat-card.red {
    border-left-color: var(--danger);
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
    color: var(--primary);
    flex-shrink: 0;
}

.stat-icon .icon {
    width: 20px;
    height: 20px;
}

.stat-card.green .stat-icon {
    background: #f0fdf4;
    color: var(--success);
}

.stat-card.orange .stat-icon {
    background: #fff7ed;
    color: var(--warning);
}

.stat-card.purple .stat-icon {
    background: #f5f3ff;
    color: var(--purple);
}

.stat-card.red .stat-icon {
    background: #fef2f2;
    color: var(--danger);
}

.stat-label {
    font-size: 12px;
    color: var(--muted);
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
}

.two-column {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}

.card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 18px;
    box-shadow: var(--shadow);
}

.card-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th {
    text-align: left;
    color: var(--muted);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

tbody tr:hover {
    background: #f8fafc;
}

/* Student photo fix */

.student-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    min-width: 200px;
}

.student-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    max-width: 40px;
    min-height: 40px;
    max-height: 40px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
    flex-shrink: 0;
}

.student-avatar img {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    min-height: 40px !important;
    max-height: 40px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
    display: block !important;
}

.student-avatar picture {
    width: 40px !important;
    height: 40px !important;
    display: block !important;
    overflow: hidden !important;
}

.student-avatar picture img {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    min-height: 40px !important;
    max-height: 40px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
}

.empty-state {
    text-align: center;
    color: var(--muted);
    padding: 24px;
}

.search-form {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.search-form input {
    flex: 1;
    min-width: 180px;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 13px;
    outline: none;
}

.search-form input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.quick-actions .btn {
    padding: 9px 10px;
    font-size: 12px;
}

@media (max-width: 1100px) {

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

}

@media (max-width: 900px) {

    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .overlay.show {
        display: block;
    }

    .main {
        margin-left: 0;
    }

    .menu-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 600px) {

    .content {
        padding: 16px;
    }

    .topbar {
        padding: 12px 16px;
    }

    .admin-role,
    .admin-name {
        display: none;
    }

    .page-header h1 {
        font-size: 20px;
    }

    .page-header .btn {
        width: 100%;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .card {
        padding: 14px;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 12px;
    }

    th,
    td {
        padding: 7px 8px;
    }

    .student-cell {
        min-width: 170px;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        max-width: 36px;
        min-height: 36px;
        max-height: 36px;
    }

    .student-avatar img,
    .student-avatar picture,
    .student-avatar picture img {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
    }

}

</style>

</head>

<body>

<div class="app">

<aside class="sidebar" id="sidebar">

    <div class="brand">

        <div class="brand-icon">
            SM
        </div>

        <div>
            <div class="brand-name">
                Student MS
            </div>

            <div class="brand-sub">
                College Admin
            </div>
        </div>

    </div>

    <nav class="nav">

        <?php foreach ($navItems as $item): ?>

            <a
                href="<?= e($item['href']) ?>"
                class="nav-link<?= $activeMenu === $item['key'] ? ' active' : '' ?>"
            >

                <?= $item['icon'] ?>

                <span>
                    <?= e($item['label']) ?>
                </span>

            </a>

        <?php endforeach; ?>

    </nav>

    <div class="sidebar-footer">

        <a href="logout.php" class="nav-link logout">

            <?= $icon('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>') ?>

            <span>
                Logout
            </span>

        </a>

    </div>

</aside>

<div class="overlay" id="overlay"></div>

<div class="main">

<header class="topbar">

    <div class="topbar-left">

        <button
            class="menu-toggle"
            id="menuToggle"
            type="button"
            aria-label="Open menu"
        >

            <?= $icon('<line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>') ?>

        </button>

        <div class="topbar-title">
            <?= e($pageTitle) ?>
        </div>

    </div>

    <div class="admin-profile">

        <div class="admin-avatar">
            <?= e($adminInitial) ?>
        </div>

        <div>

            <div class="admin-name">
                <?= e($adminName) ?>
            </div>

            <div class="admin-role">
                Administrator
            </div>

        </div>

    </div>

</header>

<main class="content">

<section class="page-header">

    <div>

        <h1>
            Welcome back, <?= e($adminName) ?>
        </h1>

        <p>
            Here is an overview of your college records.
        </p>

    </div>

    <a href="add_student.php" class="btn">
        + Quick Add Student
    </a>

</section>

<section class="stats-grid">

    <?php foreach ($statCards as $card): ?>

        <div class="stat-card <?= e($card['variant']) ?>">

            <div class="stat-icon">
                <?= $card['icon'] ?>
            </div>

            <div>

                <div class="stat-label">
                    <?= e($card['label']) ?>
                </div>

                <div class="stat-value">
                    <?= $card['value'] ?>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

</section>

<section class="two-column">

    <div class="card">

        <h2 class="card-title">
            Recent Students
        </h2>

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Student</th>
                        <th>USN</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (empty($recentStudents)): ?>

                    <tr>

                        <td colspan="5" class="empty-state">
                            No students added yet.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($recentStudents as $student): ?>

                        <tr>

                            <td>

                                <div class="student-cell">

                                    <div class="student-avatar">

                                        <?php render_avatar(
                                            $student['photo'],
                                            $student['full_name']
                                        ); ?>

                                    </div>

                                    <span>
                                        <?= e($student['full_name']) ?>
                                    </span>

                                </div>

                            </td>

                            <td>
                                <?= e($student['usn']) ?>
                            </td>

                            <td>
                                <?= e($student['department_code'] ?? '-') ?>
                            </td>

                            <td>
                                <?= e($student['semester']) ?>
                            </td>

                            <td>

                                <a
                                    href="view_student.php?id=<?= (int)$student['id'] ?>"
                                    class="btn btn-small btn-outline"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <div class="card">

        <h2 class="card-title">
            Quick Search
        </h2>

        <form
            method="GET"
            action="students.php"
            class="search-form"
        >

            <input
                type="text"
                name="q"
                placeholder="Name, USN, phone or email"
            >

            <button type="submit" class="btn btn-small">
                Search
            </button>

        </form>

    </div>

    <div class="card">

        <h2 class="card-title">
            Quick Actions
        </h2>

        <div class="quick-actions">

            <a href="add_student.php" class="btn">
                + Add Student
            </a>

            <a href="students.php" class="btn btn-outline">
                Manage Students
            </a>

            <a href="departments.php" class="btn btn-outline">
                Departments
            </a>

            <a href="courses.php" class="btn btn-outline">
                Courses
            </a>

            <a href="attendance.php" class="btn btn-outline">
                Attendance
            </a>

            <a href="marks.php" class="btn btn-outline">
                Marks
            </a>

            <a href="reports.php" class="btn btn-outline">
                Reports
            </a>

        </div>

    </div>

</section>

</main>

</div>

</div>

<script>

const menuToggle = document.getElementById("menuToggle");
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");

function setMobileMenu(isOpen) {

    sidebar.classList.toggle("open", isOpen);
    overlay.classList.toggle("show", isOpen);

}

if (menuToggle) {

    menuToggle.addEventListener("click", function () {

        setMobileMenu(!sidebar.classList.contains("open"));

    });

}

if (overlay) {

    overlay.addEventListener("click", function () {

        setMobileMenu(false);

    });

}

</script>

</body>

</html>
```