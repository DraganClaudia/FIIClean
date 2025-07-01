<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/Auth.php';

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
        // Pentru acces public la comenzi (fără autentificare strictă)
        header('Content-Type: application/json');
        
        try {
            $orders = $this->orderModel->getAll();
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function getMyOrders() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        header('Content-Type: application/json');
        
        try {
            $orders = $this->orderModel->getByClient($user['id']);
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function getAssignedOrders() {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        if (!in_array($user['role'], ['worker_transport', 'worker_cleaner'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        
        try {
            if ($user['role'] === 'worker_transport') {
                $orders = $this->orderModel->getByTransportWorker($user['id']);
            } else {
                $orders = $this->orderModel->getByCleaningWorker($user['id']);
            }
            
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function createOrder() {
        header('Content-Type: application/json');
        
        error_log("=== ORDER CREATION DEBUG ===");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("RAW INPUT: " . file_get_contents('php://input'));
        error_log("AUTH HEADER: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'MISSING'));
        // Verifică dacă datele sunt trimise ca JSON
        $rawInput = file_get_contents('php://input');
        
        if (empty($rawInput)) {
            http_response_code(400);
            echo json_encode(['error' => 'No data received']);
            return;
        }
        
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }

        // Verifică autentificarea pentru utilizatori logați
        $user = null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if ($authHeader) {
            $user = $this->auth->checkAuth();
            
            // Pentru client-i, setează automat datele lor
            if ($user && $user['role'] === 'client') {
                $input['client_id'] = $user['id'];
                $input['client_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $input['client_email'] = $user['email'];
                $input['client_phone'] = $user['phone'];
            } elseif (empty($input['client_name'])) {
                // Dacă nu e client autentificat și nu are client_name, returnează eroare
                http_response_code(400);
                echo json_encode(['error' => 'Client name is required']);
                return;
            }
            
            // Manager poate crea comenzi doar pentru locația sa
            if ($user && $user['role'] === 'manager') {
                if (isset($input['location_id']) && !$this->auth->checkLocationAccess($input['location_id'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Cannot create orders for this location']);
                    return;
                }
            }
        }
        
        // Validare câmpuri obligatorii
        $required = ['service_type'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field $field is required"]);
                return;
            }
        }
        
        try {
            if ($this->orderModel->create($input)) {
                echo json_encode(['success' => true, 'message' => 'Order created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error creating order']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function updateOrderStatus($id) {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }
        
        try {
            // Verifică cine poate actualiza această comandă
            $order = $this->orderModel->getById($id);
            if (!$order) {
                http_response_code(404);
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
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            if ($this->orderModel->updateStatus($id, $input)) {
                echo json_encode(['success' => true, 'message' => 'Status updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error updating status']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function assignWorker($id) {
        $user = $this->auth->checkAuth();
        if (!$user) return;
        
        // Doar admin și manager pot asigna worker-i
        if (!in_array($user['role'], ['admin', 'manager'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input || !isset($input['worker_id']) || !isset($input['worker_type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        
        $workerId = $input['worker_id'];
        $workerType = $input['worker_type']; // 'transport' sau 'cleaner'
        
        $field = $workerType === 'transport' ? 'assigned_transport' : 'assigned_cleaner';
        
        try {
            if ($this->orderModel->assignWorker($id, $workerId, $field)) {
                echo json_encode(['success' => true, 'message' => 'Worker assigned']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error assigning worker']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function getOrdersByLocation($locationId) {
        // Permite acces public pentru locații
        header('Content-Type: application/json');
        
        try {
            $orders = $this->orderModel->getByLocation($locationId);
            echo json_encode($orders);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function getOrderStatistics() {
        // Permite acces public pentru statistici
        header('Content-Type: application/json');
        
        try {
            $stats = $this->orderModel->getStatistics();
            echo json_encode($stats);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
?>
