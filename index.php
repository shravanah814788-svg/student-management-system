<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

redirect('login.php');
