<?php
ob_start();
header("Content-Type: application/json");

// Suppress warnings from redefines in config files
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/php-backend/init.php';
@require_once __DIR__ . '/includes/config.php';

// Check auth without redirect for API
if (!Session::get('user_id') || Session::get('user_type') !== 'parent') {
    echo json_encode(["status" => "error", "reply" => "Unauthorized access. Please log in again."]);
    exit;
}

$user = Session::getUser();
$parentId = $user['user_id'];
$studentId = Session::get('active_student_id');

if (!$studentId) {
    echo json_encode(["status" => "error", "reply" => "No student selected. Please select a student first."]);
    exit;
}

$apiKey = GROQ_API_KEY;

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $userMessage = $data['message'] ?? '';

    if (!$userMessage) {
        echo json_encode(["status" => "error", "reply" => "Message missing"]);
        exit;
    }

    // 🔥 GROQ API
    $url = "https://api.groq.com/openai/v1/chat/completions";

    $postData = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [
            [
                "role" => "system",
                "content" => "You are a Parent Support AI for MindCare. Analyze student's emotional condition using summarized insights. Be warm, supportive, and structured. Always include Emotional State, Observations, Suggestions, and Positive Reinforcement. Respect privacy."
            ],
            [
                "role" => "user",
                "content" => $userMessage
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("cURL Error: " . $curlError);
    }

    $result = json_decode($response, true);
    if (!$result) {
        throw new Exception("Invalid API response format");
    }

    if (isset($result['error'])) {
        throw new Exception("API Error: " . ($result['error']['message'] ?? 'Unknown error'));
    }

    $reply = $result['choices'][0]['message']['content'] ?? "I couldn't generate a response right now.";

    // ✅ SAVE TO DB
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare("INSERT INTO parent_chat_messages (parent_id, student_id, sender, message) VALUES (?, ?, 'user', ?)");
            $stmt->execute([$parentId, $studentId, $userMessage]);

            $stmt = $pdo->prepare("INSERT INTO parent_chat_messages (parent_id, student_id, sender, message) VALUES (?, ?, 'ai', ?)");
            $stmt->execute([$parentId, $studentId, $reply]);
        } catch (PDOException $e) {
            // Log DB error if needed but don't break the response
        }
    }

    ob_clean();
    echo json_encode(["status" => "success", "reply" => $reply]);
    exit;

} catch (Exception $e) {
    error_log("Parent AI API Error: " . $e->getMessage());
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "error",
        "reply" => "I'm having trouble connecting right now. Reason: " . $e->getMessage()
    ]);
    exit;
}
