<?php
/**
 * ClientController - interfata pentru clienti
 */
require_once 'app/models/ComandaModel.php';
require_once 'app/models/ClientModel.php';
require_once 'app/models/SediuModel.php';

class ClientController extends Controller {
    private $comandaModel;
    private $clientModel;
    private $sediuModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->comandaModel = new ComandaModel();
        $this->clientModel = new ClientModel();
        $this->sediuModel = new SediuModel();
    }
    
    /**
     * Dashboard client
     */
    public function dashboard() {
        try {
            $clientId = $this->getOrCreateClientId();
            $comenziClient = $this->clientModel->getClientComenzi($clientId);
            $sediiActive = $this->sediuModel->getSediiActive();
            
            $stats = [
                'total_comenzi' => count($comenziClient),
                'comenzi_active' => count(array_filter($comenziClient, function($c) { 
                    return in_array($c['Status'], ['noua', 'in curs']); 
                })),
                'comenzi_finalizate' => count(array_filter($comenziClient, function($c) { 
                    return $c['Status'] === 'finalizata'; 
                }))
            ];
            
            $data = [
                'title' => 'Dashboard Client - CaS',
                'stats' => $stats,
                'comenzi_recente' => array_slice($comenziClient, 0, 5),
                'sedii_active' => $sediiActive,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Error in client dashboard: " . $e->getMessage());
            $this->view('client/dashboard', [
                'title' => 'Dashboard Client - CaS',
                'error' => 'Eroare la incarcarea datelor'
            ]);
        }
    }
    
    /**
     * Comenzile clientului
     */
    public function comenzile() {
        try {
            $clientId = $this->getOrCreateClientId();
            $comenzi = $this->clientModel->getClientComenzi($clientId);
            
            $data = [
                'title' => 'Comenzile Mele - CaS',
                'comenzi' => $comenzi,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/comenzile', $data);
            
        } catch (Exception $e) {
            error_log("Error in comenzile: " . $e->getMessage());
            $this->view('client/comenzile', [
                'title' => 'Comenzile Mele - CaS',
                'error' => 'Eroare la incarcarea comenzilor'
            ]);
        }
    }
    
    /**
     * Comanda noua
     */
    public function comandaNoua() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processCamandaNoua();
            
            if ($result['success']) {
                $this->redirect('?controller=client&action=comenzile&success=1');
            } else {
                $this->showComandaNoua($result['error']);
            }
            return;
        }
        
        $this->showComandaNoua();
    }
    
    /**
     * Afiseaza formularul pentru comanda noua
     */
    private function showComandaNoua($error = null) {
        try {
            $sediiActive = $this->sediuModel->getSediiActive();
            
            $data = [
                'title' => 'Comanda Noua - CaS',
                'sedii_active' => $sediiActive,
                'error' => $error,
                'csrf_token' => generate_csrf_token()
            ];
            
            $this->view('client/comanda_noua', $data);
            
        } catch (Exception $e) {
            error_log("Error showing comanda noua: " . $e->getMessage());
            $this->view('client/comanda_noua', [
                'title' => 'Comanda Noua - CaS',
                'error' => 'Eroare la incarcarea formularului'
            ]);
        }
    }
    
    /**
     * Proceseaza comanda noua
     */
    private function processCamandaNoua() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $sediuId = sanitize_numeric($_POST['sediu_id'] ?? null);
        $tipServiciu = sanitize_input($_POST['tip_serviciu'] ?? '');
        $dataProgramare = sanitize_input($_POST['data_programare'] ?? '');
        $transport = isset($_POST['transport']) ? 1 : 0;
        $recurenta = isset($_POST['recurenta']) ? 1 : 0;
        
        // Validari
        if (!$sediuId || empty($tipServiciu) || empty($dataProgramare)) {
            return ['success' => false, 'error' => 'Toate campurile obligatorii trebuie completate'];
        }
        
        $tipuriValide = ['covor', 'auto', 'textil'];
        if (!in_array($tipServiciu, $tipuriValide)) {
            return ['success' => false, 'error' => 'Tip serviciu invalid'];
        }
        
        $data = DateTime::createFromFormat('Y-m-d', $dataProgramare);
        if (!$data || $data->format('Y-m-d') !== $dataProgramare) {
            return ['success' => false, 'error' => 'Data nu este valida'];
        }
        
        if ($data <= new DateTime()) {
            return ['success' => false, 'error' => 'Data programarii trebuie sa fie in viitor'];
        }
        
        try {
            $clientId = $this->getOrCreateClientId();
            
            $comandaId = $this->comandaModel->createComanda(
                $clientId, $sediuId, $tipServiciu, $dataProgramare, $recurenta, $transport
            );
            
            return [
                'success' => true,
                'message' => 'Comanda a fost creata cu succes',
                'comanda_id' => $comandaId
            ];
            
        } catch (Exception $e) {
            error_log("Error creating comanda: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la crearea comenzii'];
        }
    }
    
    /**
     * Obtine sau creeaza ID-ul clientului pentru utilizatorul curent
     */
    private function getOrCreateClientId() {
        $userEmail = $_SESSION['email'] ?? '';
        $userName = $_SESSION['username'] ?? '';
        
        if (empty($userEmail)) {
            throw new Exception("Email utilizator necesar");
        }
        
        // Incearca sa gaseasca clientul dupa email
        $client = $this->clientModel->getClientByEmail($userEmail);
        
        if ($client) {
            return $client['id'];
        }
        
        // Creeaza client nou
        return $this->clientModel->createClient($userName, $userEmail);
    }
    
    /**
     * Anuleaza comanda
     */
    public function anuleazaComanda() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Metoda nepermisa'], 405);
            return;
        }
        
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF invalid'], 400);
            return;
        }
        
        $comandaId = sanitize_numeric($_POST['comanda_id'] ?? null);
        
        if (!$comandaId) {
            $this->json(['error' => 'ID comanda necesar'], 400);
            return;
        }
        
        try {
            $comanda = $this->comandaModel->getComandaById($comandaId);
            
            if (!$comanda) {
                $this->json(['error' => 'Comanda nu a fost gasita'], 404);
                return;
            }
            
            // Verifica daca comanda poate fi anulata
            if (!in_array($comanda['Status'], ['noua', 'in curs'])) {
                $this->json(['error' => 'Comanda nu poate fi anulata'], 400);
                return;
            }
            
            $success = $this->comandaModel->updateComandaStatus($comandaId, 'anulata');
            
            if ($success) {
                $this->json(['success' => true, 'message' => 'Comanda a fost anulata']);
            } else {
                $this->json(['error' => 'Eroare la anularea comenzii'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Error cancelling comanda: " . $e->getMessage());
            $this->json(['error' => 'Eroare la anularea comenzii'], 500);
        }
    }
}
