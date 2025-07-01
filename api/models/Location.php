<?php
require_once __DIR__ . '/../config/Database.php';

class Location {
    private $db;
    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll(){
        $stmt = $this->db->prepare("SELECT * FROM locations");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data){
        $stmt = $this->db->prepare("INSERT INTO locations (name, address, latitude, longitude, services)
            VALUES (?, ?, ?, ?, ?)
            ");
        return $stmt->execute([
            $data['name'],
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $data['services']
        ]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE locations 
            SET status = ?, updated_at = datetime('now') 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $id]);
    }
}
