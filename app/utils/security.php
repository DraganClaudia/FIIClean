<?php
/**
 * Functii de securitate - protectie XSS si CSRF
 */

/**
 * Sanitizare input pentru prevenirea XSS
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
 * Sanitizare si validare numere
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
 * Validare email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generare token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificare token CSRF
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return true; // Pentru compatibilitate in tranzitie
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hash parola securizat
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificare parola
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Curăță numele fișierului pentru upload-uri
 */
function clean_filename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

/**
 * Validare URL
 */
function validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Log evenimente de securitate
 */
function log_security_event($event, $details = '') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null
    ];
    
    error_log("Security Event: " . json_encode($logEntry));
}

/**
 * Verifică puterea parolei
 */
function check_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = 'Parola trebuie sa aiba cel putin 6 caractere';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Parola trebuie sa contina cel putin o litera mare';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Parola trebuie sa contina cel putin o cifra';
    }
    
    return [
        'is_strong' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Rate limiting simplu
 */
function check_rate_limit($identifier, $maxAttempts = 5, $timeWindow = 3600) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    // Reset daca a trecut fereastra de timp
    if (time() - $data['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = [
            'count' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    // Verifica limita
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}
?>
