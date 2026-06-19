<?php
/**
 * User Model
 */

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($email, $password, $name, $userType = 'student') {
        if (!$this->conn) return false;
        
        $hashedPassword = Auth::hashPassword($password);
        
        $sql = "INSERT INTO {$this->table} (email, password, name, user_type, created_at) 
                VALUES (:email, :password, :name, :user_type, NOW())";
        
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword,
                ':name' => $name,
                ':user_type' => $userType
            ]);
        } catch (PDOException $e) {
            error_log("User Creation Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function findByEmail($email) {
        if (!$this->conn) return false;
        
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetch();
    }
    
    public function findById($id) {
        if (!$this->conn) return false;
        
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        if (!$this->conn) return false;
        
        $setClauses = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function emailExists($email) {
        if (!$this->conn) return false;
        
        $sql = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function delete($id) {
        if (!$this->conn) return false;
        
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([':id' => $id]);
    }
}
