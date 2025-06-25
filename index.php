<?php
/**
 * CaS - Sistema Web pentru Managementul Spalatoriilor
 * Entry Point Principal cu Routing Complet pentru API si RSS
 * Conform cerintelor: arhitectura bazata pe servicii Web
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constante aplicatie
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/CaS_FII-Clean/');

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

// Routing pentru aplicatie
$controller = sanitize_input($_GET['controller'] ?? 'public');
$action = sanitize_input($_GET['action'] ?? 'home');

// RSS Feed handling - conform cerintelor RSS
if (isset($_GET['rss'])) {
    require_once 'app/utils/RSSFeedGenerator.php';
    $rssType = sanitize_input($_GET['rss']);
    
    // Validare tipuri RSS permise
    $validRssTypes = ['sedii', 'statistici', 'resurse', 'general'];
    if (!in_array($rssType, $validRssTypes)) {
        $rssType = 'general';
    }
    
    RSSGenerator::generate($rssType);
    exit;
}

// API Routing - conform cerintelor servicii Web
if ($controller === 'api') {
    handleApiRequest($action);
    exit;
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
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Eroare aplicatie: ' . $e->getMessage(),
            'code' => 'APPLICATION_ERROR'
        ]);
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

/**
 * Functie pentru gestionarea cererilor API
 * Implementeaza arhitectura bazata pe servicii Web
 */
function handleApiRequest($action) {
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
}

/**
 * Verifica daca cererea este AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Functie pentru debugging (doar in development)
 */
function debugInfo() {
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">';
        echo '<h3>Debug Info:</h3>';
        echo '<p><strong>Controller:</strong> ' . ($_GET['controller'] ?? 'public') . '</p>';
        echo '<p><strong>Action:</strong> ' . ($_GET['action'] ?? 'home') . '</p>';
        echo '<p><strong>Request Method:</strong> ' . $_SERVER['REQUEST_METHOD'] . '</p>';
        echo '<p><strong>Is AJAX:</strong> ' . (isAjaxRequest() ? 'Yes' : 'No') . '</p>';
        echo '<p><strong>Session ID:</strong> ' . session_id() . '</p>';
        echo '<p><strong>User ID:</strong> ' . ($_SESSION['user_id'] ?? 'Not logged in') . '</p>';
        echo '</div>';
    }
}

// Apeleaza functia de debugging daca este necesara
debugInfo();

/**
 * Gestionare erori fatale
 */
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . " line " . $error['line']);
        
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Eroare fatala aplicatie',
                'code' => 'FATAL_ERROR'
            ]);
        } else {
            echo '<div style="background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border: 1px solid #ef5350; border-radius: 4px;">';
            echo '<h3>Eroare Fatala</h3>';
            echo '<p>A aparut o eroare fatala in aplicatie. Va rugam contactati administratorul.</p>';
            echo '<p><a href="' . BASE_URL . '">Inapoi la pagina principala</a></p>';
            echo '</div>';
        }
    }
});

/**
 * Security Headers pentru siguranta aplicatiei
 */
function setSecurityHeaders() {
    // Previne clickjacking
    header('X-Frame-Options: DENY');
    
    // Previne MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy basic
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com;");
}

// Aplica headerele de securitate
setSecurityHeaders();

/**
 * Cleanup pentru sesiune
 */
function cleanupSession() {
    // Curata mesajele flash dupa afisare
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    unset($_SESSION['info_message']);
    
    // Regenereaza ID-ul sesiunii periodic pentru securitate
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) { // 5 minute
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Cleanup sesiune la sfarsitul request-ului
register_shutdown_function('cleanupSession');

/**
 * Logging pentru monitorizare
 */
function logRequest() {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'is_ajax' => isAjaxRequest(),
        'controller' => $_GET['controller'] ?? 'public',
        'action' => $_GET['action'] ?? 'home'
    ];
    
    // Log doar pentru debugging - poate fi dezactivat in productie
    if (isset($_GET['log']) && $_GET['log'] === '1') {
        error_log("Request Log: " . json_encode($logData));
    }
}

// Log request-ul curent
logRequest();
?>
