<?php
require_once __DIR__ . '/../config/Database.php';

class Resource {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM resources");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO resources 
            (location_id, resource_type, name, quantity, unit, min_threshold, cost_per_unit, supplier, last_restocked)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['location_id'],
            $data['resource_type'],
            $data['name'],
            $data['quantity'],
            $data['unit'],
            $data['min_threshold'],
            $data['cost_per_unit'],
            $data['supplier'],
            $data['last_restocked']
        ]);
    }
  public function addQuantityByDepartmentName($name, $departmentName, $amount) {
    $stmt = $this->db->prepare("
        UPDATE resources
        SET quantity = quantity + ?
        WHERE name = ? 
        AND location_id = (
            SELECT id FROM locations WHERE name = ?
        )
    ");
    return $stmt->execute([$amount, $name, $departmentName]);
}



public function deleteByNameAndLocation($name, $locationId) {
    $stmt = $this->db->prepare("
        DELETE FROM resources
        WHERE name = ? AND location_id = ?
    ");
    return $stmt->execute([$name, $locationId]);
}
public function subtractQuantityByDepartmentName($name, $departmentName, $amount) {
    $stmt = $this->db->prepare("
        UPDATE resources
        SET quantity = quantity - ?
        WHERE name = ? 
        AND location_id = (
            SELECT id FROM locations WHERE name = ?
        )
    ");
    return $stmt->execute([$amount, $name, $departmentName]);
}
    
    public function getLowStock($threshold = null) {
        $sql = "SELECT r.*, l.name as location_name 
                FROM resources r 
                LEFT JOIN locations l ON r.location_id = l.id 
                WHERE r.quantity <= " . ($threshold ? "?" : "r.min_threshold") . "
                ORDER BY r.quantity ASC";
        
        $stmt = $this->db->prepare($sql);
        $params = $threshold ? [$threshold] : [];
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByLocation($locationId) {
        $stmt = $this->db->prepare("
            SELECT * FROM resources 
            WHERE location_id = ? 
            ORDER BY name
        ");
        $stmt->execute([$locationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
