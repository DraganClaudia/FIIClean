<?php
/**
 * APIController - handles REST API endpoints
 * Implements web services architecture with JSON responses
 */
require_once 'app/models/LocationModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ResourceModel.php';

class APIController extends Controller {
    private $locationModel;
    private $orderModel;
    private $resourceModel;
    
    public function __construct() {
        parent::__construct();
        
        // Set JSON header for all API responses
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        $this->locationModel = new LocationModel();
        $this->orderModel = new OrderModel();
        $this->resourceModel = new ResourceModel();
    }
    
    /**
     * API endpoint: GET /api/locations
     */
    public function getLocations() {
        try {
            $status = sanitize_input($_GET['status'] ?? null);
            $service = sanitize_input($_GET['service'] ?? null);
            
            if ($status) {
                $locations = $this->locationModel->getLocationsByStatus($status);
            } elseif ($service) {
                $locations = $this->locationModel->getLocationsByService($service);
            } else {
                $locations = $this->locationModel->getAllLocations();
            }
            
            $this->json([
                'success' => true,
                'data' => $locations,
                'count' => count($locations),
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getLocations: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la obținerea locațiilor'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/locations/{id}
     */
    public function getLocation() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            
            if (!$id) {
                $this->json([
                    'success' => false,
                    'error' => 'ID locație necesar'
                ], 400);
                return;
            }
            
            $location = $this->locationModel->getLocationById($id);
            
            if (!$location) {
                $this->json([
                    'success' => false,
                    'error' => 'Locația nu a fost găsită'
                ], 404);
                return;
            }
            
            $stats = $this->locationModel->getLocationStats($id);
            $resources = $this->locationModel->getLocationResources($id);
            $orders = $this->locationModel->getLocationOrders($id, 5);
            $operational = $this->locationModel->isLocationOperational($id);
            
            $this->json([
                'success' => true,
                'data' => [
                    'location' => $location,
                    'statistics' => $stats,
                    'resources' => $resources,
                    'recent_orders' => $orders,
                    'operational_status' => $operational
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getLocation: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la obținerea detaliilor locației'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/orders
     */
    public function getOrders() {
        try {
            $status = sanitize_input($_GET['status'] ?? null);
            $location_id = sanitize_numeric($_GET['location_id'] ?? null);
            $service_type = sanitize_input($_GET['service_type'] ?? null);
            $limit = sanitize_numeric($_GET['limit'] ?? 50, 1, 1000);
            
            $filters = [
                'status' => $status,
                'location_id' => $location_id,
                'service_type' => $service_type,
                'limit' => $limit
            ];
            
            $orders = $this->orderModel->getOrdersWithFilters($filters);
            
            $this->json([
                'success' => true,
                'data' => $orders,
                'count' => count($orders),
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getOrders: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la obținerea comenzilor'
            ], 500);
        }
    }
    
    /**
     * API endpoint: POST /api/orders
     */
    public function createOrder() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->json([
                    'success' => false,
                    'error' => 'Date JSON invalide'
                ], 400);
                return;
            }
            
            // Validate required fields
            $required_fields = ['client_id', 'location_id', 'service_type', 'scheduled_date'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $this->json([
                        'success' => false,
                        'error' => "Câmpul '$field' este obligatoriu"
                    ], 400);
                    return;
                }
            }
            
            // Sanitize input data
            $order_data = [
                'client_id' => sanitize_numeric($input['client_id']),
                'location_id' => sanitize_numeric($input['location_id']),
                'service_type' => sanitize_input($input['service_type']),
                'scheduled_date' => sanitize_input($input['scheduled_date']),
                'recurring' => isset($input['recurring']) ? (bool)$input['recurring'] : false,
                'transport' => isset($input['transport']) ? (bool)$input['transport'] : false
            ];
            
            // Validate service type
            $valid_services = ['covor', 'auto', 'textil'];
            if (!in_array($order_data['service_type'], $valid_services)) {
                $this->json([
                    'success' => false,
                    'error' => 'Tip serviciu invalid'
                ], 400);
                return;
            }
            
            // Check if location is available
            $location = $this->locationModel->getLocationById($order_data['location_id']);
            if (!$location || $location['Stare'] !== 'activ') {
                $this->json([
                    'success' => false,
                    'error' => 'Locația nu este disponibilă'
                ], 400);
                return;
            }
            
            $order_id = $this->orderModel->createOrder($order_data);
            
            $this->json([
                'success' => true,
                'data' => [
                    'order_id' => $order_id,
                    'message' => 'Comanda a fost creată cu succes'
                ],
                'timestamp' => date('c')
            ], 201);
            
        } catch (Exception $e) {
            error_log("API Error - createOrder: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la crearea comenzii'
            ], 500);
        }
    }
    
    /**
     * API endpoint: PUT /api/orders/{id}
     */
    public function updateOrder() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$id || !$input) {
                $this->json([
                    'success' => false,
                    'error' => 'ID comandă și date JSON necesare'
                ], 400);
                return;
            }
            
            // Check if order exists
            $order = $this->orderModel->getOrderById($id);
            if (!$order) {
                $this->json([
                    'success' => false,
                    'error' => 'Comanda nu a fost găsită'
                ], 404);
                return;
            }
            
            $update_data = [];
            
            // Status update
            if (isset($input['status'])) {
                $valid_statuses = ['noua', 'in curs', 'finalizata', 'anulata'];
                $status = sanitize_input($input['status']);
                
                if (!in_array($status, $valid_statuses)) {
                    $this->json([
                        'success' => false,
                        'error' => 'Status invalid'
                    ], 400);
                    return;
                }
                
                $update_data['status'] = $status;
            }
            
            $success = $this->orderModel->updateOrder($id, $update_data);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'data' => [
                        'order_id' => $id,
                        'message' => 'Comanda a fost actualizată cu succes'
                    ],
                    'timestamp' => date('c')
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => 'Eroare la actualizarea comenzii'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - updateOrder: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la actualizarea comenzii'
            ], 500);
        }
    }
    
    /**
     * API endpoint: DELETE /api/orders/{id}
     */
    public function deleteOrder() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            
            if (!$id) {
                $this->json([
                    'success' => false,
                    'error' => 'ID comandă necesar'
                ], 400);
                return;
            }
            
            $order = $this->orderModel->getOrderById($id);
            if (!$order) {
                $this->json([
                    'success' => false,
                    'error' => 'Comanda nu a fost găsită'
                ], 404);
                return;
            }
            
            $success = $this->orderModel->deleteOrder($id);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => 'Comanda a fost ștearsă cu succes'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => 'Eroare la ștergerea comenzii'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - deleteOrder: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la ștergerea comenzii'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/resources
     */
    public function getResources() {
        try {
            $type = sanitize_input($_GET['type'] ?? null);
            
            if ($type) {
                $resources = $this->resourceModel->getResourcesByType($type);
            } else {
                $resources = $this->resourceModel->getAllResources();
            }
            
            $this->json([
                'success' => true,
                'data' => $resources,
                'count' => count($resources),
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getResources: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la obținerea resurselor'
            ], 500);
        }
    }
    
    /**
     * API endpoint: PUT /api/resources/{id}
     */
    public function updateResource() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$id || !$input || !isset($input['quantity'])) {
                $this->json([
                    'success' => false,
                    'error' => 'ID resursă și cantitate necesare'
                ], 400);
                return;
            }
            
            $quantity = sanitize_numeric($input['quantity'], 0);
            if ($quantity === false) {
                $this->json([
                    'success' => false,
                    'error' => 'Cantitate invalidă'
                ], 400);
                return;
            }
            
            $success = $this->resourceModel->updateResourceQuantity($id, $quantity);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'data' => [
                        'resource_id' => $id,
                        'new_quantity' => $quantity,
                        'message' => 'Resursa a fost actualizată cu succes'
                    ],
                    'timestamp' => date('c')
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => 'Eroare la actualizarea resursei'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - updateResource: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la actualizarea resursei'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/statistics
     */
    public function getStatistics() {
        try {
            $type = sanitize_input($_GET['type'] ?? 'overview');
            
            $stats = [];
            
            switch ($type) {
                case 'locations':
                    $stats = [
                        'total_locations' => $this->locationModel->getTotalLocations(),
                        'active_locations' => $this->locationModel->getActiveLocationsCount()
                    ];
                    break;
                    
                case 'orders':
                    $stats = [
                        'total_orders' => $this->orderModel->getTotalOrdersCount(),
                        'orders_by_status' => $this->orderModel->getOrdersByStatusCount()
                    ];
                    break;
                    
                case 'resources':
                    $stats = [
                        'total_resources' => $this->resourceModel->getTotalResourcesCount(),
                        'resources_by_type' => $this->resourceModel->getResourcesByTypeCount()
                    ];
                    break;
                    
                default:
                    $stats = [
                        'total_locations' => $this->locationModel->getTotalLocations(),
                        'active_orders' => $this->orderModel->getActiveOrdersCount(),
                        'today_orders' => $this->orderModel->getTodayOrdersCount()
                    ];
            }
            
            $this->json([
                'success' => true,
                'data' => $stats,
                'type' => $type,
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getStatistics: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la obținerea statisticilor'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/health
     */
    public function healthCheck() {
        try {
            $health_data = [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'version' => '1.0.0',
                'database' => $this->checkDatabaseHealth(),
                'services' => [
                    'locations' => $this->checkLocationService(),
                    'orders' => $this->checkOrderService(),
                    'resources' => $this->checkResourceService()
                ]
            ];
            
            // Determine overall health status
            $all_healthy = $health_data['database']['status'] === 'ok';
            foreach ($health_data['services'] as $service) {
                if ($service['status'] !== 'ok') {
                    $all_healthy = false;
                    break;
                }
            }
            
            if (!$all_healthy) {
                $health_data['status'] = 'degraded';
            }
            
            $status_code = $all_healthy ? 200 : 503;
            
            $this->json($health_data, $status_code);
            
        } catch (Exception $e) {
            error_log("API Error - healthCheck: " . $e->getMessage());
            $this->json([
                'status' => 'unhealthy',
                'error' => 'Eroare la verificarea stării sistemului',
                'timestamp' => date('c')
            ], 503);
        }
    }
    
    /**
     * API endpoint: GET /api/search
     */
    public function search() {
        try {
            $query = sanitize_input($_GET['q'] ?? '');
            $type = sanitize_input($_GET['type'] ?? 'all');
            $limit = sanitize_numeric($_GET['limit'] ?? 20, 1, 100);
            
            if (empty($query) || strlen($query) < 2) {
                $this->json([
                    'success' => false,
                    'error' => 'Interogarea trebuie să aibă cel puțin 2 caractere'
                ], 400);
                return;
            }
            
            $results = [];
            
            if ($type === 'all' || $type === 'locations') {
                $results['locations'] = $this->locationModel->searchLocations($query, $limit);
            }
            
            if ($type === 'all' || $type === 'orders') {
                $results['orders'] = $this->orderModel->searchOrders($query, $limit);
            }
            
            if ($type === 'all' || $type === 'resources') {
                $results['resources'] = $this->resourceModel->searchResources($query, $limit);
            }
            
            $total_results = array_sum(array_map('count', $results));
            
            $this->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'type' => $type,
                    'total_results' => $total_results,
                    'limit' => $limit
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - search: " . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Eroare la căutare'
            ], 500);
        }
    }
    
    /**
     * Check database connectivity
     */
    private function checkDatabaseHealth() {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT 1");
            
            return [
                'status' => 'ok',
                'response_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => 'Database connection failed'
            ];
        }
    }
    
    /**
     * Check location service
     */
    private function checkLocationService() {
        try {
            $count = $this->locationModel->getTotalLocations();
            return [
                'status' => 'ok',
                'total_locations' => $count
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => 'Location service failed'
            ];
        }
    }
    
    /**
     * Check order service
     */
    private function checkOrderService() {
        try {
            $count = $this->orderModel->getActiveOrdersCount();
            return [
                'status' => 'ok',
                'active_orders' => $count
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => 'Order service failed'
            ];
        }
    }
    
    /**
     * Check resource service
     */
    private function checkResourceService() {
        try {
            $count = $this->resourceModel->getTotalResourcesCount();
            return [
                'status' => 'ok',
                'total_resources' => $count
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => 'Resource service failed'
            ];
        }
    }
    
    /**
     * Handle undefined API endpoints
     */
    public function notFound() {
        $this->json([
            'success' => false,
            'error' => 'Endpoint API nu a fost găsit',
            'available_endpoints' => [
                'GET /api/locations',
                'GET /api/locations/{id}',
                'GET /api/orders',
                'POST /api/orders',
                'PUT /api/orders/{id}',
                'DELETE /api/orders/{id}',
                'GET /api/resources',
                'PUT /api/resources/{id}',
                'GET /api/statistics',
                'GET /api/search',
                'GET /api/health'
            ]
        ], 404);
    }
}