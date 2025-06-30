<?php
require_once '../models/Order.php';

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';
        $id = $_GET['id'] ?? null;

        switch($method) {
            case 'GET':
                if($action === 'list') {
                    $this->getAllOrders();
                } elseif($action === 'location' && $id) {
                    $this->getOrdersByLocation($id);
                } elseif($action === 'statistics') {
                    $this->getOrderStatistics();
                }
                break;
            case 'POST':
                if($action === 'create') {
                    $this->createOrder();
                }
                break;
            case 'PUT':
                if($action === 'updateStatus' && $id) {
                    $this->updateOrderStatus($id);
                }
                break;
        }
    }

    private function getAllOrders() {
        header('Content-Type: application/json');
        $orders = $this->orderModel->getAll();
        echo json_encode($orders);
    }

    private function getOrdersByLocation($locationId) {
          header('Content-Type: application/json');
        $orders = $this->orderModel->getByLocation($locationId);
        echo json_encode($orders);
    }

    private function createOrder() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($this->orderModel->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Order created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating order']);
        }
    }

    private function updateOrderStatus($id) {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        if($this->orderModel->updateStatus($id, $input['status'])) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating status']);
        }
    }

    private function getOrderStatistics() {
        header('Content-Type: application/json');
        $stats = $this->orderModel->getStatistics();
        echo json_encode($stats);
    }
}
