<?php
require_once __DIR__ . '/../config/Database.php';
class Order {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT o.*, l.name as location_name 
            FROM orders o 
            LEFT JOIN locations l ON o.location_id = l.id 
            ORDER BY o.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByLocation($locationId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE location_id = ? ORDER BY created_at DESC");
        $stmt->execute([$locationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO orders (location_id, client_name, client_phone, client_email, 
                              service_type, pickup_address, delivery_address, 
                              scheduled_date, price, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['location_id'],
            $data['client_name'],
            $data['client_phone'],
            $data['client_email'],
            $data['service_type'],
            $data['pickup_address'],
            $data['delivery_address'],
            $data['scheduled_date'],
            $data['price'],
            $data['notes']
        ]);
    }

    public function updateStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    public function getStatistics() {
    $stmt = $this->db->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
            AVG(total_price) as average_price,
            SUM(total_price) as total_revenue
        FROM orders
    ");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}
