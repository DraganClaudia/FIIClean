<?php
/**
 * Controller de baza - functionalitate comuna pentru toate controller-ele
 * Versiune curățată fără probleme de headers
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
        // Extract data pentru view
        if (!empty($data)) {
            extract($data);
        }
        
        // Include header
        $headerPath = 'app/views/layouts/header.php';
        if (file_exists($headerPath)) {
            include $headerPath;
        }
        
        // Include view-ul principal
        $viewPath = 'app/views/' . $viewName . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo '<div class="alert alert-error">View not found: ' . htmlspecialchars($viewName) . '</div>';
        }
        
        // Include footer
        $footerPath = 'app/views/layouts/footer.php';
        if (file_exists($footerPath)) {
            include $footerPath;
        }
    }
    
    /**
     * Redirect cu verificare headers
     */
    protected function redirect($url) {
        if (!headers_sent()) {
            header('Location: ' . $url);
        } else {
            // Fallback pentru cazul în care headers-urile au fost deja trimise
            echo '<script>window.location.href = "' . htmlspecialchars($url) . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
        }
        exit();
    }
    
    /**
     * JSON response pentru AJAX cu verificare headers
     */
    protected function json($data, $statusCode = 200) {
        // Curata orice output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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
    
    /**
     * Seteaza mesaj flash in sesiune
     */
    protected function setFlashMessage($message, $type = 'info') {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    /**
     * Obtine si sterge mesajul flash din sesiune
     */
    protected function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
    
    /**
     * Valideaza CSRF token
     */
    protected function validateCsrfToken($token) {
        return verify_csrf_token($token);
    }
    
    /**
     * Sanitizeaza input array
     */
    protected function sanitizeInputArray($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInputArray'], $data);
        }
        return sanitize_input($data);
    }
    
    /**
     * Logheaza actiunea utilizatorului
     */
    protected function logUserAction($action, $details = '') {
        $logData = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        error_log("User Action: " . json_encode($logData));
    }
}
?>
