<?php
/**
 * Controller de baza - functionalitate comuna pentru toate controller-ele
 */
abstract class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Incarca view cu date
     */
    protected function view($viewName, $data = []) {
        extract($data);
        
        include 'app/views/layouts/header.php';
        
        $viewPath = 'app/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo '<div class="alert alert-error">View not found: ' . htmlspecialchars($viewName) . '</div>';
        }
        
        include 'app/views/layouts/footer.php';
    }
    
    /**
     * Redirect
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * JSON response pentru AJAX
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Verifica daca request-ul este AJAX
     */
    protected function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Verifica autentificarea
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Autentificare necesara'], 401);
            } else {
                $this->redirect('?controller=auth&action=login');
            }
        }
    }
}
