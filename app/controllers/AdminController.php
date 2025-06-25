<?php
/**
 * AdminController - handles admin operations
 * Implements admin dashboard, user management, location management
 */
require_once 'app/models/UserModel.php';
require_once 'app/models/LocationModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ReportModel.php';

class AdminController extends Controller {
    private $userModel;
    private $locationModel;
    private $orderModel;
    private $reportModel;
    
    public function __construct() {
        parent::__construct();
        
        // Check admin authentication
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/CaS_FII-Clean/app/action/login.php');
            return;
        }
        
        $this->userModel = new UserModel();
        $this->locationModel = new LocationModel();
        $this->orderModel = new OrderModel();
        $this->reportModel = new ReportModel();
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard() {
        try {
            $stats = [
                'total_locations' => $this->locationModel->getTotalLocations(),
                'total_users' => $this->userModel->getTotalUsers(),
                'active_orders' => $this->orderModel->getActiveOrdersCount(),
                'today_orders' => $this->orderModel->getTodayOrdersCount()
            ];
            
            $recent_orders = $this->orderModel->getRecentOrders(5);
            $location_stats = $this->locationModel->getAllLocationsWithStats();
            
            $data = [
                'title' => 'Dashboard Admin - CaS',
                'stats' => $stats,
                'recent_orders' => $recent_orders,
                'location_stats' => $location_stats,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Error in admin dashboard: " . $e->getMessage());
            $this->view('admin/dashboard', [
                'title' => 'Dashboard Admin - CaS',
                'error' => 'Eroare la încărcarea datelor dashboard'
            ]);
        }
    }
    
    /**
     * Manage locations
     */
    public function manageLocations() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLocationAction();
            return;
        }
        
        try {
            $locations = $this->locationModel->getAllLocationsWithStats();
            
            $data = [
                'title' => 'Management Locații - CaS',
                'locations' => $locations,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/manage_locations', $data);
            
        } catch (Exception $e) {
            error_log("Error in manage locations: " . $e->getMessage());
            $this->view('admin/manage_locations', [
                'title' => 'Management Locații - CaS',
                'error' => 'Eroare la încărcarea locațiilor'
            ]);
        }
    }
    
    /**
     * Handle location CRUD operations
     */
    private function handleLocationAction() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $action = sanitize_input($_POST['action'] ?? '');
        
