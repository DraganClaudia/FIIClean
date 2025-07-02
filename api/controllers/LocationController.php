<?php
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../helpers/Security.php';
class LocationController {
    private $locationModel;
    
    public function __construct() {
        $this->locationModel = new Location();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';
        $id = $_GET['id'] ?? null;
        
        switch($method) {
            case 'GET':
                if($action === 'list') {
                    $this->getAllLocations();
                }
                break;
            case 'POST':
                if($action === 'create') {
                    $this->createLocation();
                }
                break;
            case 'PUT':
                if($action === 'update-status' && $id) {
                    $this->updateLocationStatus($id);
                }
                break;
        }
    }
    
    private function getAllLocations() {
        header('Content-Type: application/json');
        $locations = $this->locationModel->getAll();
        echo json_encode($locations);
    }
    
    private function createLocation() {
        header('Content-Type: application/json');
        $rawInput = json_decode(file_get_contents('php://input'), true);
        $input = Security::sanitizeInput($rawInput);
        
        if($this->locationModel->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Location created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating location']);
        }
    }
    private function updateLocationStatus($id) {
        header('Content-Type: application/json');
        $rawInput = json_decode(file_get_contents('php://input'), true);
        $input = Security::sanitizeInput($rawInput);
        
        $status = $input['status'] ?? '';
        $allowedStatuses = ['active', 'inactive', 'maintenance'];
        
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Status invalid']);
            return;
        }
        
        if($this->locationModel->updateStatus($id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error updating status']);
        }
    }
}
?>
