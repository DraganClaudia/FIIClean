<?php
/**
 * PublicController - handles public-facing pages
 * Implements MVC pattern and AJAX responses
 */
require_once 'app/models/LocationModel.php';

class PublicController extends Controller {
    private $locationModel;
    
    public function __construct() {
        parent::__construct();
        $this->locationModel = new LocationModel();
    }
    
    /**
     * Home page - displays main interface
     */
    public function home() {
        try {
            // Get sample locations for display
            $locations = $this->locationModel->getAllLocations();
            
            $data = [
                'title' => 'CaS - Cleaning Web Simulator',
                'locations' => $locations,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('public/home', $data);
            
        } catch (Exception $e) {
            error_log("Error in home action: " . $e->getMessage());
            $this->view('public/home', ['title' => 'CaS - Cleaning Web Simulator', 'error' => 'Eroare la încărcarea datelor']);
        }
    }
    
    /**
     * Get location details via AJAX
     */
    public function getLocationDetails() {
        if (!$this->isAjax()) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $locationId = sanitize_numeric($_GET['id'] ?? null);
        
        if (!$locationId) {
            $this->json(['error' => 'ID locație invalid'], 400);
            return;
        }
        
        try {
            $location = $this->locationModel->getLocationById($locationId);
            $stats = $this->locationModel->getLocationStats($locationId);
            $operational = $this->locationModel->isLocationOperational($locationId);
            $resources = $this->locationModel->getLocationResources($locationId);
            $orders = $this->locationModel->getLocationOrders($locationId, 5);
            
            if (!$location) {
                $this->json(['error' => 'Locația nu a fost găsită'], 404);
                return;
            }
            
            $response = [
                'location' => $location,
                'stats' => $stats,
                'operational_status' => $operational['operational_status'] ?? 'unknown',
                'resources' => $resources,
                'recent_orders' => $orders
            ];
            
            $this->json($response);
            
        } catch (Exception $e) {
            error_log("Error getting location details: " . $e->getMessage());
            $this->json(['error' => 'Eroare la obținerea detaliilor locației'], 500);
        }
    }
    
    /**
     * Contact page
     */
    public function contact() {
        $data = [
            'title' => 'Contact - CaS',
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('public/contact', $data);
    }
}