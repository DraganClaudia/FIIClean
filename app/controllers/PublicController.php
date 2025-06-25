<?php
/**
 * PublicController - pagini publice si API endpoints
 */
require_once 'app/models/SediuModel.php';

class PublicController extends Controller {
    private $sediuModel;
    
    public function __construct() {
        parent::__construct();
        $this->sediuModel = new SediuModel();
    }
    
    /**
     * Pagina principala
     */
    public function home() {
        try {
            $sedii = $this->sediuModel->getAllSedii();
            
            $data = [
                'title' => 'CaS - Sistem Spalatorii',
                'sedii' => $sedii,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('public/home', $data);
            
        } catch (Exception $e) {
            error_log("Error in home: " . $e->getMessage());
            $this->view('public/home', [
                'title' => 'CaS - Sistem Spalatorii',
                'error' => 'Eroare la incarcarea datelor'
            ]);
        }
    }
    
    /**
     * Detalii sediu via AJAX
     */
    public function getSediuDetails() {
        if (!$this->isAjax()) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $sediuId = sanitize_numeric($_GET['id'] ?? null);
        
        if (!$sediuId) {
            $this->json(['error' => 'ID sediu invalid'], 400);
            return;
        }
        
        try {
            $sediu = $this->sediuModel->getSediuById($sediuId);
            $stats = $this->sediuModel->getSediuStats($sediuId);
            
            if (!$sediu) {
                $this->json(['error' => 'Sediul nu a fost gasit'], 404);
                return;
            }
            
            $this->json([
                'sediu' => $sediu,
                'stats' => $stats,
                'operational_status' => $sediu['Stare'] === 'activ' ? 'operational' : 'inactive'
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting sediu details: " . $e->getMessage());
            $this->json(['error' => 'Eroare la obtinerea detaliilor'], 500);
        }
    }
    
    /**
     * Pagina contact
     */
    public function contact() {
        $error = null;
        $success = null;
        $formData = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processContactForm();
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['error'];
                $formData = $result['form_data'] ?? [];
            }
        }
        
        $data = [
            'title' => 'Contact - CaS',
            'error' => $error,
            'success' => $success,
            'form_data' => $formData,
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('public/contact', $data);
    }
    
    /**
     * Proceseaza formularul de contact
     */
    private function processContactForm() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');
        
        $formData = compact('name', 'email', 'phone', 'subject', 'message');
        
        // Validari
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            return [
                'success' => false, 
                'error' => 'Campurile marcate cu * sunt obligatorii',
                'form_data' => $formData
            ];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false, 
                'error' => 'Adresa de email nu este valida',
                'form_data' => $formData
            ];
        }
        
        // Log mesajul (in loc de trimitere email pentru simplitate)
        error_log("Contact form submission: $name ($email) - $subject");
        
        return [
            'success' => true, 
            'message' => 'Mesajul a fost trimis cu succes!'
        ];
    }
}
