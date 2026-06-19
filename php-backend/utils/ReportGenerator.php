<?php
// php-backend/utils/ReportGenerator.php

@require_once __DIR__ . '/../../includes/config.php';

class ReportGenerator {
    /**
     * Generates a daily AI report for a student and stores it in the database.
     * 
     * @param PDO $pdo The database connection.
     * @param int $student_id The ID of the student.
     * @param string|null $date The date for the report (defaults to today).
     * @return bool True on success, false on failure.
     */
    public static function generateDailyReport($pdo, $student_id, $date = null) {
        if (!$date) $date = date('Y-m-d');
        
        // Ensure GROQ_API_KEY is defined
        if (!defined('GROQ_API_KEY')) {
            return false;
        }

        try {
            // Fetch daily summary from conversation logs
            $stmt = $pdo->prepare("SELECT summary FROM daily_summary WHERE user_id = ? AND date = ?");
            $stmt->execute([$student_id, $date]);
            $summaryRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $summaryText = $summaryRow ? $summaryRow['summary'] : "No specific conversation summary logged for this day yet.";
            
            // Fetch average mood from logs
            $stmt = $pdo->prepare("SELECT ROUND(AVG(mood), 1) as avg_mood FROM mood_logs WHERE user_id = ? AND DATE(created_at) = ?");
            $stmt->execute([$student_id, $date]);
            $avgMood = $stmt->fetchColumn();
            
            if ($avgMood === null) {
                // Check chat_summary if mood_logs is empty for some reason
                $stmt = $pdo->prepare("SELECT avg_mood FROM chat_summary WHERE user_id = ? AND summary_date = ?");
                $stmt->execute([$student_id, $date]);
                $avgMood = $stmt->fetchColumn();
            }
            
            $moodScore = ($avgMood !== null) ? $avgMood : "Unknown";
            
            // Fetch current stress level from chat_summary
            $stmt = $pdo->prepare("SELECT stress_level FROM chat_summary WHERE user_id = ? AND summary_date = ?");
            $stmt->execute([$student_id, $date]);
            $currentStress = $stmt->fetchColumn() ?: "Moderate";

            // AI Prompt
            $apiKey = GROQ_API_KEY;
            $url = "https://api.groq.com/openai/v1/chat/completions";
            
            $promptContent = "
            You are an expert child psychologist AI assisting a parent via the MindCare platform.
            Student's Daily Interaction Summary: $summaryText
            Student's Average Mood Score (0-10): $moodScore
            Current Stress Assessment: $currentStress

            Please provide a structured report for the parent containing:
            1. Overall Review: A deeply insightful analysis of the student's emotional state today (1 paragraph).
            2. Stress Level: A refined assessment (Low, Moderate, or High).
            3. Suggestions: Three specific, actionable, and supportive suggestions for the parent to help their child.

            STRICT OUTPUT RULES:
            - Respond ONLY in valid JSON. No markdown formatting.
            - Ensure suggestions are an array of 3 strings.

            JSON FORMAT:
            {
              \"overall_review\": \"...\",
              \"stress_level\": \"Low/Moderate/High\",
              \"suggestions\": [\"...\", \"...\", \"...\"]
            }
            ";

            $postData = [
                "model" => "llama-3.3-70b-versatile",
                "messages" => [
                    ["role" => "system", "content" => "You are an AI generating structured parent reports in JSON only."],
                    ["role" => "user", "content" => $promptContent]
                ],
                "temperature" => 0.5
            ];

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ["Authorization: Bearer $apiKey", "Content-Type: application/json"],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                return false;
            }

            $result = json_decode($response, true);
            $content = $result['choices'][0]['message']['content'] ?? "";
            
            // Extract JSON
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $aiData = json_decode($matches[0], true);
            } else {
                return false;
            }

            if (!$aiData || !isset($aiData['overall_review'])) {
                return false;
            }

            // Save to parent_detailed_reports
            $stmt = $pdo->prepare("
                INSERT INTO parent_detailed_reports (student_id, report_date, mood_score, stress_level, overall_review, suggestions)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                mood_score = VALUES(mood_score),
                stress_level = VALUES(stress_level),
                overall_review = VALUES(overall_review),
                suggestions = VALUES(suggestions)
            ");
            
            $stmt->execute([
                $student_id, 
                $date, 
                ($moodScore === 'Unknown') ? null : $moodScore,
                $aiData['stress_level'] ?? $currentStress,
                $aiData['overall_review'],
                json_encode($aiData['suggestions'] ?? [])
            ]);

            return true;

        } catch (Exception $e) {
            error_log("ReportGenerator Error: " . $e->getMessage());
            return false;
        }
    }
}
