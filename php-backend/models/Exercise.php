<?php
/**
 * Exercise Model
 */

class Exercise {
    private $conn;
    private $table = 'exercises';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll($limit = 50) {
        if (!$this->conn) {
            if (APP_ENV === 'development') {
                return [
                    ['id' => 1, 'name' => 'Deep Breathing', 'description' => 'A simple breathing exercise.', 'category' => 'breathing', 'duration' => 5],
                    ['id' => 2, 'name' => 'Mindful Meditation', 'description' => 'Focus on your breath.', 'category' => 'meditation', 'duration' => 10],
                    ['id' => 3, 'name' => 'Stress Relief Stretch', 'description' => 'Gentle stretches for tension.', 'category' => 'stress', 'duration' => 8]
                ];
            }
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        if (!$this->conn) {
            if (APP_ENV === 'development') {
                return ['id' => $id, 'name' => 'Mock Exercise', 'description' => 'Mock description.', 'category' => 'general', 'duration' => 5];
            }
            return null;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch();
    }
    
    public function getByCategory($category) {
        if (!$this->conn) {
            if (APP_ENV === 'development') {
                return [['id' => 1, 'name' => 'Mock Exercise (' . $category . ')', 'category' => $category, 'duration' => 5]];
            }
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE category = :category ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':category' => $category]);
        
        return $stmt->fetchAll();
    }
    
    public function create($name, $description, $category, $duration, $instructions, $benefits = null) {
        if (!$this->conn) return false;
        
        $sql = "INSERT INTO {$this->table} (name, description, category, duration, instructions, benefits, created_at) 
                VALUES (:name, :description, :category, :duration, :instructions, :benefits, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':category' => $category,
            ':duration' => $duration,
            ':instructions' => $instructions,
            ':benefits' => $benefits
        ]);
    }
}
