<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function require_login()
{
    if (empty($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
    }
}

function show_errors(array $errors)
{
    if (!empty($errors)) {
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
            echo '<li>' . e($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid()
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function fetch_rows(mysqli $conn, $sql, $types = '', array $params = [])
{
    if (empty($params)) {
        $result = $conn->query($sql);
    } else {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetch_row(mysqli $conn, $sql, $types = '', array $params = [])
{
    $rows = fetch_rows($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

function run_query(mysqli $conn, $sql, $types = '', array $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return false;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function percentage($part, $whole)
{
    return $whole > 0 ? round(($part / $whole) * 100, 2) : 0;
}

function render_avatar($photo, $name, $class = 'avatar')
{
    if (!empty($photo)) {
        echo '<img class="' . e($class) . '" src="uploads/students/' . e($photo) . '" alt="Student photo">';
    } else {
        echo '<span class="' . e($class) . ' avatar-empty">' . e(strtoupper(substr($name, 0, 1))) . '</span>';
    }
}
