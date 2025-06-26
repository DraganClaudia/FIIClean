<?php
/**
 * Entry Point Principal cu Routing Complet pentru API si RSS
 */

// Pornire output buffering pentru a preveni erorile de header => Mi-a dat cu eroare la un moment dat
ob_start();

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constante aplicatie
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/fiiclean/');

// Autoloader simplificat
spl_autoload_register(function ($class) {
    $paths = ['core/', 'models/', 'controllers/', 'utils/'];
    foreach ($paths as $path) {
        $file = BASE_PATH . '/app/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Include utilitati de baza
require_once 'app/utils/security.php';
require_once 'app/utils/helpers.php';

/**
 * Functie pentru gestionarea cererilor API
 * Implementeaza arhitectura bazata pe servicii Web
 */
function handleApiRequest($action) {
    // Curata output buffer-ul pentru API
    if (ob_get_level()) {
        ob_clean();
    }
    
    try {
        require_once 'app/controllers/APIController.php';
        $apiController = new APIController();
        
        // Maparea actiunilor API la metodele controllerului
        $apiActions = [
            // Sedii endpoints
            'getSedii' => 'getSedii',
            'getSediu' => 'getSediu',
            
            // Comenzi endpoints  
            'getComenzi' => 'getComenzi',
            'createComanda' => 'createComanda',
            'updateComanda' => 'updateComanda',
            'deleteComanda' => 'deleteComanda',
            
            // Resurse endpoints
            'getResurse' => 'getResurse',
            'updateResursa' => 'updateResursa',
            
            // Statistici si cautare
            'getStatistici' => 'getStatistici',
            'cautare' => 'cautare',
            
            // Utilitare
            'health' => 'healthCheck',
            'export' => 'exportData'
        ];
        
        if (isset($apiActions[$action]) && method_exists($apiController, $apiActions[$action])) {
            $method = $apiActions[$action];
            $apiController->$method();
        } else {
            $apiController->notFound();
        }
        
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Eroare interna API',
            'code' => 'API_INTERNAL_ERROR',
            'timestamp' => date('c')
        ]);
    }
    
    // Trimite output-ul si opreste executia
    if (ob_get_level()) {
        ob_end_flush();
    }
    exit;
}

/**
 * Verifica daca cererea este AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Routing pentru aplicatie
$controller = sanitize_input($_GET['controller'] ?? 'public');
$action = sanitize_input($_GET['action'] ?? 'home');

// RSS Feed handling - conform cerintelor RSS
if (isset($_GET['rss'])) {
    // Curata output buffer-ul pentru RSS
    if (ob_get_level()) {
        ob_clean();
    }
    
    require_once 'app/utils/RSSFeedGenerator.php';
    $rssType = sanitize_input($_GET['rss']);
    
    // Validare tipuri RSS permise
    $validRssTypes = ['sedii', 'statistici', 'resurse', 'general'];
    if (!in_array($rssType, $validRssTypes)) {
        $rssType = 'general';
    }
    
    RSSGenerator::generate($rssType);
    
    // Trimite output-ul si opreste executia
    if (ob_get_level()) {
        ob_end_flush();
    }
    exit;
}

// API Routing - conform cerintelor servicii Web
if ($controller === 'api') {
    handleApiRequest($action);
    // Nu ajunge aici, handleApiRequest face exit
}

// Pentru cererile AJAX care nu sunt API, seteaza header-ul JSON
if (isAjaxRequest() && $controller !== 'api') {
    // Curata output buffer pentru AJAX
    if (ob_get_level()) {
        ob_clean();
    }
    header('Content-Type: application/json');
}

// Validare controllere standard
$validControllers = ['public', 'auth', 'client', 'admin'];
if (!in_array($controller, $validControllers)) {
    $controller = 'public';
}

try {
    // Instantiere controller
    $controllerClass = ucfirst($controller) . 'Controller';
    
    if (!class_exists($controllerClass)) {
        throw new Exception("Controller $controllerClass nu exista");
    }
    
    $controllerInstance = new $controllerClass();
    
    // Executare actiune
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        // Fallback la actiunea home
        if (method_exists($controllerInstance, 'home')) {
            $controllerInstance->home();
        } else {
            throw new Exception("Actiunea $action nu exista pentru $controllerClass");
        }
    }
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    
    // Pentru cererile AJAX, returneaza JSON
    if (isAjaxRequest()) {
        // Curata output buffer-ul
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Eroare aplicatie: ' . $e->getMessage(),
            'code' => 'APPLICATION_ERROR'
        ]);
        
        if (ob_get_level()) {
            ob_end_flush();
        }
        exit;
    }
    
    // Pentru cererile normale, afiseaza pagina de eroare
    include 'app/views/layouts/header.php';
    echo '<div class="container">';
    echo '<div class="alert alert-error">Eroare aplicatie: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<p><a href="' . BASE_URL . '">Inapoi la pagina principala</a></p>';
    echo '</div>';
    include 'app/views/layouts/footer.php';
}

// Trimite output-ul final
if (ob_get_level()) {
    ob_end_flush();
}
?>
