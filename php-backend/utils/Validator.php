<?php
/**
 * Data Validation Class
 */

class Validator {
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 digit
        return preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
    
    public static function validateString($string, $minLength = 2, $maxLength = 255) {
        $length = strlen(trim($string));
        return $length >= $minLength && $length <= $maxLength;
    }
    
    public static function validateNumber($number, $min = 0, $max = PHP_INT_MAX) {
        if (!is_numeric($number)) {
            return false;
        }
        $num = intval($number);
        return $num >= $min && $num <= $max;
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeArray($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value);
            } else {
                $result[$key] = self::sanitizeInput($value);
            }
        }
        return $result;
    }
}
