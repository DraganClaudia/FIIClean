<?php
require_once __DIR__ . '/../config/Database.php';

class Order {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT o.*, l.name as location_name,
                   u1.first_name || ' ' || u1.last_name as client_full_name,
                   u2.first_name || ' ' || u2.last_name as transport_worker_name,
                   u3.first_name || ' ' || u3.last_name as cleaner_worker_name
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            LEFT JOIN users u1 ON o.client_id = u1.id
            LEFT JOIN users u2 ON o.assigned_transport = u2.id
            LEFT JOIN users u3 ON o.assigned_cleaner = u3.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByClient($clientId) {
        $stmt = $this->db->prepare("
            SELECT o.*, l.name as location_name
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            WHERE o.client_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByTransportWorker($workerId) {
        $stmt = $this->db->prepare("
            SELECT o.*, l.name as location_name
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            WHERE o.assigned_transport = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByCleaningWorker($workerId) {
        $stmt = $this->db->prepare("
            SELECT o.*, l.name as location_name
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            WHERE o.assigned_cleaner = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByLocation($locationId) {
        $stmt = $this->db->prepare("
            SELECT o.*, l.name as location_name 
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            WHERE o.location_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$locationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO orders (location_id, client_id, client_name, client_phone, client_email, 
                              service_type, pickup_address, delivery_address, 
                              scheduled_date, price, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['location_id'],
            $data['client_id'] ?? null,
            $data['client_name'],
            $data['client_phone'] ?? null,
            $data['client_email'] ?? null,
            $data['service_type'],
            $data['pickup_address'] ?? null,
            $data['delivery_address'] ?? null,
            $data['scheduled_date'] ?? null,
            $data['price'] ?? null,
            $data['notes'] ?? null
        ]);
    }

    public function updateStatus($id, $statusData) {
        $allowedFields = ['status', 'transport_status', 'cleaning_status', 'notes'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($statusData[$field])) {
                $updates[] = "$field = ?";
                $params[] = $statusData[$field];
            }
        }
        
        if (empty($updates)) return false;
        
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?");
        return $stmt->execute($params);
    }
    
    public function assignWorker($orderId, $workerId, $field) {
        $stmt = $this->db->prepare("UPDATE orders SET $field = ? WHERE id = ?");
        return $stmt->execute([$workerId, $orderId]);
    }

    public function getStatistics() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                AVG(price) as average_price,
                SUM(price) as total_revenue
            FROM orders
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
