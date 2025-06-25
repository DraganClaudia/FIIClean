<?php
/**
 * ClientController - handles client-facing operations
 */
require_once 'app/models/OrderModel.php';
require_once 'app/models/LocationModel.php';
require_once 'app/models/UserModel.php';

class ClientController extends Controller {
    private $orderModel;
    private $locationModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        
        // Require authentication for all client actions
        $this->requireAuth();
        
        $this->orderModel = new OrderModel();
        $this->locationModel = new LocationModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Require authentication helper
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Autentificare necesară'], 401);
            } else {
                $this->redirect('/CaS_FII-Clean/app/action/login.php');
            }
            exit();
        }
    }
    
    /**
     * Client dashboard
     */
    public function dashboard() {
        try {
            $user_id = $_SESSION['user_id'];
            
            $recent_orders = $this->orderModel->getOrdersByUserId($user_id, 5);
            
            $stats = [
                'total_orders' => $this->orderModel->getUserOrderCount($user_id),
                'pending_orders' => $this->orderModel->getUserOrderCount($user_id, 'noua'),
                'in_progress_orders' => $this->orderModel->getUserOrderCount($user_id, 'in curs'),
                'completed_orders' => $this->orderModel->getUserOrderCount($user_id, 'finalizata')
            ];
            
            $locations = $this->locationModel->getActiveLocations();
            $service_usage = $this->orderModel->getUserServiceUsage($user_id);
            
            $data = [
                'title' => 'Dashboard Client - CaS',
                'stats' => $stats,
                'recent_orders' => $recent_orders,
                'locations' => $locations,
                'service_usage' => $service_usage,
                'user' => $this->getCurrentUser(),
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Error in client dashboard: " . $e->getMessage());
            $this->view('client/dashboard', [
                'title' => 'Dashboard Client - CaS',
                'error' => 'Eroare la încărcarea dashboard-ului'
            ]);
        }
    }
    
    /**
     * View user's orders
     */
    public function myOrders() {
        try {
            $user_id = $_SESSION['user_id'];
            $status_filter = sanitize_input($_GET['status'] ?? null);
            $service_filter = sanitize_input($_GET['service'] ?? null);
            $page = sanitize_numeric($_GET['page'] ?? 1, 1);
            $per_page = 10;
            
            $filters = [
                'user_id' => $user_id,
                'status' => $status_filter,
                'service_type' => $service_filter,
                'page' => $page,
                'per_page' => $per_page
            ];
            
            $orders = $this->orderModel->getOrdersWithFilters($filters);
            $total_orders = $this->orderModel->getOrdersCountWithFilters($filters);
            $total_pages = ceil($total_orders / $per_page);
            
            $status_options = $this->orderModel->getOrderStatusOptions();
            $service_options = $this->orderModel->getServiceTypeOptions();
            
            $data = [
                'title' => 'Comenzile Mele - CaS',
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_orders' => $total_orders,
                    'per_page' => $per_page
                ],
                'filters' => [
                    'status' => $status_filter,
                    'service' => $service_filter
                ],
                'filter_options' => [
                    'status' => $status_options,
                    'service' => $service_options
                ],
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/my_orders', $data);
            
        } catch (Exception $e) {
            error_log("Error in my orders: " . $e->getMessage());
            $this->view('client/my_orders', [
                'title' => 'Comenzile Mele - CaS',
                'error' => 'Eroare la încărcarea comenzilor'
            ]);
        }
    }
    
    /**
     * Create new order
     */
    public function newOrder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processNewOrder();
            
            if ($this->isAjax()) {
                $this->json($result);
            } else {
                if ($result['success']) {
                    $this->redirect('?controller=client&action=myOrders&success=order_created');
                } else {
                    $this->showNewOrderForm($result['error']);
                }
            }
            return;
        }
        
        $this->showNewOrderForm();
    }
    
    /**
     * Show new order form
     */
    private function showNewOrderForm($error = null) {
        try {
            $locations = $this->locationModel->getActiveLocations();
            $user = $this->getCurrentUser();
            
            $data = [
                'title' => 'Comandă Nouă - CaS',
                'locations' => $locations,
                'user' => $user,
                'error' => $error,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/new_order', $data);
            
        } catch (Exception $e) {
            error_log("Error showing new order form: " . $e->getMessage());
            $this->view('client/new_order', [
                'title' => 'Comandă Nouă - CaS',
                'error' => 'Eroare la încărcarea formularului'
            ]);
        }
    }
    
    /**
     * Process new order creation
     */
    private function processNewOrder() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $user_id = $_SESSION['user_id'];
        $location_id = sanitize_numeric($_POST['location_id'] ?? null);
        $service_type = sanitize_input($_POST['service_type'] ?? '');
        $scheduled_date = sanitize_input($_POST['scheduled_date'] ?? '');
        $transport = isset($_POST['transport']) ? 1 : 0;
        $recurring = isset($_POST['recurring']) ? 1 : 0;
        
        if (!$location_id || empty($service_type) || empty($scheduled_date)) {
            return ['success' => false, 'error' => 'Toate câmpurile obligatorii trebuie completate'];
        }
        
        $valid_services = ['covor', 'auto', 'textil'];
        if (!in_array($service_type, $valid_services)) {
            return ['success' => false, 'error' => 'Tip serviciu invalid'];
        }
        
        $date = DateTime::createFromFormat('Y-m-d', $scheduled_date);
        if (!$date || $date->format('Y-m-d') !== $scheduled_date) {
            return ['success' => false, 'error' => 'Data nu este validă'];
        }
        
        if ($date <= new DateTime()) {
            return ['success' => false, 'error' => 'Data programării trebuie să fie în viitor'];
        }
        
        $location = $this->locationModel->getLocationById($location_id);
        if (!$location || $location['Stare'] !== 'activ') {
            return ['success' => false, 'error' => 'Locația selectată nu este disponibilă'];
        }
        
        try {
            $client_id = $this->getOrCreateClientRecord($user_id);
            
            if (!$client_id) {
                return ['success' => false, 'error' => 'Eroare la crearea înregistrării client'];
            }
            
            $order_data = [
                'client_id' => $client_id,
                'location_id' => $location_id,
                'service_type' => $service_type,
                'scheduled_date' => $scheduled_date,
                'transport' => $transport,
                'recurring' => $recurring,
                'status' => 'noua'
            ];
            
            $order_id = $this->orderModel->createOrder($order_data);
            
            if ($order_id) {
                return [
                    'success' => true,
                    'message' => 'Comanda a fost creată cu succes',
                    'order_id' => $order_id
                ];
            } else {
                return ['success' => false, 'error' => 'Eroare la crearea comenzii'];
            }
            
        } catch (Exception $e) {
            error_log("Error creating new order: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la procesarea comenzii'];
        }
    }
    
    /**
     * Get or create client record for user
     */
    private function getOrCreateClientRecord($user_id) {
        try {
            $client = $this->orderModel->getClientByUserId($user_id);
            
            if ($client) {
                return $client['id'];
            }
            
            $user = $this->getCurrentUser();
            $client_data = [
                'name' => $user['username'],
                'email' => $user['email'],
                'phone' => '',
                'address' => '',
                'user_id' => $user_id
            ];
            
            return $this->orderModel->createClient($client_data);
            
        } catch (Exception $e) {
            error_log("Error getting/creating client record: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * View order details
     */
    public function viewOrder() {
        $order_id = sanitize_numeric($_GET['id'] ?? null);
        
        if (!$order_id) {
            if ($this->isAjax()) {
                $this->json(['error' => 'ID comandă necesar'], 400);
            } else {
                $this->redirect('?controller=client&action=myOrders');
            }
            return;
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $order = $this->orderModel->getOrderByIdAndUserId($order_id, $user_id);
            
            if (!$order) {
                if ($this->isAjax()) {
                    $this->json(['error' => 'Comanda nu a fost găsită'], 404);
                } else {
                    $this->redirect('?controller=client&action=myOrders&error=not_found');
                }
                return;
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'order' => $order
                ]);
            } else {
                $data = [
                    'title' => 'Detalii Comandă #' . $order_id . ' - CaS',
                    'order' => $order,
                    'csrf_token' => generate_csrf_token()
                ];
                
                $this->view('client/view_order', $data);
            }
            
        } catch (Exception $e) {
            error_log("Error viewing order: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->json(['error' => 'Eroare la încărcarea comenzii'], 500);
            } else {
                $this->redirect('?controller=client&action=myOrders&error=load_error');
            }
        }
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Metodă nepermisă'], 405);
            return;
        }
        
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $order_id = sanitize_numeric($_POST['order_id'] ?? null);
        $reason = sanitize_input($_POST['reason'] ?? '');
        
        if (!$order_id) {
            $this->json(['error' => 'ID comandă necesar'], 400);
            return;
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $order = $this->orderModel->getOrderByIdAndUserId($order_id, $user_id);
            
            if (!$order) {
                $this->json(['error' => 'Comanda nu a fost găsită'], 404);
                return;
            }
            
            if (!in_array($order['Status'], ['noua', 'in curs'])) {
                $this->json(['error' => 'Comanda nu poate fi anulată în starea curentă'], 400);
                return;
            }
            
            $success = $this->orderModel->cancelOrder($order_id, $reason);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => 'Comanda a fost anulată cu succes'
                ]);
            } else {
                $this->json(['error' => 'Eroare la anularea comenzii'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            $this->json(['error' => 'Eroare la anularea comenzii'], 500);
        }
    }
    
    /**
     * Rate/review completed order
     */
    public function rateOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Metodă nepermisă'], 405);
            return;
        }
        
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $order_id = sanitize_numeric($_POST['order_id'] ?? null);
        $rating = sanitize_numeric($_POST['rating'] ?? null, 1, 5);
        $review = sanitize_input($_POST['review'] ?? '');
        
        if (!$order_id || !$rating) {
            $this->json(['error' => 'ID comandă și rating sunt necesare'], 400);
            return;
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $order = $this->orderModel->getOrderByIdAndUserId($order_id, $user_id);
            
            if (!$order) {
                $this->json(['error' => 'Comanda nu a fost găsită'], 404);
                return;
            }
            
            if ($order['Status'] !== 'finalizata') {
                $this->json(['error' => 'Puteți evalua doar comenzile finalizate'], 400);
                return;
            }
            
            $success = $this->orderModel->rateOrder($order_id, $rating, $review);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => 'Evaluarea a fost salvată cu succes'
                ]);
            } else {
                $this->json(['error' => 'Eroare la salvarea evaluării'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Error rating order: " . $e->getMessage());
            $this->json(['error' => 'Eroare la salvarea evaluării'], 500);
        }
    }
    
    /**
     * Get current user data
     */
    private function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            return $this->userModel->getUserById($_SESSION['user_id']);
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update client profile information
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Metodă nepermisă'], 405);
            return;
        }
        
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $phone = sanitize_input($_POST['phone'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        
        try {
            $user_id = $_SESSION['user_id'];
            $client_id = $this->getOrCreateClientRecord($user_id);
            
            if (!$client_id) {
                $this->json(['error' => 'Eroare la obținerea profilului client'], 500);
                return;
            }
            
            $update_data = [
                'phone' => $phone,
                'address' => $address
            ];
            
            $success = $this->orderModel->updateClientProfile($client_id, $update_data);
            
            if ($success) {
                $this->json([
                    'success' => true,
                    'message' => 'Profilul a fost actualizat cu succes'
                ]);
            } else {
                $this->json(['error' => 'Eroare la actualizarea profilului'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Error updating client profile: " . $e->getMessage());
            $this->json(['error' => 'Eroare la actualizarea profilului'], 500);
        }
    }
    
    /**
     * Export user's orders to CSV
     */
    public function exportOrders() {
        if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $orders = $this->orderModel->getAllOrdersByUserId($user_id);
            
            if (empty($orders)) {
                $this->json(['error' => 'Nu aveți comenzi de exportat'], 400);
                return;
            }
            
            $filename = 'comenzile_mele_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'ID Comandă',
                'Tip Serviciu', 
                'Data Programare',
                'Status',
                'Transport',
                'Recurentă',
                'Locație',
                'Data Creare'
            ]);
            
            // CSV data
            foreach ($orders as $order) {
                fputcsv($output, [
                    $order['id'],
                    ucfirst($order['TipServiciu']),
                    $order['DataProgramare'],
                    ucfirst($order['Status']),
                    $order['Transport'] ? 'Da' : 'Nu',
                    $order['Recurenta'] ? 'Da' : 'Nu',
                    $order['sediu_name'] ?? 'N/A',
                    date('Y-m-d H:i:s')
                ]);
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            error_log("Error exporting orders: " . $e->getMessage());
            $this->json(['error' => 'Eroare la export'], 500);
        }
    }
    
    /**
     * Get order history for specific order
     */
    public function getOrderHistory() {
        $order_id = sanitize_numeric($_GET['id'] ?? null);
        
        if (!$order_id) {
            $this->json(['error' => 'ID comandă necesar'], 400);
            return;
        }
        
        try {
            $user_id = $_SESSION['user_id'];
            $order = $this->orderModel->getOrderByIdAndUserId($order_id, $user_id);
            
            if (!$order) {
                $this->json(['error' => 'Comanda nu a fost găsită'], 404);
                return;
            }
            
            $history = $this->orderModel->getOrderHistory($order_id);
            
            $this->json([
                'success' => true,
                'history' => $history
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting order history: " . $e->getMessage());
            $this->json(['error' => 'Eroare la obținerea istoricului'], 500);
        }
    }
}