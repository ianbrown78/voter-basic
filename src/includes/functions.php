<?php
// includes/functions.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /public/login.php');
        exit;
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in'])
        && $_SESSION['admin_logged_in'] === true
        && $_SESSION['admin_user_id'] > 0;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?>