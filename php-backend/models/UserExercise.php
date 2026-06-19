<?php
/**
 * User Exercise Model (for tracking completed exercises)
 */

class UserExercise {
    private $conn;
    private $table = 'user_exercises';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function recordCompletion($userId, $exerciseId, $feedbackRating = null) {
        if (!$this->conn) return false;
        
        $sql = "INSERT INTO {$this->table} (user_id, exercise_id, completed_at, feedback_rating) 
                VALUES (:user_id, :exercise_id, NOW(), :feedback_rating)";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':user_id' => $userId,
            ':exercise_id' => $exerciseId,
            ':feedback_rating' => $feedbackRating
        ]);
    }
    
    public function getUserCompletedExercises($userId, $limit = 10) {
        if (!$this->conn) {
            if (APP_ENV === 'development') {
                return [
                    ['id' => 1, 'name' => 'Deep Breathing', 'category' => 'breathing', 'duration' => 5, 'completed_at' => date('Y-m-d H:i:s')],
                    ['id' => 2, 'name' => 'Mindful Walk', 'category' => 'meditation', 'duration' => 15, 'completed_at' => date('Y-m-d H:i:s', strtotime('-1 day'))]
                ];
            }
            return [];
        }
        
        $sql = "SELECT ue.*, e.name, e.category, e.duration FROM {$this->table} ue 
                JOIN exercises e ON ue.exercise_id = e.id 
                WHERE ue.user_id = :user_id 
                ORDER BY ue.completed_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getTodayExercises($userId) {
        if (!$this->conn) {
            return (APP_ENV === 'development') ? 2 : 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND DATE(completed_at) = CURDATE()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
