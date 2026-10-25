<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Student Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <div class="main-area">
        <header class="topbar">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Open menu">&#9776;</button>
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <div class="topbar-user">
                <span class="topbar-name">Hi, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout.php" class="btn btn-small btn-danger">Logout</a>
            </div>
        </header>
        <main class="content">
