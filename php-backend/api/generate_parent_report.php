<?php
ob_start();
header("Content-Type: application/json");

// Adjust relative path assuming this script is in php-backend/api
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../../includes/config.php';

Auth::checkParentLogin();
$pid = $_SESSION['user_id'];

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);
$student_id = isset($data['student_id']) ? (int)$data['student_id'] : 0;
$target_date = isset($data['date']) ? $data['date'] : date('Y-m-d');

if ($student_id <= 0) {
    echo json_encode(['error' => 'Valid Student ID required']);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Ensure this parent is linked to this student
$stmt = $pdo->prepare("SELECT id FROM parent_student_links WHERE parent_id = ? AND student_id = ?");
$stmt->execute([$pid, $student_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Unauthorized access to this student.']);
    exit;
}

// Fetch daily summary
$stmt = $pdo->prepare("SELECT summary FROM daily_summary WHERE user_id = ? AND date = ?");
$stmt->execute([$student_id, $target_date]);
$summaryRow = $stmt->fetch();
$summaryText = $summaryRow ? $summaryRow['summary'] : "No conversation summary logged for this day.";

// Fetch average mood
$stmt = $pdo->prepare("SELECT ROUND(AVG(mood), 1) as avg_mood FROM mood_logs WHERE user_id = ? AND DATE(created_at) = ?");
$stmt->execute([$student_id, $target_date]);
$moodRow = $stmt->fetch();
$avgMood = $moodRow['avg_mood'] ?? "Unknown";

// Groq API call
$apiKey = GROQ_API_KEY;
if (!$apiKey) {
    echo json_encode(['error' => 'GROQ_API_KEY is missing.']);
    exit;
}

$url = "https://api.groq.com/openai/v1/chat/completions";

$promptContent = "
You are an expert child psychologist AI assisting a parent.
Student's Daily Summary: $summaryText
Student's Average Mood Score (0-10 scale, 10 being best, 0 being worst): $avgMood

Based on the above, provide:
1. A brief description of the student's current feelings.
2. An analysis of their mood score.
3. Three actionable and supportive suggestions for the parent to make them feel better or reinforce positive feelings.

STRICT OUTPUT RULES:
- Respond ONLY in valid JSON. No markdown, no extra text before or after the JSON braces.
JSON FORMAT:
{
  \"description\": \"brief paragraph interpreting the child's feelings\",
  \"score_analysis\": \"brief text analyzing the score\",
  \"suggestions\": [\"suggestion 1\", \"suggestion 2\", \"suggestion 3\"]
}
";

$postData = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        [
            "role" => "system",
            "content" => "You are an AI generating structured parent reports in JSON only."
        ],
        [
            "role" => "user",
            "content" => $promptContent
        ]
    ],
    "temperature" => 0.5
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

if ($response === false) {
    echo json_encode(['error' => "API connection failed: " . curl_error($ch)]);
    exit;
}

curl_close($ch);
$result = json_decode($response, true);
$content = $result['choices'][0]['message']['content'] ?? "";

// Extract JSON safely
preg_match('/\{.*\}/s', $content, $matches);
$jsonString = $matches[0] ?? '{}';
$aiData = json_decode($jsonString, true);

if (!$aiData) {
    echo json_encode([
        'description' => "Could not automatically generate description. Raw summary from system: $summaryText",
        'score_analysis' => "Average mood logged today is $avgMood/5.",
        'suggestions' => ["Talk to your child.", "Spend quality time together.", "Ensure they get enough rest."]
    ]);
    exit;
}

echo json_encode($aiData);
