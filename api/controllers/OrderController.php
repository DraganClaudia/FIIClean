<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/SimpleAuth.php';

class OrderController {
    private $orderModel;
    private $auth;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->auth = new Auth();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';
        $id = $_GET['id'] ?? null;
        
        switch($method) {
            case 'GET':
                if($action === 'list') {
                    $this->getAllOrders();
                } elseif($action === 'my-orders') {
                    $this->getMyOrders();
                } elseif($action === 'assigned') {
                    $this->getAssignedOrders();
                } elseif($action === 'by-location' && $id) {
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
                } elseif($action === 'assign-worker' && $id) {
                    $this->assignWorker($id);
                }
                break;
        }
    }
    
    private function getAllOrders() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        header('Content-Type: application/json');
        
        if ($user['role'] === 'admin') {
            $orders = $this->orderModel->getAll();
        } elseif ($user['role'] === 'manager') {
            $orders = $this->orderModel->getByLocation($user['location_id']);
        } else {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        echo json_encode($orders);
    }
    
    private function getMyOrders() {
        if (!$this->auth->requireRole('client')) return;
        
        $user = $GLOBALS['current_user'];
        header('Content-Type: application/json');
        
        $orders = $this->orderModel->getByClient($user['id']);
        echo json_encode($orders);
    }
    
    private function getAssignedOrders() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        if (!in_array($user['role'], ['worker_transport', 'worker_cleaner'])) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        
        if ($user['role'] === 'worker_transport') {
            $orders = $this->orderModel->getByTransportWorker($user['id']);
        } else {
            $orders = $this->orderModel->getByCleaningWorker($user['id']);
        }
        
        echo json_encode($orders);
    }
    
    private function createOrder() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        // Verifică că are permisiunea să creeze comenzi
        if (!in_array($user['role'], ['admin', 'manager', 'client'])) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Pentru client-i, setează automat datele lor
        if ($user['role'] === 'client') {
            $input['client_id'] = $user['id'];
            $input['client_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $input['client_email'] = $user['email'];
            $input['client_phone'] = $user['phone'];
        }
        
        // Manager poate crea comenzi doar pentru locația sa
        if ($user['role'] === 'manager') {
            if (!$this->auth->checkLocationAccess($input['location_id'])) {
                echo json_encode(['error' => 'Cannot create orders for this location']);
                return;
            }
        }
        
        if($this->orderModel->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Order created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating order']);
        }
    }
    
    private function updateOrderStatus($id) {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verifică cine poate actualiza această comandă
        $order = $this->orderModel->getById($id);
        if (!$order) {
            echo json_encode(['error' => 'Order not found']);
            return;
        }
        
        $canUpdate = false;
        
        if ($user['role'] === 'admin') {
            $canUpdate = true;
        } elseif ($user['role'] === 'manager' && $order['location_id'] == $user['location_id']) {
            $canUpdate = true;
        } elseif ($user['role'] === 'worker_transport' && $order['assigned_transport'] == $user['id']) {
            // Worker transport poate actualiza doar transport_status
            $input = ['transport_status' => $input['transport_status'] ?? $input['status']];
            $canUpdate = true;
        } elseif ($user['role'] === 'worker_cleaner' && $order['assigned_cleaner'] == $user['id']) {
            // Worker cleaner poate actualiza doar cleaning_status
            $input = ['cleaning_status' => $input['cleaning_status'] ?? $input['status']];
            $canUpdate = true;
        }
        
        if (!$canUpdate) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        if($this->orderModel->updateStatus($id, $input)) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating status']);
        }
    }
    
    private function assignWorker($id) {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        // Doar admin și manager pot asigna worker-i
        if (!in_array($user['role'], ['admin', 'manager'])) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        
        $workerId = $input['worker_id'];
        $workerType = $input['worker_type']; // 'transport' sau 'cleaner'
        
        $field = $workerType === 'transport' ? 'assigned_transport' : 'assigned_cleaner';
        
        if($this->orderModel->assignWorker($id, $workerId, $field)) {
            echo json_encode(['success' => true, 'message' => 'Worker assigned']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assigning worker']);
        }
    }
    
    // Restul metodelor rămân la fel
    private function getOrdersByLocation($locationId) {
        if (!$this->auth->checkLocationAccess($locationId)) {
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        $orders = $this->orderModel->getByLocation($locationId);
        echo json_encode($orders);
    }
    
    private function getOrderStatistics() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        header('Content-Type: application/json');
        $stats = $this->orderModel->getStatistics();
        echo json_encode($stats);
    }
}
?>
