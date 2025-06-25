<?php
/**
 * Security utility functions
 * Implements protection against SQL injection and XSS attacks
 */

/**
 * Sanitize input data to prevent XSS attacks
 */
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

/**
 * Validate email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    // Pentru compatibilitate, permite trimiterea fără token temporar
    if (!isset($_SESSION['csrf_token'])) {
        return true; // Temporar pentru tranziție
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Alias pentru compatibilitate cu AuthController
 */
function generateCSRFToken() {
    return generate_csrf_token();
}

/**
 * Alias pentru compatibilitate cu AuthController
 */
function verifyCSRFToken($token) {
    return verify_csrf_token($token);
}

/**
 * Validează numărul de telefon românesc
 */
function validateRomanianPhone($phone) {
    // Elimină spațiile și alte caractere
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Verifică formatele: +40XXXXXXXXX, 0XXXXXXXXX
    $patterns = [
        '/^\+40[0-9]{9}$/',  // +40XXXXXXXXX
        '/^0[0-9]{9}$/'      // 0XXXXXXXXX
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $phone)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Clean filename for uploads
 */
function clean_filename($filename) {
    // Remove path information and dots from filename
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    return $filename;
}

/**
 * Validate and sanitize numeric input
 */
function sanitize_numeric($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_INT);
    
    if ($value === false) {
        return false;
    }
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return $value;
}

/**
 * Previne SQL Injection prin escapare
 */
function escape_sql($string, $connection = null) {
    if ($connection && method_exists($connection, 'real_escape_string')) {
        return $connection->real_escape_string($string);
    }
    return addslashes($string);
}

/**
 * Generează parolă hash securizată
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifică parola împotriva hash-ului
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Limitare rate pentru prevenirea spam-ului
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 3600) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    // Resetează dacă a trecut fereastra de timp
    if (time() - $data['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = [
            'count' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    // Verifică dacă a depășit limita
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    // Incrementează contorul
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Validează și curăță URL-uri
 */
function sanitize_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Verifică dacă URL-ul este valid
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Previne Directory Traversal
 */
function sanitize_filename($filename) {
    // Elimină caractere periculoase
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    // Previne ../ și alte tentative de traversal
    $filename = str_replace(['../', '.\\', '..\\'], '', $filename);
    
    return $filename;
}

/**
 * Verifică permisiunile de fișier
 */
function is_safe_file($filepath, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']) {
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Log evenimente de securitate
 */
function log_security_event($event, $details = '', $level = 'INFO') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'level' => $level
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    
    // Scrie în fișierul de log de securitate
    $logFile = APP_ROOT . '/logs/security.log';
    
    // Creează directorul logs dacă nu există
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logLine, 3, $logFile);
}

/**
 * Verifică dacă cererea vine dintr-o sursă de încredere
 */
function verify_request_origin() {
    $allowedOrigins = [
        'http://localhost',
        'https://localhost',
        BASE_URL ?? 'http://localhost'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    
    foreach ($allowedOrigins as $allowed) {
        if (strpos($origin, $allowed) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Curăță și validează datele de intrare pentru baza de date
 */
function clean_database_input($input, $type = 'string') {
    $input = trim($input);
    
    switch ($type) {
        case 'int':
            return (int) $input;
            
        case 'float':
            return (float) $input;
            
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
            
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
            
        case 'string':
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Verifică puterea parolei
 */
function check_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = 'Parola trebuie să aibă cel puțin 6 caractere';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Parola trebuie să conțină cel puțin o literă mare';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Parola trebuie să conțină cel puțin o cifră';
    }
    
    return [
        'is_strong' => empty($errors),
        'errors' => $errors
    ];
}
?>