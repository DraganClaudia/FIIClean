<?php
/**
 * AuthController - autentificare si inregistrare utilizatori
 */
require_once 'app/models/UserModel.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    /**
     * Pagina de login
     */
    public function login() {
        // Daca e deja logat, redirect
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
                
                $this->redirect(BASE_URL);
                return;
            } else {
                $error = $result['error'];
            }
        }
        
        $data = [
            'title' => 'Login - CaS',
            'error' => $error,
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Proceseaza login-ul
     */
    private function processLogin() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'Username si parola sunt obligatorii'];
        }
        
        try {
            $user = $this->userModel->authenticateUser($username, $password);
            
            if ($user) {
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Credentiale invalide'];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la autentificare'];
        }
    }
    
    /**
     * Pagina de inregistrare
     */
    public function register() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processRegister();
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['email'] = $result['email'];
                
                $this->redirect(BASE_URL);
                return;
            } else {
                $error = $result['error'];
            }
        }
        
        $data = [
            'title' => 'Inregistrare - CaS',
            'error' => $error,
            'csrf_token' => generate_csrf_token()
        ];
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Proceseaza inregistrarea
     */
    private function processRegister() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Token de securitate invalid'];
        }
        
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validari
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'error' => 'Toate campurile sunt obligatorii'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email invalid'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Parola trebuie sa aiba minim 6 caractere'];
        }
        
        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Parolele nu se potrivesc'];
        }
        
        try {
            if ($this->userModel->userExists($username, $email)) {
                return ['success' => false, 'error' => 'Username sau email deja folosit'];
            }
            
            $userId = $this->userModel->createUser($username, $email, $password);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'username' => $username,
                'email' => $email
            ];
        } catch (Exception $e) {
            error_log("Register error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Eroare la inregistrare'];
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $this->redirect(BASE_URL);
    }
}
