<?php
require_once 'C:\xampp\htdocs\projet web fr\config\database.php';

class User {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function createUser($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteUser($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function updatePassword($userId, $hashedPassword) {
         $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
}

public function updateProfilePicture($userId, $imagePath) {
    try {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        return $stmt->execute([$imagePath, $userId]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

public function getTotalUsers() {
    $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    return $result['count'];
}

public function updateLastLogin($userId) {
    $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $stmt->execute([':id' => $userId]);
}

public function getAllUsers() {
    $stmt = $this->pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getActiveTodayCount() {
    $today = date('Y-m-d');
    $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(last_login) = :today");
    $stmt->execute([':today' => $today]);
    $result = $stmt->fetch();
    return $result['count'];
}


public function getAllUsersWithLastLogin($search = '') {
    if (!empty($search)) {
        $stmt = $this->pdo->prepare("SELECT id, username, email, is_admin, last_login FROM users 
                                    WHERE username LIKE :search OR email LIKE :search 
                                    ORDER BY last_login DESC");
        $searchTerm = '%' . $search . '%';
        $stmt->execute([':search' => $searchTerm]);
    } else {
        $stmt = $this->pdo->query("SELECT id, username, email, is_admin, last_login FROM users ORDER BY last_login DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function verifyUser($email, $password) {
    $user = $this->getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

public function findByEmail($email) {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Add this method to update reset token
public function updateResetToken($userId, $token, $expiry) {
    $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
    return $stmt->execute([$token, $expiry, $userId]);
}

// Add this method to clear reset token
public function clearResetToken($userId) {
    $stmt = $this->pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
    return $stmt->execute([$userId]);
}

// Add this method to update password
/*public function updatePassword($userId, $passwordHash) {
    $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$passwordHash, $userId]);
}*/


/**
 * Update multiple user fields at once
 */

public function updateUser($userId, $updateData) {
    // Filter out empty password if it exists
    if (isset($updateData['password']) && empty($updateData['password'])) {
        unset($updateData['password']);
    }
    
    // Hash password if it's being updated
    if (isset($updateData['password'])) {
        $updateData['password'] = password_hash($updateData['password'], PASSWORD_DEFAULT);
    }
    
    $setParts = [];
    $params = [];
    
    foreach ($updateData as $key => $value) {
        if ($value instanceof PDOExpr) {
            $setParts[] = "$key = $value";
        } else {
            $setParts[] = "$key = ?";
            $params[] = $value;
        }
    }
    
    $params[] = $userId;
    
    $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    
    return $stmt->execute($params);
}

public function isEmailTaken($email, $excludeUserId = null) {
    $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
    $params = [$email];
    
    if ($excludeUserId) {
        $sql .= " AND id != ?";
        $params[] = $excludeUserId;
    }
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

public function getAverageTimeSpent() {
    try {
        $stmt = $this->pdo->query("
            SELECT 
                CASE 
                    WHEN session_count = 0 THEN 0 
                    ELSE ROUND(total_time_spent / session_count / 60, 1) 
                END as avg_time
            FROM users
            WHERE session_count > 0
        ");
        $result = $stmt->fetch();
        return $result['avg_time'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error calculating average time: " . $e->getMessage());
        return 0;
    }
}


}
?>