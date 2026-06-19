<?php

/**
 * Session Management Class
 */

class Session
{

    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        self::init();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::init();
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
            $_SESSION = [];
        }
    }

    public static function id($id = null)
    {
        if ($id !== null) {
            return session_id($id);
        }
        return session_id();
    }

    public static function isLoggedIn()
    {
        return self::has('user_id');
    }

    public static function getUser()
    {
        return [
            'user_id' => self::get('user_id'),
            'user_email' => self::get('user_email'),
            'user_name' => self::get('user_name'),
            'user_type' => self::get('user_type'),
        ];
    }
}
