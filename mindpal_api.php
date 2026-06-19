<?php
ob_start(); // 🔥 prevents HTML errors breaking JSON

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/php-backend/utils/ReportGenerator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Safe user id
$user_id = $_SESSION['user_id'] ?? 0;

$apiKey = GROQ_API_KEY;

$data = json_decode(file_get_contents("php://input"), true);
$userMessage = $data['message'] ?? '';

if (!$userMessage) {
    echo json_encode(["reply" => "No message received"]);
    exit;
}

$url = "https://api.groq.com/openai/v1/chat/completions";

$previous_summary = "";

if ($user_id > 0) {
    try {
        require_once __DIR__ . '/includes/db.php'; // sets up global $pdo
        
        $stmt = $pdo->prepare("SELECT summary FROM daily_summary WHERE user_id = ? AND date = CURDATE()");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $previous_summary = $row['summary'];
        }
    } catch (PDOException $e) {
    }
}

$postData = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        [
            "role" => "system",
            "content" => "
You are MindPal, a deeply empathetic AI companion for students.

STRICT OUTPUT RULES:
- Respond ONLY in valid JSON
- No extra text before or after JSON
- No explanations

JSON FORMAT:
{
  \"reply\": \"natural, human-like emotional response\",
  \"mood\": number (0-10),
  \"summary\": \"short updated summary of student's mental state\",
  \"suggestion\": \"short helpful advice\",
  \"attention\": \"YES or NO\"
}

BEHAVIOR:
- Talk like a very close best friend
- Be warm, caring, and emotionally supportive
- Make the user feel heard and understood
- Never sound robotic, clinical, or like a doctor
- Keep replies natural and slightly conversational

EMOTIONAL FLOW (VERY IMPORTANT):

1. If user is sad/stressed:
   - First deeply understand and acknowledge feelings
   - Comfort them gently
   - Make them feel safe and not alone
   - Slowly calm them down

2. Once user becomes slightly better:
   - Encourage small positive steps
   - Suggest simple calming activities (like breathing, relaxing)

3. When appropriate:
   - Suggest doing exercises from the Wellness Coach (do NOT force, suggest naturally)

4. After user feels better:
   - Continue conversation for a bit (do NOT abruptly end)
   - Gently motivate them for studies
   - Encourage focus, confidence, and small progress
   - Offer help in studies if needed

5. If user is already happy:
   - Be cheerful and motivating
   - Encourage productivity and learning

MOOD SCORING:
- 0–3 → very sad / stressed (attention YES)
- 4–6 → neutral / slightly low
- 7–10 → positive / happy

SUMMARY RULES:
- Update summary every message
- Combine previous emotional state with current message
- Keep it 1–2 lines only
- Do NOT include private details
- Focus on emotional trend only

ATTENTION RULE:
- YES if:
  - mood ≤ 3
  - strong stress, anxiety, loneliness, burnout
- Otherwise NO

IMPORTANT:
- DO NOT mention mood score in reply
- DO NOT mention summary in reply
- These are backend only
- Keep conversation natural, not structured like a report
"
        ],
        [
            "role" => "user",
            "content" => "
Previous Summary:
$previous_summary

User Message:
$userMessage
"
        ]
    ],
    "temperature" => 0.7
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($postData)
]);

$response = curl_exec($ch);

// ❗ CURL ERROR CHECK
if ($response === false) {
    echo json_encode(["reply" => "API connection failed 😔"]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);
$content = $result['choices'][0]['message']['content'] ?? "";
// DEBUG (remove later)
file_put_contents("debug.txt", $content);

// 🔥 EXTRACT JSON SAFELY (VERY IMPORTANT)
preg_match('/\{.*\}/s', $content, $matches);

$jsonString = $matches[0] ?? '{}';

$ai = json_decode($jsonString, true);

// Safe fallback
$reply = $ai['reply'] ?? "I'm here for you 💜";
$mood = $ai['mood'] ?? 5;
$suggestion = $ai['suggestion'] ?? "";
$attention = $ai['attention'] ?? "NO";
$summary = $ai['summary'] ?? "";

// ✅ SAVE TO DB (mood score + daily summary for parent dashboard)
if ($user_id > 0) {
    try {
        // $pdo is already initialized above

        // Save User Message
        $stmt = $pdo->prepare("INSERT INTO student_chat_messages (user_id, sender, message) VALUES (?, 'user', ?)");
        $stmt->execute([$user_id, $userMessage]);

        // Save AI Message
        $stmt = $pdo->prepare("INSERT INTO student_chat_messages (user_id, sender, message) VALUES (?, 'ai', ?)");
        $stmt->execute([$user_id, $reply]);

        // Save Mood Score
        $stmt = $pdo->prepare("INSERT INTO mood_logs (user_id, mood) VALUES (?, ?)");
        $stmt->execute([$user_id, $mood]);

        // Calculate daily average and update chat_summary
        $stmt = $pdo->prepare("SELECT AVG(mood) FROM mood_logs WHERE user_id = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$user_id]);
        $avg_mood = (float)$stmt->fetchColumn();

        $trend = 'Neutral';
        $stress = 'Moderate';

        if ($avg_mood >= 7) {
            $trend = 'Positive';
            $stress = 'Low';
        } elseif ($avg_mood <= 3) {
            $trend = 'Negative';
            $stress = 'High';
        }

        $stmt = $pdo->prepare("
            INSERT INTO chat_summary (user_id, summary_date, avg_mood, mood_trend, stress_level) 
            VALUES (?, CURDATE(), ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            avg_mood = VALUES(avg_mood),
            mood_trend = VALUES(mood_trend),
            stress_level = VALUES(stress_level)
        ");
        $stmt->execute([$user_id, $avg_mood, $trend, $stress]);

        // Update / Insert Daily Summary
        if (!empty($summary)) {
            $stmt = $pdo->prepare("SELECT id FROM daily_summary WHERE user_id = ? AND date = CURDATE()");
            $stmt->execute([$user_id]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE daily_summary SET summary = ? WHERE user_id = ? AND date = CURDATE()");
                $stmt->execute([$summary, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO daily_summary (user_id, date, summary) VALUES (?, CURDATE(), ?)");
                $stmt->execute([$user_id, $summary]);
            }
        }

        // ✅ AUTO-GENERATE PARENT REPORT
        ReportGenerator::generateDailyReport($pdo, $user_id);

    } catch (PDOException $e) {
        file_put_contents("db_error.txt", date('Y-m-d H:i:s') . ": " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// ✅ FINAL RESPONSE
echo json_encode([
    "reply" => $reply
]);
