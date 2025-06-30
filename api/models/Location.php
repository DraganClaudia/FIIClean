<?php
require_once '../config/Database.php';

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
}
