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
