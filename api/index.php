<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config/Database.php';
$db = new Database();
$db->connect();
$db->initTables();

$resource = $_GET['resource'] ?? 'locations';

switch($resource) {
    case 'locations':
        require_once __DIR__ . '/controllers/LocationController.php';
        $controller = new LocationController();
        $controller->handleRequest();
        break;
    case 'orders':
        require_once __DIR__ . '/controllers/OrderController.php';
        $controller = new OrderController();
        $controller->handleRequest();
        break;
    case 'resources':
        require_once 'controllers/ResourceController.php';
        $controller = new ResourceController();
        $controller->handleRequest();
        break;
    case 'export':
        require_once __DIR__ . '/controllers/ExportController.php';
        $controller = new ExportController();
        $controller->handleRequest();
        break;
    case 'rss':
        require_once __DIR__ . '/controllers/RSSController.php';
        $controller = new RSSController();
        $controller->handleRequest();
        break;
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
        
    case 'users':
        require_once __DIR__ . '/users.php';
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Resource not found']);
}
?>
