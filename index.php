<?php
/**
 * CaS - Sistema Web pentru Managementul Spalatoriilor
 * Entry Point Principal cu Routing Simplificat
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constante aplicatie
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/CaS_FII-Clean/');

// Autoloader simplu
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

// Routing simplu
$controller = sanitize_input($_GET['controller'] ?? 'public');
$action = sanitize_input($_GET['action'] ?? 'home');

// Validare controller
$validControllers = ['public', 'auth', 'client', 'admin'];
if (!in_array($controller, $validControllers)) {
    $controller = 'public';
}

// RSS handling
if (isset($_GET['rss'])) {
    require_once 'app/utils/RSSGenerator.php';
    $rssType = sanitize_input($_GET['rss']);
    RSSGenerator::generate($rssType);
    exit;
}

try {
    // Instantiere controller
    $controllerClass = ucfirst($controller) . 'Controller';
    $controllerInstance = new $controllerClass();
    
    // Executare actiune
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        $controllerInstance->home();
    }
    
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    include 'app/views/layouts/header.php';
    echo '<div class="alert alert-error">Eroare: ' . htmlspecialchars($e->getMessage()) . '</div>';
    include 'app/views/layouts/footer.php';
}
?>
