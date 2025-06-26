<?php
/**
 * APIController - servicii Web REST pentru sistemul de spalatorii
 */
require_once 'app/models/SediuModel.php';
require_once 'app/models/ComandaModel.php';
require_once 'app/models/ClientModel.php';
require_once 'app/models/ResourceModel.php';

class APIController extends Controller {
    private $sediuModel;
    private $comandaModel;
    private $clientModel;
    private $resourceModel;
    
    public function __construct() {
        parent::__construct();
        
        // Seteaza headerele pentru API REST doar daca nu au fost deja trimise
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        }
        
        // Gestioneaza cererea OPTIONS pentru CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (!headers_sent()) {
                http_response_code(200);
            }
            exit();
        }
        
        $this->sediuModel = new SediuModel();
        $this->comandaModel = new ComandaModel();
        $this->clientModel = new ClientModel();
        $this->resourceModel = new ResourceModel();
    }
    
    /**
     * API endpoint: GET /api/sedii
     * Returneaza lista sediilor cu filtrare pentru localizare geografica
     */
    public function getSedii() {
        try {
            $stare = sanitize_input($_GET['stare'] ?? null);
            $lat = sanitize_numeric($_GET['lat'] ?? null);
            $lng = sanitize_numeric($_GET['lng'] ?? null);
            
            if ($stare) {
                $sedii = $this->sediuModel->getSediiByStare($stare);
            } else {
                $sedii = $this->sediuModel->getAllSedii();
            }
            
            // Adauga statistici pentru monitorizare in timp real
            foreach ($sedii as &$sediu) {
                $sediu['stats'] = $this->sediuModel->getSediuStats($sediu['id']);
                $sediu['servicii_disponibile'] = ['covor', 'auto', 'textil'];
            }
            
            $this->respondJSON([
                'success' => true,
                'data' => $sedii,
                'count' => count($sedii),
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getSedii: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la obtinerea sediilor',
                'code' => 'SEDII_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/sedii/{id}
     * Returneaza detalii despre un sediu specific pentru monitorizare
     */
    public function getSediu() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            
            if (!$id) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'ID sediu necesar',
                    'code' => 'MISSING_ID'
                ], 400);
                return;
            }
            
            $sediu = $this->sediuModel->getSediuById($id);
            
            if (!$sediu) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Sediul nu a fost gasit',
                    'code' => 'SEDIU_NOT_FOUND'
                ], 404);
                return;
            }
            
            // Obtine date pentru monitorizare timp real
            $stats = $this->sediuModel->getSediuStats($id);
            $comenzi_recente = $this->comandaModel->getComenziForSediu($id, 5);
            
            $this->respondJSON([
                'success' => true,
                'data' => [
                    'sediu' => $sediu,
                    'statistici' => $stats,
                    'comenzi_recente' => $comenzi_recente,
                    'operational_status' => $sediu['Stare'] === 'activ'
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getSediu: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la obtinerea detaliilor sediului',
                'code' => 'SEDIU_DETAILS_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/comenzi
     * Returneaza lista comenzilor cu filtrare pentru statistici
     */
    public function getComenzi() {
        try {
            $status = sanitize_input($_GET['status'] ?? null);
            $sediu_id = sanitize_numeric($_GET['sediu_id'] ?? null);
            $tip_serviciu = sanitize_input($_GET['tip_serviciu'] ?? null);
            $limit = sanitize_numeric($_GET['limit'] ?? 50, 1, 1000);
            $offset = sanitize_numeric($_GET['offset'] ?? 0, 0);
            
            $comenzi = $this->comandaModel->getAllComenzi();
            
            // Aplicare filtre
            if ($status) {
                $comenzi = array_filter($comenzi, function($c) use ($status) {
                    return $c['Status'] === $status;
                });
            }
            
            if ($sediu_id) {
                $comenzi = array_filter($comenzi, function($c) use ($sediu_id) {
                    return $c['idSediu'] == $sediu_id;
                });
            }
            
            if ($tip_serviciu) {
                $comenzi = array_filter($comenzi, function($c) use ($tip_serviciu) {
                    return $c['TipServiciu'] === $tip_serviciu;
                });
            }
            
            // Paginare
            $total = count($comenzi);
            $comenzi = array_slice($comenzi, $offset, $limit);
            
            $this->respondJSON([
                'success' => true,
                'data' => array_values($comenzi),
                'pagination' => [
                    'total' => $total,
                    'count' => count($comenzi),
                    'limit' => $limit,
                    'offset' => $offset
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getComenzi: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la obtinerea comenzilor',
                'code' => 'COMENZI_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: POST /api/comenzi
     * Creeaza o comanda noua cu planificare si transport
     */
    public function createComanda() {
        try {
            $input = $this->getJSONInput();
            
            if (!$input) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Date JSON invalide sau lipsa',
                    'code' => 'INVALID_JSON'
                ], 400);
                return;
            }
            
            // Validare campuri obligatorii
            $required_fields = ['client_id', 'sediu_id', 'tip_serviciu', 'data_programare'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $this->respondJSON([
                        'success' => false,
                        'error' => "Campul '$field' este obligatoriu",
                        'code' => 'MISSING_REQUIRED_FIELD'
                    ], 400);
                    return;
                }
            }
            
            // Sanitizare date
            $client_id = sanitize_numeric($input['client_id']);
            $sediu_id = sanitize_numeric($input['sediu_id']);
            $tip_serviciu = sanitize_input($input['tip_serviciu']);
            $data_programare = sanitize_input($input['data_programare']);
            $recurenta = isset($input['recurenta']) ? (bool)$input['recurenta'] : false;
            $transport = isset($input['transport']) ? (bool)$input['transport'] : false;
            
            // Validare tip serviciu (covoare, autoturisme, imbracaminte)
            $servicii_valide = ['covor', 'auto', 'textil'];
            if (!in_array($tip_serviciu, $servicii_valide)) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Tip serviciu invalid. Valori permise: ' . implode(', ', $servicii_valide),
                    'code' => 'INVALID_SERVICE_TYPE'
                ], 400);
                return;
            }
            
            // Validare data programare
            $data_obj = DateTime::createFromFormat('Y-m-d', $data_programare);
            if (!$data_obj || $data_obj->format('Y-m-d') !== $data_programare) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Data programare invalida. Format asteptat: YYYY-MM-DD',
                    'code' => 'INVALID_DATE_FORMAT'
                ], 400);
                return;
            }
            
            if ($data_obj <= new DateTime()) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Data programarii trebuie sa fie in viitor',
                    'code' => 'INVALID_DATE_PAST'
                ], 400);
                return;
            }
            
            // Verifica daca clientul exista
            $client = $this->clientModel->getClientById($client_id);
            if (!$client) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Clientul specificat nu exista',
                    'code' => 'CLIENT_NOT_FOUND'
                ], 400);
                return;
            }
            
            // Verifica daca sediul exista si este operational
            $sediu = $this->sediuModel->getSediuById($sediu_id);
            if (!$sediu) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Sediul specificat nu exista',
                    'code' => 'SEDIU_NOT_FOUND'
                ], 400);
                return;
            }
            
            if ($sediu['Stare'] !== 'activ') {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Sediul nu este disponibil pentru programari',
                    'code' => 'SEDIU_NOT_AVAILABLE'
                ], 400);
                return;
            }
            
            // Creeaza comanda
            $comanda_id = $this->comandaModel->createComanda(
                $client_id, $sediu_id, $tip_serviciu, $data_programare, $recurenta, $transport
            );
            
            if ($comanda_id) {
                $comanda_creata = $this->comandaModel->getComandaById($comanda_id);
                
                $this->respondJSON([
                    'success' => true,
                    'data' => [
                        'comanda_id' => $comanda_id,
                        'comanda' => $comanda_creata,
                        'message' => 'Comanda a fost creata cu succes'
                    ],
                    'timestamp' => date('c')
                ], 201);
            } else {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Eroare la crearea comenzii',
                    'code' => 'CREATE_FAILED'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - createComanda: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare interna la crearea comenzii',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: PUT /api/comenzi/{id}
     * Actualizeaza status comanda
     */
    public function updateComanda() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            $input = $this->getJSONInput();
            
            if (!$id) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'ID comanda necesar',
                    'code' => 'MISSING_ID'
                ], 400);
                return;
            }
            
            if (!$input || !isset($input['status'])) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Status nou necesar pentru actualizare',
                    'code' => 'MISSING_STATUS'
                ], 400);
                return;
            }
            
            // Verifica daca comanda exista
            $comanda = $this->comandaModel->getComandaById($id);
            if (!$comanda) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Comanda nu a fost gasita',
                    'code' => 'COMANDA_NOT_FOUND'
                ], 404);
                return;
            }
            
            $status_valid = ['noua', 'in curs', 'finalizata', 'anulata'];
            $new_status = sanitize_input($input['status']);
            
            if (!in_array($new_status, $status_valid)) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Status invalid. Valori permise: ' . implode(', ', $status_valid),
                    'code' => 'INVALID_STATUS'
                ], 400);
                return;
            }
            
            $success = $this->comandaModel->updateComandaStatus($id, $new_status);
            
            if ($success) {
                $comanda_actualizata = $this->comandaModel->getComandaById($id);
                
                $this->respondJSON([
                    'success' => true,
                    'data' => [
                        'comanda_id' => $id,
                        'comanda' => $comanda_actualizata,
                        'message' => 'Status comanda actualizat cu succes'
                    ],
                    'timestamp' => date('c')
                ]);
            } else {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Eroare la actualizarea statusului',
                    'code' => 'UPDATE_FAILED'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - updateComanda: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare interna la actualizarea comenzii',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: DELETE /api/comenzi/{id}
     * Sterge o comanda
     */
    public function deleteComanda() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            
            if (!$id) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'ID comanda necesar',
                    'code' => 'MISSING_ID'
                ], 400);
                return;
            }
            
            $comanda = $this->comandaModel->getComandaById($id);
            if (!$comanda) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Comanda nu a fost gasita',
                    'code' => 'COMANDA_NOT_FOUND'
                ], 404);
                return;
            }
            
            $success = $this->comandaModel->deleteComanda($id);
            
            if ($success) {
                $this->respondJSON([
                    'success' => true,
                    'message' => 'Comanda a fost stearsa cu succes',
                    'timestamp' => date('c')
                ]);
            } else {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Eroare la stergerea comenzii',
                    'code' => 'DELETE_FAILED'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - deleteComanda: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare interna la stergerea comenzii',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/resurse
     * Returneaza lista resurselor (detergenti, echipamente, apa)
     */
    public function getResurse() {
        try {
            $tip = sanitize_input($_GET['tip'] ?? null);
            $stoc_redus = isset($_GET['stoc_redus']) ? (bool)$_GET['stoc_redus'] : false;
            
            if ($stoc_redus) {
                $resurse = $this->resourceModel->getLowStockResources();
            } elseif ($tip) {
                $resurse = $this->resourceModel->getResourcesByType($tip);
            } else {
                $resurse = $this->resourceModel->getAllResources();
            }
            
            $this->respondJSON([
                'success' => true,
                'data' => $resurse,
                'count' => count($resurse),
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getResurse: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la obtinerea resurselor',
                'code' => 'RESURSE_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: PUT /api/resurse/{id}
     * Actualizeaza cantitatea unei resurse
     */
    public function updateResursa() {
        try {
            $id = sanitize_numeric($_GET['id'] ?? null);
            $input = $this->getJSONInput();
            
            if (!$id) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'ID resursa necesar',
                    'code' => 'MISSING_ID'
                ], 400);
                return;
            }
            
            if (!$input || !isset($input['cantitate'])) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Cantitate necesara',
                    'code' => 'MISSING_QUANTITY'
                ], 400);
                return;
            }
            
            $cantitate = sanitize_numeric($input['cantitate'], 0);
            if ($cantitate === false) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Cantitate invalida',
                    'code' => 'INVALID_QUANTITY'
                ], 400);
                return;
            }
            
            $success = $this->resourceModel->updateResourceQuantity($id, $cantitate);
            
            if ($success) {
                $resursa = $this->resourceModel->getResourceById($id);
                
                $this->respondJSON([
                    'success' => true,
                    'data' => [
                        'resursa_id' => $id,
                        'resursa' => $resursa,
                        'message' => 'Resursa actualizata cu succes'
                    ],
                    'timestamp' => date('c')
                ]);
            } else {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Eroare la actualizarea resursei',
                    'code' => 'UPDATE_FAILED'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("API Error - updateResursa: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare interna la actualizarea resursei',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/statistici
     * Returneaza statistici pentru monitorizare (zi, luna, an)
     */
    public function getStatistici() {
        try {
            $tip = sanitize_input($_GET['tip'] ?? 'general');
            
            $stats = [];
            
            switch ($tip) {
                case 'sedii':
                    $stats = [
                        'total_sedii' => count($this->sediuModel->getAllSedii()),
                        'sedii_active' => count($this->sediuModel->getSediiActive()),
                        'sedii_reparatii' => count($this->sediuModel->searchSedii('reparatii'))
                    ];
                    break;
                    
                case 'comenzi':
                    $statistici_generale = $this->comandaModel->getStatsGeneral();
                    $stats = [
                        'total_comenzi' => $statistici_generale['total_comenzi'] ?? 0,
                        'comenzi_noi' => $statistici_generale['comenzi_noi'] ?? 0,
                        'comenzi_in_curs' => $statistici_generale['comenzi_in_curs'] ?? 0,
                        'comenzi_finalizate' => $statistici_generale['comenzi_finalizate'] ?? 0,
                        'comenzi_astazi' => $statistici_generale['comenzi_astazi'] ?? 0
                    ];
                    break;
                    
                case 'resurse':
                    $stats = [
                        'total_resurse' => count($this->resourceModel->getAllResources()),
                        'resurse_stoc_redus' => count($this->resourceModel->getLowStockResources()),
                        'tipuri_resurse' => $this->resourceModel->getResourceTypes()
                    ];
                    break;
                    
                default:
                    $statistici_generale = $this->comandaModel->getStatsGeneral();
                    $stats = [
                        'total_sedii' => count($this->sediuModel->getAllSedii()),
                        'sedii_active' => count($this->sediuModel->getSediiActive()),
                        'total_comenzi' => $statistici_generale['total_comenzi'] ?? 0,
                        'comenzi_active' => ($statistici_generale['comenzi_noi'] ?? 0) + ($statistici_generale['comenzi_in_curs'] ?? 0),
                        'comenzi_astazi' => $statistici_generale['comenzi_astazi'] ?? 0,
                        'total_resurse' => count($this->resourceModel->getAllResources())
                    ];
            }
            
            $this->respondJSON([
                'success' => true,
                'data' => $stats,
                'tip' => $tip,
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - getStatistici: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la obtinerea statisticilor',
                'code' => 'STATS_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/cautare
     * Cautare in toate entitatile
     */
    public function cautare() {
        try {
            $query = sanitize_input($_GET['q'] ?? '');
            $tip = sanitize_input($_GET['tip'] ?? 'toate');
            $limit = sanitize_numeric($_GET['limit'] ?? 20, 1, 100);
            
            if (empty($query) || strlen($query) < 2) {
                $this->respondJSON([
                    'success' => false,
                    'error' => 'Interogarea trebuie sa aiba cel putin 2 caractere',
                    'code' => 'QUERY_TOO_SHORT'
                ], 400);
                return;
            }
            
            $rezultate = [];
            
            if ($tip === 'toate' || $tip === 'sedii') {
                $rezultate['sedii'] = $this->sediuModel->searchSedii($query);
                $rezultate['sedii'] = array_slice($rezultate['sedii'], 0, $limit);
            }
            
            if ($tip === 'toate' || $tip === 'comenzi') {
                $comenzi = $this->comandaModel->getAllComenzi();
                $rezultate['comenzi'] = array_filter($comenzi, function($c) use ($query) {
                    return stripos($c['nume_client'], $query) !== false ||
                           stripos($c['nume_sediu'], $query) !== false ||
                           stripos($c['TipServiciu'], $query) !== false;
                });
                $rezultate['comenzi'] = array_slice(array_values($rezultate['comenzi']), 0, $limit);
            }
            
            if ($tip === 'toate' || $tip === 'resurse') {
                $rezultate['resurse'] = $this->resourceModel->searchResources($query, $limit);
            }
            
            $total_rezultate = array_sum(array_map('count', $rezultate));
            
            $this->respondJSON([
                'success' => true,
                'data' => $rezultate,
                'meta' => [
                    'query' => $query,
                    'tip' => $tip,
                    'total_rezultate' => $total_rezultate,
                    'limit' => $limit
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("API Error - cautare: " . $e->getMessage());
            $this->respondJSON([
                'success' => false,
                'error' => 'Eroare la cautare',
                'code' => 'SEARCH_ERROR'
            ], 500);
        }
    }
    
    /**
     * API endpoint: GET /api/health
     * Verifica starea sistemului
     */
    public function healthCheck() {
        try {
            $health_data = [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'version' => '1.0.0',
                'sistem' => 'CaS - Cleaning Web Simulator',
                'baza_date' => $this->checkDatabaseHealth(),
                'servicii' => [
                    'sedii' => $this->checkSediuService(),
                    'comenzi' => $this->checkComandaService(),
                    'resurse' => $this->checkResourceService()
                ]
            ];
            
            // Determina statusul general
            $toate_ok = $health_data['baza_date']['status'] === 'ok';
            foreach ($health_data['servicii'] as $serviciu) {
                if ($serviciu['status'] !== 'ok') {
                    $toate_ok = false;
                    break;
                }
            }
            
            if (!$toate_ok) {
                $health_data['status'] = 'degraded';
            }
            
            $status_code = $toate_ok ? 200 : 503;
            
            $this->respondJSON($health_data, $status_code);
            
        } catch (Exception $e) {
            error_log("API Error - healthCheck: " . $e->getMessage());
            $this->respondJSON([
                'status' => 'unhealthy',
                'error' => 'Eroare critica la verificarea starii sistemului',
                'timestamp' => date('c')
            ], 503);
        }
    }
    
    /**
     * Export data in CSV/JSON format
     */
    public function exportData() {
        try {
            $type = sanitize_input($_GET['type'] ?? '');
            $format = sanitize_input($_GET['format'] ?? 'csv');
            
            if (!verify_csrf_token($_GET['csrf_token'] ?? '')) {
                $this->respondJSON(['error' => 'Token CSRF invalid'], 400);
                return;
            }
            
            $data = [];
            $filename = '';
            
            switch ($type) {
                case 'comenzi':
                    $data = $this->comandaModel->getAllComenzi();
                    $filename = 'comenzi_' . date('Y-m-d');
                    break;
                case 'sedii':
                    $data = $this->sediuModel->getAllSedii();
                    $filename = 'sedii_' . date('Y-m-d');
                    break;
                case 'resurse':
                    $data = $this->resourceModel->getAllResources();
                    $filename = 'resurse_' . date('Y-m-d');
                    break;
                default:
                    $this->respondJSON(['error' => 'Tip de export necunoscut'], 400);
                    return;
            }
            
            if ($format === 'json') {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $filename . '.json"');
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
            $this->respondJSON(['error' => 'Eroare la export'], 500);
        }
    }
    
    /**
     * Endpoint pentru cereri nerecunoscute
     */
    public function notFound() {
        $this->respondJSON([
            'success' => false,
            'error' => 'Endpoint API nu a fost gasit',
            'code' => 'ENDPOINT_NOT_FOUND',
            'endpointuri_disponibile' => [
                'GET /api/sedii - Lista sedii',
                'GET /api/sedii/{id} - Detalii sediu',
                'GET /api/comenzi - Lista comenzi',
                'POST /api/comenzi - Creeaza comanda',
                'PUT /api/comenzi/{id} - Actualizeaza comanda',
                'DELETE /api/comenzi/{id} - Sterge comanda',
                'GET /api/resurse - Lista resurse',
                'PUT /api/resurse/{id} - Actualizeaza resursa',
                'GET /api/statistici - Statistici generale',
                'GET /api/cautare - Cautare globala',
                'GET /api/health - Status sistem'
            ]
        ], 404);
    }
    
    /**
     * Functii helper
     */
    
    /**
     * Trimite raspuns JSON
     */
    private function respondJSON($data, $status_code = 200) {
        // Seteaza header-ele doar daca nu au fost deja trimise
        if (!headers_sent()) {
            http_response_code($status_code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Obtine input JSON din request
     */
    private function getJSONInput() {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return null;
        }
        
        $decoded = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Verifica starea bazei de date
     */
    private function checkDatabaseHealth() {
        try {
            $this->db->query("SELECT 1");
            return ['status' => 'ok', 'message' => 'Conexiune baza de date functionala'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Eroare conexiune baza de date'];
        }
    }
    
    /**
     * Verifica serviciul sedii
     */
    private function checkSediuService() {
        try {
            $this->sediuModel->getAllSedii();
            return ['status' => 'ok', 'message' => 'Serviciu sedii functional'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Eroare serviciu sedii'];
        }
    }
    
    /**
     * Verifica serviciul comenzi
     */
    private function checkComandaService() {
        try {
            $this->comandaModel->getStatsGeneral();
            return ['status' => 'ok', 'message' => 'Serviciu comenzi functional'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Eroare serviciu comenzi'];
        }
    }
    
    /**
     * Verifica serviciul resurse
     */
    private function checkResourceService() {
        try {
            $this->resourceModel->getAllResources();
            return ['status' => 'ok', 'message' => 'Serviciu resurse functional'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Eroare serviciu resurse'];
        }
    }
}
?>
