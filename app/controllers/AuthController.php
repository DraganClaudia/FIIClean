<?php
/**
 * AuthController - handles authentication and user session management
 */
require_once 'app/models/UserModel.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    /**
     * Login page and authentication
     */
    public function login() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processLogin();
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = $result['user']['username'];
                $_SESSION['email'] = $result['user']['email'];
                $_SESSION['login_time'] = time();
                
                session_regenerate_id(true);
                
                $redirect_url = $_SESSION['intended_url'] ?? BASE_URL;
                unset($_SESSION['intended_url']);
                
                if ($this->isAjax()) {
                    $this->json([
                        'success' => true,
                        'message' => 'Autentificare reușită',
                        'redirect_url' => $redirect_url
                    ]);
                } else {
                    $this->redirect($redirect_url);
                }
                return;
            } else {
                $error = $result['error'];
                
                if ($this->isAjax()) {
                    $this->json([
                        'success' => false,
                        'error' => $error
                    ], 401);
                    return;
                }
            }
        }
        
        $data = [
            'title' => 'Autentificare - CaS',
            'error' => $error,
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Process login form submission
     */
    private function processLogin() {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'Username și parola sunt obligatorii'];
        }
        
        if ($this->hasTooManyFailedAttempts($username)) {
            return ['success' => false, 'error' => 'Prea multe încercări eșuate. Încercați mai târziu.'];
        }
        
        try {
            $user = $this->userModel->authenticateUser($username, $password);
            
            if ($user) {
                $this->resetFailedAttempts($username);
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                $this->recordFailedAttempt($username);
                return ['success' => false, 'error' => 'Username sau parolă incorectă'];
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la autentificare'];
        }
    }
    
    /**
     * Registration page and user creation
     */
    public function register() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processRegistration();
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['email'] = $result['email'];
                $_SESSION['login_time'] = time();
                
                session_regenerate_id(true);
                
                if ($this->isAjax()) {
                    $this->json([
                        'success' => true,
                        'message' => 'Cont creat cu succes',
                        'redirect_url' => BASE_URL
                    ]);
                } else {
                    $this->redirect(BASE_URL);
                }
                return;
            } else {
                $error = $result['error'];
                
                if ($this->isAjax()) {
                    $this->json([
                        'success' => false,
                        'error' => $error
                    ], 400);
                    return;
                }
            }
        }
        
        $data = [
            'title' => 'Înregistrare - CaS',
            'error' => $error,
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Process registration form submission
     */
    private function processRegistration() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $validation_result = $this->validateRegistrationData($username, $email, $password, $confirm_password);
        if (!$validation_result['valid']) {
            return ['success' => false, 'error' => $validation_result['error']];
        }
        
        try {
            if ($this->userModel->userExists($username, $email)) {
                return ['success' => false, 'error' => 'Username sau email deja înregistrat'];
            }
            
            $user_id = $this->userModel->createUser($username, $email, $password);
            
            if ($user_id) {
                return [
                    'success' => true,
                    'user_id' => $user_id,
                    'username' => $username,
                    'email' => $email
                ];
            } else {
                return ['success' => false, 'error' => 'Eroare la crearea contului'];
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la înregistrare'];
        }
    }
    
    /**
     * Validate registration form data
     */
    private function validateRegistrationData($username, $email, $password, $confirm_password) {
        if (empty($username) || strlen($username) < 3) {
            return ['valid' => false, 'error' => 'Username-ul trebuie să aibă cel puțin 3 caractere'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'error' => 'Username-ul poate conține doar litere, cifre și underscore'];
        }
        
        if (empty($email) || !validate_email($email)) {
            return ['valid' => false, 'error' => 'Adresa de email nu este validă'];
        }
        
        if (empty($password) || strlen($password) < 6) {
            return ['valid' => false, 'error' => 'Parola trebuie să aibă cel puțin 6 caractere'];
        }
        
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
            return ['valid' => false, 'error' => 'Parola trebuie să conțină cel puțin o literă mare și o cifră'];
        }
        
        if ($password !== $confirm_password) {
            return ['valid' => false, 'error' => 'Parolele nu se potrivesc'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Logout functionality
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $username = $_SESSION['username'] ?? 'unknown';
            error_log("User logout: $username");
        }
        
        session_destroy();
        session_start();
        session_regenerate_id(true);
        
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'Delogare reușită',
                'redirect_url' => BASE_URL
            ]);
        } else {
            $this->redirect(BASE_URL);
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public function checkAuth() {
        $is_authenticated = isset($_SESSION['user_id']);
        $user_data = null;
        
        if ($is_authenticated) {
            $user_data = [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'login_time' => $_SESSION['login_time'] ?? null
            ];
        }
        
        $this->json([
            'authenticated' => $is_authenticated,
            'user' => $user_data,
            'timestamp' => date('c')
        ]);
    }
    
    /**
     * Track failed login attempts
     */
    private function recordFailedAttempt($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts_file = sys_get_temp_dir() . '/failed_attempts_' . md5($username . $ip);
        
        $attempts = 1;
        if (file_exists($attempts_file)) {
            $data = json_decode(file_get_contents($attempts_file), true);
            if ($data && time() - $data['timestamp'] < 3600) {
                $attempts = $data['attempts'] + 1;
            }
        }
        
        file_put_contents($attempts_file, json_encode([
            'attempts' => $attempts,
            'timestamp' => time()
        ]));
    }
    
    /**
     * Check if user has too many failed attempts
     */
    private function hasTooManyFailedAttempts($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts_file = sys_get_temp_dir() . '/failed_attempts_' . md5($username . $ip);
        
        if (file_exists($attempts_file)) {
            $data = json_decode(file_get_contents($attempts_file), true);
            if ($data && time() - $data['timestamp'] < 3600) {
                return $data['attempts'] >= 5;
            }
        }
        
        return false;
    }
    
    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts_file = sys_get_temp_dir() . '/failed_attempts_' . md5($username . $ip);
        
        if (file_exists($attempts_file)) {
            unlink($attempts_file);
        }
    }
    
    /**
     * Require authentication for protected routes
     */
    public function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? BASE_URL;
            
            if ($this->isAjax()) {
                $this->json([
                    'authenticated' => false,
                    'error' => 'Autentificare necesară',
                    'login_url' => BASE_URL . '?controller=auth&action=login'
                ], 401);
            } else {
                $this->redirect(BASE_URL . '?controller=auth&action=login');
            }
            exit();
        }
        
        // Check session timeout (24 hours)
        $session_timeout = 24 * 60 * 60;
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
            $this->logout();
            return;
        }
        
        $_SESSION['last_activity'] = time();
    }
}