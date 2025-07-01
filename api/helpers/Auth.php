<?php
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/../config/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function checkAuth() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            $this->sendError(401, 'Token missing');
            return false;
        }
        
        $userData = JWT::verify($token);
        
        if (!$userData) {
            $this->sendError(401, 'Invalid token');
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, l.name as location_name 
                FROM users u 
                LEFT JOIN locations l ON u.location_id = l.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$userData['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->sendError(401, 'User not found or inactive');
                return false;
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Auth check error: " . $e->getMessage());
            $this->sendError(500, 'Database error');
            return false;
        }
    }
    
    public function checkAuthOptional() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) return null;
        
        $userData = JWT::verify($token);
        if (!$userData) return null;
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, l.name as location_name 
                FROM users u 
                LEFT JOIN locations l ON u.location_id = l.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$userData['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function hasRole($requiredRole) {
        $user = $this->checkAuth();
        if (!$user) return false;
        
        if ($user['role'] === 'admin') return true;
        
        return $user['role'] === $requiredRole;
    }
    
    public function canAccessLocation($locationId) {
        $user = $this->checkAuth();
        if (!$user) return false;
        
        if ($user['role'] === 'admin') return true;
        
        if (in_array($user['role'], ['manager', 'worker_transport', 'worker_cleaner'])) {
            return $user['location_id'] == $locationId;
        }
        
        return true;
    }

    public function checkAuthForController() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            return null;
        }
        
        $userData = JWT::verify($token);
        if (!$userData) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, l.name as location_name 
                FROM users u 
                LEFT JOIN locations l ON u.location_id = l.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$userData['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Auth check error: " . $e->getMessage());
            return null;
        }
    }
    
    private function getTokenFromHeader() {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? '';
        } else {
            return null;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    private function sendError($code, $message) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }
}
?>
