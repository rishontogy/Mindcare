<?php
/**
 * Authentication Utility Class
 */

class Auth {
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    public static function checkLogin() {
        Session::init();
        
        if (!Session::isLoggedIn()) {
            header('Location: ' . constant('APP_URL') . 'login.php');
            exit();
        }
    }
    
    public static function checkParentLogin() {
        Session::init();
        
        if (!Session::isLoggedIn() || Session::get('user_type') !== 'parent') {
            header('Location: ' . constant('APP_URL') . 'parent-login.php');
            exit();
        }
    }
    
    public static function checkStudentLogin() {
        Session::init();
        
        if (!Session::isLoggedIn() || Session::get('user_type') !== 'student') {
            header('Location: ' . constant('APP_URL') . 'login.php');
            exit();
        }
    }
    
    public static function redirectIfLoggedIn($redirect = '/dashboard.php') {
        Session::init();
        
        if (Session::isLoggedIn()) {
            $path = ltrim($redirect, '/');
            header('Location: ' . constant('APP_URL') . $path);
            exit();
        }
    }
}
