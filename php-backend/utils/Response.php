<?php
/**
 * API Response Utility Class
 */

class Response {
    
    public static function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
    
    public static function success($message, $data = null, $statusCode = 200) {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    public static function error($message, $data = null, $statusCode = 400) {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    public static function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    public static function notFound() {
        http_response_code(404);
        die('Page not found');
    }
    
    public static function unauthorized() {
        http_response_code(401);
        die('Unauthorized');
    }
    
    public static function forbidden() {
        http_response_code(403);
        die('Forbidden');
    }
}
