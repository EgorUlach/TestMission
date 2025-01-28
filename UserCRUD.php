<?php

class UserCRUD {
    private $pdo;

    public function __construct($dsn, $user, $password) {
        try {
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения: " . $e->getMessage());
        }
    }

    public function addUser($name, $email, $groups = []) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Ошибка: Некорректный email.";
            return false;
        }

        $sql = "INSERT INTO users (name, email, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute([$name, $email])) {
            $userId = $this->pdo->lastInsertId();
            $this->updateUserGroups($userId, $groups);
            return true;
        }

        return false;
    }

    public function getUsers() {
        $sql = "SELECT 
            u.id AS user_id,        
            u.name AS user_name,    
            u.email,                
            u.created_at,           
            u.updated_at,           
            g.name AS group_name    
        FROM 
            users u                 
        LEFT JOIN 
            users_groups ug ON u.id = ug.user_id  
        LEFT JOIN 
            type_groups g ON ug.group_id = g.id  
        ORDER BY 
            u.created_at DESC;";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $name, $email, $groups = []) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Ошибка: Некорректный email.";
            return false;
        }

        $sql = "UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute([$name, $email, $id])) {
            $this->updateUserGroups($id, $groups);
            return true;
        }

        return false;
    }

    public function deleteUser($id) {
        // Удаляем пользователя
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    private function updateUserGroups($userId, $groups) {
        // Удаляем старые группы
        $sqlDelete = "DELETE FROM users_groups WHERE user_id = ?";
        $stmtDelete = $this->pdo->prepare($sqlDelete);
        $stmtDelete->execute([$userId]);

        // Добавляем новые группы
        $sqlInsert = "INSERT INTO users_groups (user_id, group_id) VALUES (?, ?)";
        $stmtInsert = $this->pdo->prepare($sqlInsert);

        foreach ($groups as $groupId) {
            $stmtInsert->execute([$userId, $groupId]);
        }
    }
}



