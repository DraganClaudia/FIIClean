<?php
/**
 * AdminController - administratie aplicatie
 */
require_once 'app/models/SediuModel.php';
require_once 'app/models/ComandaModel.php';
require_once 'app/models/ClientModel.php';
require_once 'app/models/UserModel.php';

class AdminController extends Controller {
    private $sediuModel;
    private $comandaModel;
    private $clientModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->sediuModel = new SediuModel();
        $this->comandaModel = new ComandaModel();
        $this->clientModel = new ClientModel();
        $this->userModel = new UserModel();
    }
    
    /**
     * Dashboard admin
     */
    public function dashboard() {
        try {
            $stats = $this->comandaModel->getStatsGeneral();
            $sediiStats = $this->sediuModel->getAllSedii();
            $comenziRecente = $this->comandaModel->getAllComenzi();
            $comenziRecente = array_slice($comenziRecente, 0, 5);
            
            $data = [
                'title' => 'Dashboard Admin - CaS',
                'stats' => $stats,
                'sedii_stats' => $sediiStats,
                'comenzi_recente' => $comenziRecente,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Error in admin dashboard: " . $e->getMessage());
            $this->view('admin/dashboard', [
                'title' => 'Dashboard Admin - CaS',
                'error' => 'Eroare la incarcarea datelor'
            ]);
        }
    }
    
    /**
     * Gestionare sedii
     */
    public function manageSedii() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSediuAction();
            return;
        }
        
        try {
            $sedii = $this->sediuModel->getAllSedii();
            
            $data = [
                'title' => 'Gestionare Sedii - CaS',
                'sedii' => $sedii,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/manage_sedii', $data);
            
        } catch (Exception $e) {
            error_log("Error in manage sedii: " . $e->getMessage());
            $this->view('admin/manage_sedii', [
                'title' => 'Gestionare Sedii - CaS',
                'error' => 'Eroare la incarcarea sediilor'
            ]);
        }
    }
    
    /**
     * Proceseaza actiuni pentru sedii
     */
    private function handleSediuAction() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $action = sanitize_input($_POST['action'] ?? '');
        
        try {
            switch ($action) {
                case 'add':
                    $result = $this->addSediu();
                    break;
                case 'edit':
                    $result = $this->editSediu();
                    break;
                case 'delete':
                    $result = $this->deleteSediu();
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Actiune necunoscuta'];
            }
            
            if ($this->isAjax()) {
                $this->json($result);
            } else {
                $this->redirect('?controller=admin&action=manageSedii');
            }
            
        } catch (Exception $e) {
            error_log("Sediu action error: " . $e->getMessage());
            if ($this->isAjax()) {
                $this->json(['error' => 'Eroare la procesarea actiunii'], 500);
            } else {
                $this->redirect('?controller=admin&action=manageSedii&error=1');
            }
        }
    }
    
    /**
     * Adauga sediu nou
     */
    private function addSediu() {
        $nume = sanitize_input($_POST['nume'] ?? '');
        $adresa = sanitize_input($_POST['adresa'] ?? '');
        $latitudine = sanitize_numeric($_POST['latitudine'] ?? null);
        $longitudine = sanitize_numeric($_POST['longitudine'] ?? null);
        
        if (empty($nume) || empty($adresa)) {
            return ['success' => false, 'message' => 'Numele si adresa sunt obligatorii'];
        }
        
        $sediuId = $this->sediuModel->addSediu($nume, $adresa, $latitudine, $longitudine);
        
        return [
            'success' => true,
            'message' => 'Sediul a fost adaugat cu succes',
            'sediu_id' => $sediuId
        ];
    }
    
    /**
     * Editeaza sediu
     */
    private function editSediu() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        $nume = sanitize_input($_POST['nume'] ?? '');
        $adresa = sanitize_input($_POST['adresa'] ?? '');
        $stare = sanitize_input($_POST['stare'] ?? 'activ');
        $latitudine = sanitize_numeric($_POST['latitudine'] ?? null);
        $longitudine = sanitize_numeric($_POST['longitudine'] ?? null);
        
        if (!$id || empty($nume) || empty($adresa)) {
            return ['success' => false, 'message' => 'Date invalide'];
        }
        
        $success = $this->sediuModel->updateSediu($id, $nume, $adresa, $stare, $latitudine, $longitudine);
        
        return [
            'success' => $success,
            'message' => $success ? 'Sediul a fost actualizat' : 'Eroare la actualizare'
        ];
    }
    
    /**
     * Sterge sediu
     */
    private function deleteSediu() {
        $id = sanitize_numeric($_POST['id'] ?? null);
        
        if (!$id) {
            return ['success' => false, 'message' => 'ID invalid'];
        }
        
        $success = $this->sediuModel->deleteSediu($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Sediul a fost sters' : 'Eroare la stergere'
        ];
    }
    
    /**
     * Gestionare comenzi
     */
    public function manageComenzi() {
        try {
            $comenzi = $this->comandaModel->getAllComenzi();
            
            $data = [
                'title' => 'Gestionare Comenzi - CaS',
                'comenzi' => $comenzi,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('admin/manage_comenzi', $data);
            
        } catch (Exception $e) {
            error_log("Error in manage comenzi: " . $e->getMessage());
            $this->view('admin/manage_comenzi', [
                'title' => 'Gestionare Comenzi - CaS',
                'error' => 'Eroare la incarcarea comenzilor'
            ]);
        }
    }
    
    /**
     * Export date CSV
     */
    public function exportData() {
        if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $type = sanitize_input($_GET['type'] ?? '');
        
        try {
            switch ($type) {
                case 'comenzi':
                    $data = $this->comandaModel->getAllComenzi();
                    $filename = 'comenzi_' . date('Y-m-d');
                    break;
                case 'sedii':
                    $data = $this->sediuModel->getAllSedii();
                    $filename = 'sedii_' . date('Y-m-d');
                    break;
                case 'clienti':
                    $data = $this->clientModel->getAllClienti();
                    $filename = 'clienti_' . date('Y-m-d');
                    break;
                default:
                    $this->json(['error' => 'Tip de export necunoscut'], 400);
                    return;
            }
            
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
            
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            $this->json(['error' => 'Eroare la export'], 500);
        }
    }
}
