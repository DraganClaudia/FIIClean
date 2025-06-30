<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';
$db = new Database();
$db->connect();
$db->initTables();

$resource = $_GET['resource'] ?? 'locations';

switch($resource) {
    case 'locations':
        require_once 'controllers/LocationController.php';
        $controller = new LocationController();
        $controller->handleRequest();
        break;
    case 'orders':
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->handleRequest();
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Resource not found']);
}
?>
