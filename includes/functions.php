<?php
// C:\Users\RISHON OOMMEN TOGY\Downloads\MindCare\php-app\includes\functions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function redirect_if_not_logged_in()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function redirect_if_logged_in()
{
    if (is_logged_in()) {
        header("Location: dashboard.php");
        exit();
    }
}

function get_current_user_name()
{
    return $_SESSION['user_name'] ?? 'User';
}

function get_current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function generate_uuid()
{
    return bin2hex(random_bytes(16));
}

if (!function_exists('clean_input')) {
    function clean_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}
