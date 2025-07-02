<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Security.php';

class Location {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT * FROM locations 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM locations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $data = Security::sanitizeInput($data);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO locations (name, address, latitude, longitude, services, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            
            return $stmt->execute([
                $data['name'],
                $data['address'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['services'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Location creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateStatus($id, $status) {
        try {
            $allowedStatuses = ['active', 'inactive', 'maintenance'];
            if (!in_array($status, $allowedStatuses)) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                UPDATE locations 
                SET status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Location status update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getActiveLocations() {
        $stmt = $this->db->prepare("
            SELECT * FROM locations 
            WHERE status = 'active' 
            ORDER BY name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
