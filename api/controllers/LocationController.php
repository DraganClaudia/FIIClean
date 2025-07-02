<?php
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../helpers/Security.php';

class ExportController {
    private $locationModel;
    private $orderModel;
    private $resourceModel;
    
    public function __construct() {
        $this->locationModel = new Location();
        $this->orderModel = new Order();
        $this->resourceModel = new Resource();
    }
    
    public function handleRequest() {
        $format = $_GET['format'] ?? 'json'; // json or csv
        $type = $_GET['type'] ?? 'all';
        
        switch($type) {
            case 'locations':
                $this->exportLocations($format);
                break;
            case 'orders':
                $this->exportOrders($format);
                break;
            case 'resources':
                $this->exportResources($format);
                break;
            case 'all':
                $this->exportAll($format);
                break;
            default:
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Invalid export type']);
        }
    }
    
    private function exportLocations($format) {
        $data = $this->locationModel->getAll();
        $this->outputData($data, $format, 'locations');
    }
    
    private function exportOrders($format) {
        $data = $this->orderModel->getAll();
        $this->outputData($data, $format, 'orders');
    }
    
    private function exportResources($format) {
        $data = $this->resourceModel->getAll();
        $this->outputData($data, $format, 'resources');
    }
    
    private function exportAll($format) {
        $data = [
            'locations' => $this->locationModel->getAll(),
            'orders' => $this->orderModel->getAll(),
            'resources' => $this->resourceModel->getAll(),
            'exported_at' => date('Y-m-d H:i:s')
        ];
        
        if ($format === 'json') {
            $this->outputJSON($data, 'complete_export');
        } else {
            // Pentru CSV, exportăm separate
            $this->exportLocations('csv');
        }
    }
    
    private function outputData($data, $format, $filename) {
        if ($format === 'csv') {
            $this->outputCSV($data, $filename);
        } else {
            $this->outputJSON($data, $filename);
        }
    }
    
    private function outputCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
    
    private function outputJSON($data, $filename) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
?>
