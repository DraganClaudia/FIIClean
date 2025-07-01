<?php
require_once __DIR__ . '/../models/Resource.php';

class ResourceController {
    private $resourceModel;

    public function __construct() {
        $this->resourceModel = new Resource();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';
        $id = $_GET['id'] ?? null;

        switch($method) {
            case 'GET':
                if ($action === 'list') {
                    $this->getAllResources();
                } elseif($action === 'by-location' && $id) {
                    $this->getResourcesByLocation($id);
                } elseif($action === 'low-stock') {
                    $this->getLowStockResources();
                }
                break;

            case 'POST':
                if ($action === 'create') {
                    $this->createResource();
                }
                break;

            case 'PUT':
                if ($action === 'addQuantity') {
                    $this->addQuantity();
                }
                if ($action === 'subtractQuantity') {
                    $this->subtractQuantity();
                }
                break;

            case 'DELETE':
                if ($action === 'delete') {
                    $this->deleteResource();
                }
                break;
        }
    }

    private function getAllResources() {
        header('Content-Type: application/json');
        $resources = $this->resourceModel->getAll();
        echo json_encode($resources);
    }

    private function getResourcesByLocation($locationId) {
        header('Content-Type: application/json');
        $resources = $this->resourceModel->getByLocation($locationId);
        echo json_encode($resources);
    }
    
    private function getLowStockResources() {
        header('Content-Type: application/json');
        $threshold = $_GET['threshold'] ?? null;
        $resources = $this->resourceModel->getLowStock($threshold);
        echo json_encode($resources);
    }

    private function createResource() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if ($this->resourceModel->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Resource created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating resource']);
        }
    }

    private function addQuantity() {
        header('Content-Type: application/json');
        parse_str(file_get_contents("php://input"), $_PUT);

        $name = $_PUT['name'] ?? null;
        $departmentName = $_PUT['department_name'] ?? null;
        $amount = $_PUT['amount'] ?? null;

        if ($name && $departmentName && $amount) {
        if ($this->resourceModel->addQuantityByDepartmentName($name, $departmentName, $amount)) {
            echo json_encode(['success' => true, 'message' => 'Quantity added successfully']);
        } else {
        echo json_encode(['success' => false, 'message' => 'Error adding quantity']);
        }
        } else {
        echo json_encode(['success' => false, 'message' => 'Name, department_name and amount are required']);
        }
    }

    private function subtractQuantity() {
        header('Content-Type: application/json');
        parse_str(file_get_contents("php://input"), $_PUT);

        $name = $_PUT['name'] ?? null;
        $departmentName = $_PUT['department_name'] ?? null;
        $amount = $_PUT['amount'] ?? null;


       if ($name && $departmentName && $amount) {
       if ($this->resourceModel->subtractQuantityByDepartmentName($name, $departmentName, $amount)) {
            echo json_encode(['success' => true, 'message' => 'Quantity subtracted successfully']);
        } else {
        echo json_encode(['success' => false, 'message' => 'Error subtracting quantity']);
        }
        } else {
        echo json_encode(['success' => false, 'message' => 'Name, department_name and amount are required']);
        }
    }

    private function deleteResource() {
        header('Content-Type: application/json');
        parse_str(file_get_contents("php://input"), $_DELETE);

        $name = $_DELETE['name'] ?? null;
        $locationId = $_DELETE['location_id'] ?? null;

        if ($name && $locationId) {
            if ($this->resourceModel->deleteByNameAndLocation($name, $locationId)) {
                echo json_encode(['success' => true, 'message' => 'Resource deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting resource']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Name and location_id are required']);
        }
    }
}