        try {
            switch ($action) {
                case 'add':
                    $result = $this->addLocation();
                    break;
                case 'edit':
                    $result = $this->editLocation();
                    break;
                case 'delete':
                    $result = $this->deleteLocation();
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Acțiune necunoscută'];
            }
            
            if ($this->isAjax()) {
                $this->json($result);
            } else {
                $this->redirect('?controller=admin&action=manageLocations');
            }
            
        } catch (Exception $e) {
            error_log("Location action error: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->json(['error' => 'Eroare la procesarea acțiunii'], 500);
            } else {
                $this->redirect('?controller=admin&action=manageLocations&error=1');
            }
        }
    }
    
    /**
     * Add new location
     */
    private function addLocation() {
        $name = sanitize_input($_POST['name'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $latitude = sanitize_numeric($_POST['latitude'] ?? null);
        $longitude = sanitize_numeric($_POST['longitude'] ?? null);
        
        if (empty($name) || empty($address)) {
            return ['success' => false, 'message' => 'Numele și adresa sunt obligatorii'];
        }
        
        $locationId = $this->locationModel->createLocation($name, $address, $latitude, $longitude);
        
        return [
            'success' => true,
            'message' => 'Locația a fost adăugată cu succes',
            'location_id' => $locationId
        ];
    }
    
    /**
     * Edit location
     */
    private function editLocation() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        $name = sanitize_input($_POST['name'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $latitude = sanitize_numeric($_POST['latitude'] ?? null);
        $longitude = sanitize_numeric($_POST['longitude'] ?? null);
        $status = sanitize_input($_POST['status'] ?? 'activ');
        
        if (!$id || empty($name) || empty($address)) {
            return ['success' => false, 'message' => 'Date invalide'];
        }
        
        $success = $this->locationModel->updateLocation($id, $name, $address, $latitude, $longitude, $status);
        
        return [
            'success' => $success,
            'message' => $success ? 'Locația a fost actualizată' : 'Eroare la actualizare'
        ];
    }
    
    /**
     * Delete location
     */
    private function deleteLocation() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        
        if (!$id) {
            return ['success' => false, 'message' => 'ID invalid'];
        }
        
        $success = $this->locationModel->deleteLocation($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Locația a fost ștearsă' : 'Eroare la ștergere'
        ];
    }
    
    /**
     * Manage users
     */
    public function manageUsers() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUserAction();
            return;
        }
        
        try {
            $users = $this->userModel->getAllUsers();
            
            $data = [
                'title' => 'Management Utilizatori - CaS',
                'users' => $users,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/manage_users', $data);
            
        } catch (Exception $e) {
            error_log("Error in manage users: " . $e->getMessage());
            $this->view('admin/manage_users', [
                'title' => 'Management Utilizatori - CaS',
                'error' => 'Eroare la încărcarea utilizatorilor'
            ]);
        }
    }
    
    /**
     * Handle user CRUD operations
     */
    private function handleUserAction() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $action = sanitize_input($_POST['action'] ?? '');
        
        try {
            switch ($action) {
                case 'add':
                    $result = $this->addUser();
                    break;
                case 'edit':
                    $result = $this->editUser();
                    break;
                case 'delete':
                    $result = $this->deleteUser();
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Acțiune necunoscută'];
            }
            
            if ($this->isAjax()) {
                $this->json($result);
            } else {
                $this->redirect('?controller=admin&action=manageUsers');
            }
            
        } catch (Exception $e) {
            error_log("User action error: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->json(['error' => 'Eroare la procesarea acțiunii'], 500);
            } else {
                $this->redirect('?controller=admin&action=manageUsers&error=1');
            }
        }
    }
    
    /**
     * Add new user
     */
    private function addUser() {
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Toate câmpurile sunt obligatorii'];
        }
        
        if (!validate_email($email)) {
            return ['success' => false, 'message' => 'Email invalid'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Parola trebuie să aibă cel puțin 6 caractere'];
        }
        
        if ($this->userModel->userExists($username, $email)) {
            return ['success' => false, 'message' => 'Utilizatorul sau email-ul există deja'];
        }
        
        $userId = $this->userModel->createUser($username, $email, $password);
        
        return [
            'success' => true,
            'message' => 'Utilizatorul a fost creat cu succes',
            'user_id' => $userId
        ];
    }
    
    /**
     * Edit user
     */
    private function editUser() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!$id || empty($username) || empty($email)) {
            return ['success' => false, 'message' => 'Date invalide'];
        }
        
        if (!validate_email($email)) {
            return ['success' => false, 'message' => 'Email invalid'];
        }
        
        $success = $this->userModel->updateUser($id, $username, $email, $password);
        
        return [
            'success' => $success,
            'message' => $success ? 'Utilizatorul a fost actualizat' : 'Eroare la actualizare'
        ];
    }
    
    /**
     * Delete user
     */
    private function deleteUser() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        
        if (!$id) {
            return ['success' => false, 'message' => 'ID invalid'];
        }
        
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Nu vă puteți șterge propriul cont'];
        }
        
        $success = $this->userModel->deleteUser($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Utilizatorul a fost șters' : 'Eroare la ștergere'
        ];
    }
    
    /**
     * View reports
     */
    public function viewReports() {
        try {
            $type = sanitize_input($_GET['type'] ?? 'overview');
            
            $data = [
                'title' => 'Rapoarte - CaS',
                'report_type' => $type,
                'csrf_token' => generate_csrf_token()
            ];
            
            switch ($type) {
                case 'orders':
                    $data['orders_report'] = $this->reportModel->getOrdersReport();
                    break;
                case 'locations':
                    $data['locations_report'] = $this->reportModel->getLocationsReport();
                    break;
                case 'resources':
                    $data['resources_report'] = $this->reportModel->getResourcesReport();
                    break;
                default:
                    $data['overview_report'] = $this->reportModel->getOverviewReport();
            }
            
            $this->view('admin/view_reports', $data);
            
        } catch (Exception $e) {
            error_log("Error in view reports: " . $e->getMessage());
            $this->view('admin/view_reports', [
                'title' => 'Rapoarte - CaS',
                'error' => 'Eroare la încărcarea rapoartelor'
            ]);
        }
    }
    
    /**
     * Export data to CSV/JSON
     */
    public function exportData() {
        if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $type = sanitize_input($_GET['type'] ?? '');
        $format = sanitize_input($_GET['format'] ?? 'csv');
        
        try {
            switch ($type) {
                case 'orders':
                    $data = $this->orderModel->getAllOrdersForExport();
                    $filename = 'comenzi_' . date('Y-m-d');
                    break;
                case 'locations':
                    $data = $this->locationModel->getAllLocationsForExport();
                    $filename = 'locatii_' . date('Y-m-d');
                    break;
                case 'users':
                    $data = $this->userModel->getAllUsersForExport();
                    $filename = 'utilizatori_' . date('Y-m-d');
                    break;
                default:
                    $this->json(['error' => 'Tip de export necunoscut'], 400);
                    return;
            }
            
            if ($format === 'json') {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $filename . '.json"');
                echo json_encode($data, JSON_PRETTY_PRINT);
            } else {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
                
                $output = fopen('php://output', 'w');
                if (!empty($data)) {
                    fputcsv($output, array_keys($data[0]));
                    foreach ($data as $row) {
                        fputcsv($output, $row);
                    }
                }
                fclose($output);
            }
            
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            $this->json(['error' => 'Eroare la export'], 500);
        }
    }
}
