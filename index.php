<?php
/**
 * CaS (Cleaning and Services) - Sistema Web pentru Managementul Spalatoriilor
 * Entry Point Principal - Routing și Inițializare
 * 
 * @version 1.0
 * @author Echipa CaS
 * @license MIT
 */

// Pornește sesiunea pentru managementul utilizatorilor
session_start();

// Definește constantele aplicației
define('APP_ROOT', __DIR__);
define('BASE_URL', 'http://localhost/CaS_FII-Clean/');
define('DEBUG', true);

// Error reporting pentru development
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Handler pentru fișiere statice (CSS, JS, imagini)
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Verifică dacă cererea este pentru un fișier static
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $requestPath)) {
    $filePath = __DIR__ . $requestPath;
    if (file_exists($filePath)) {
        // Setează content-type corespunzător
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($filePath);
        exit;
    }
}

// Autoloader simplu pentru clase
spl_autoload_register(function ($className) {
    $paths = [
        'app/core/',
        'app/models/',
        'app/controllers/',
        'app/utils/'
    ];
    
    foreach ($paths as $path) {
        $file = APP_ROOT . '/' . $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Include fișierele de utilități
require_once 'app/utils/security.php';
require_once 'app/utils/helpers.php';

// Configurare CSP pentru securitate
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com");

try {
    // Routing simplu bazat pe parametrii URL
    $controller = sanitize_input($_GET['controller'] ?? 'public');
    $action = sanitize_input($_GET['action'] ?? 'home');
    
    // Validare controller și action pentru securitate
    $allowedControllers = ['public', 'auth', 'client', 'admin', 'api'];
    if (!in_array($controller, $allowedControllers)) {
        $controller = 'public';
        $action = 'home';
    }
    
    // Verificare pentru RSS feed
    if (isset($_GET['rss'])) {
        require_once 'app/utils/RSSFeedGenerator.php';
        $feedType = sanitize_input($_GET['rss']);
        RSSFeedGenerator::handleRSSRequest($feedType);
        exit;
    }
    
    // Inițializare controller bazat pe tip
    switch ($controller) {
        case 'public':
            $controllerInstance = new PublicController();
            break;
            
        case 'auth':
            $controllerInstance = new AuthController();
            break;
            
        case 'client':
            // Verifică autentificarea pentru client
            if (!isset($_SESSION['user_id'])) {
                header('Location: ?controller=auth&action=login');
                exit;
            }
            $controllerInstance = new ClientController();
            break;
            
        case 'admin':
            // Verifică privilegiile de administrator
            if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
                header('Location: ?controller=auth&action=login');
                exit;
            }
            $controllerInstance = new AdminController();
            break;
            
        case 'api':
            // API pentru serviciile web
            $controllerInstance = new APIController();
            break;
            
        default:
            $controllerInstance = new PublicController();
            $action = 'home';
    }
    
    // Verifică dacă metoda există
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        // Fallback la pagina principală
        if ($controller === 'public') {
            $controllerInstance->home();
        } else {
            header('Location: ?controller=public&action=home');
            exit;
        }
    }
    
} catch (PDOException $e) {
    // Gestionare erori bază de date
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    
    if (DEBUG) {
        include 'app/views/layouts/header.php';
        echo '<div class="alert alert-error">Eroare bază de date: ' . htmlspecialchars($e->getMessage()) . '</div>';
        include 'app/views/layouts/footer.php';
    } else {
        include 'app/views/error/500.php';
    }
    
} catch (Exception $e) {
    // Gestionare erori generale
    error_log("Application Error: " . $e->getMessage());
    http_response_code(500);
    
    if (DEBUG) {
        include 'app/views/layouts/header.php';
        echo '<div class="alert alert-error">Eroare aplicație: ' . htmlspecialchars($e->getMessage()) . '</div>';
        include 'app/views/layouts/footer.php';
    } else {
        include 'app/views/error/500.php';
    }
}
?>