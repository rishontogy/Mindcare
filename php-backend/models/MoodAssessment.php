<?php
/**
 * Mood Assessment Model
 */

class MoodAssessment {
    private $conn;
    private $table = 'mood_assessments';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($userId, $moodScore, $answers, $recommendations = null) {
        if (!$this->conn) return false;
        
        $sql = "INSERT INTO {$this->table} (user_id, mood_score, answers, recommendations, created_at) 
                VALUES (:user_id, :mood_score, :answers, :recommendations, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':user_id' => $userId,
            ':mood_score' => $moodScore,
            ':answers' => json_encode($answers),
            ':recommendations' => $recommendations
        ]);
    }
    
    public function getUserAssessments($userId, $limit = 30) {
        if (!$this->conn) {
            if (APP_ENV === 'development') {
                return [
                    ['id' => 1, 'user_id' => $userId, 'mood_score' => 8, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
                    ['id' => 2, 'user_id' => $userId, 'mood_score' => 7, 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
                    ['id' => 3, 'user_id' => $userId, 'mood_score' => 9, 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))]
                ];
            }
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id 
                ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getTodayAssessment($userId) {
        if (!$this->conn) return null;
        
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id 
                AND DATE(created_at) = CURDATE() LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetch();
    }
    
    public function getAverageMood($userId, $days = 7) {
        if (!$this->conn) {
            return (APP_ENV === 'development') ? 7.5 : 0;
        }
        
        $sql = "SELECT AVG(mood_score) as average_mood FROM {$this->table} 
                WHERE user_id = :user_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':days' => $days
        ]);
        
        $result = $stmt->fetch();
        return $result['average_mood'] ?? 0;
    }
}
