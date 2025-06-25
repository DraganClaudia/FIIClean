<?php
/**
 * Base Controller class
 * Provides common functionality for all controllers
 */
abstract class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Load a view with data
     */
    protected function view($viewName, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Include header
        include 'app/views/layouts/header.php';
        
        // Include the specific view
        $viewPath = 'app/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo '<div class="error">View not found: ' . htmlspecialchars($viewName) . '</div>';
        }
        
        // Include footer
        include 'app/views/layouts/footer.php';
    }
    
    /**
     * Redirect to a specific URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Return JSON response for AJAX calls
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
