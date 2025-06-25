<?php
/**
 * UserModel - handles user data operations
 */
class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user
     */
    public function authenticateUser($username, $password) {
        $sql = "SELECT id, username, email, parola FROM utilizator WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['parola'])) {
            unset($user['parola']); // Remove password from returned data
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create new user
     */
    public function createUser($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO utilizator (username, email, parola) VALUES (?, ?, ?)";
        $stmt = $this->db->query($sql, [$username, $email, $hashed_password]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Check if user exists
     */
    public function userExists($username, $email) {
        $sql = "SELECT id FROM utilizator WHERE username = ? OR email = ?";
        $stmt = $this->db->query($sql, [$username, $email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, username, email FROM utilizator WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT id, username, email FROM utilizator WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $username, $email, $password = null) {
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE utilizator SET username = ?, email = ?, parola = ? WHERE id = ?";
            $params = [$username, $email, $hashed_password, $id];
        } else {
            $sql = "UPDATE utilizator SET username = ?, email = ? WHERE id = ?";
            $params = [$username, $email, $id];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE utilizator SET parola = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$hashed_password, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM utilizator WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get all users
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, email FROM utilizator ORDER BY username";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total users count
     */
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as count FROM utilizator";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get users for export
     */
    public function getAllUsersForExport() {
        $sql = "SELECT 
                    id,
                    username,
                    email
                FROM utilizator 
                ORDER BY username";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Update user profile
     */
    public function updateUserProfile($user_id, $email, $new_password = null) {
        if ($new_password) {
            return $this->updateUser($user_id, null, $email, $new_password);
        } else {
            $sql = "UPDATE utilizator SET email = ? WHERE id = ?";
            $stmt = $this->db->query($sql, [$email, $user_id]);
            return $stmt->rowCount() > 0;
        }
    }
    
    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken($user_id) {
        $token = bin2hex(random_bytes(32));
        // In a real implementation, you would store this token in a database table
        // with an expiration time
        return $token;
    }
    
    /**
     * Search users
     */
    public function searchUsers($query, $limit = 20) {
        $sql = "SELECT id, username, email 
                FROM utilizator 
                WHERE username LIKE ? OR email LIKE ?
                ORDER BY username
                LIMIT ?";
        
        $search_term = '%' . $query . '%';
        $stmt = $this->db->query($sql, [$search_term, $search_term, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Toggle user status (if status field exists)
     */
    public function toggleUserStatus($user_id) {
        // This would require adding a status field to the utilizator table
        // For now, just return true as the basic table doesn't have status
        return true;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStatistics($user_id) {
        $sql = "SELECT 
                    u.username,
                    u.email,
                    COUNT(c.id) as total_orders,
                    COUNT(CASE WHEN co.Status = 'finalizata' THEN 1 END) as completed_orders
                FROM utilizator u
                LEFT JOIN Client c ON u.id = c.user_id
                LEFT JOIN Comanda co ON c.id = co.idClient
                WHERE u.id = ?
                GROUP BY u.id, u.username, u.email";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetch();
    }
    
    /**
     * Check if username is available
     */
    public function isUsernameAvailable($username, $exclude_user_id = null) {
        $sql = "SELECT id FROM utilizator WHERE username = ?";
        $params = [$username];
        
        if ($exclude_user_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch() === false;
    }
    
    /**
     * Check if email is available
     */
    public function isEmailAvailable($email, $exclude_user_id = null) {
        $sql = "SELECT id FROM utilizator WHERE email = ?";
        $params = [$email];
        
        if ($exclude_user_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch() === false;
    }
    
    /**
     * Get user activity
     */
    public function getUserActivity($user_id, $limit = 10) {
        $sql = "SELECT 
                    'order' as activity_type,
                    co.id as activity_id,
                    co.TipServiciu as activity_description,
                    co.DataProgramare as activity_date
                FROM utilizator u
                LEFT JOIN Client c ON u.id = c.user_id
                LEFT JOIN Comanda co ON c.id = co.idClient
                WHERE u.id = ? AND co.id IS NOT NULL
                ORDER BY co.DataProgramare DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$user_id, $limit]);
        return $stmt->fetchAll();
    }
}