<?php
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/../config/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // Verifică dacă utilizatorul e autentificat
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
        
        // Verifică dacă utilizatorul există și e activ
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userData['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $this->sendError(401, 'User not found');
            return false;
        }
        
        // Salvează datele utilizatorului pentru folosire în controllere
        $GLOBALS['current_user'] = $user;
        $GLOBALS['current_user']['token_data'] = $userData;
        
        return $user;
    }
    
    // Verifică rolul utilizatorului
    public function requireRole($role) {
        $user = $this->checkAuth();
        if (!$user) return false;
        
        // Admin poate totul
        if ($user['role'] === 'admin') return true;
        
        if ($user['role'] !== $role) {
            $this->sendError(403, 'Insufficient permissions');
            return false;
        }
        
        return true;
    }
    
    // Verifică accesul la o locație
    public function checkLocationAccess($locationId) {
        $user = $GLOBALS['current_user'] ?? null;
        if (!$user) return false;
        
        // Admin poate accesa toate locațiile
        if ($user['role'] === 'admin') return true;
        
        // Manager și worker-ii pot accesa doar locația lor
        if (in_array($user['role'], ['manager', 'worker_transport', 'worker_cleaner'])) {
            return $user['location_id'] == $locationId;
        }
        
        // Client-ii pot accesa toate locațiile
        return true;
    }
    
    // Verifică ownership (client poate vedea doar comenzile sale)
    public function checkOwnership($resourceId, $tableName = 'orders', $ownerField = 'client_id') {
        $user = $GLOBALS['current_user'] ?? null;
        if (!$user) return false;
        
        // Admin poate vedea totul
        if ($user['role'] === 'admin') return true;
        
        $stmt = $this->db->prepare("SELECT $ownerField FROM $tableName WHERE id = ?");
        $stmt->execute([$resourceId]);
        $ownerId = $stmt->fetchColumn();
        
        return $ownerId == $user['id'];
    }
    
    // Helper pentru token din header
    private function getTokenFromHeader() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    // Helper pentru răspunsuri de eroare
    private function sendError($code, $message) {
        header("HTTP/1.1 $code");
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }
}
?>
