<?php
/**
 * Helper functions for CaS application
 * Utility functions used throughout the application
 */

/**
 * Format date for display
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) {
        return 'N/A';
    }
    
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'noua' => '<span class="badge badge-new">Nouă</span>',
        'in curs' => '<span class="badge badge-progress">În curs</span>',
        'finalizata' => '<span class="badge badge-completed">Finalizată</span>',
        'anulata' => '<span class="badge badge-cancelled">Anulată</span>',
        'activ' => '<span class="badge badge-active">Activ</span>',
        'inactiv' => '<span class="badge badge-inactive">Inactiv</span>',
        'reparatii' => '<span class="badge badge-maintenance">Reparații</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-unknown">' . htmlspecialchars($status) . '</span>';
}

/**
 * Get service type display name
 */
function getServiceTypeName($type) {
    $types = [
        'covor' => 'Spălare covoare',
        'auto' => 'Spălare auto',
        'textil' => 'Curățenie textile'
    ];
    
    return $types[$type] ?? htmlspecialchars($type);
}

/**
 * Get resource type display name
 */
function getResourceTypeName($type) {
    $types = [
        'detergent' => 'Detergent',
        'apa' => 'Apă',
        'echipament' => 'Echipament'
    ];
    
    return $types[$type] ?? htmlspecialchars($type);
}

/**
 * Calculate percentage
 */
function calculatePercentage($value, $total) {
    if ($total == 0) {
        return 0;
    }
    
    return round(($value / $total) * 100, 2);
}

/**
 * Format number with Romanian locale
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
}

/**
 * Format currency (RON)
 */
function formatCurrency($amount) {
    return formatNumber($amount, 2) . ' RON';
}

/**
 * Truncate text with ellipsis
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return htmlspecialchars($text);
    }
    
    return htmlspecialchars(substr($text, 0, $length)) . '...';
}

/**
 * Generate pagination links
 */
function generatePagination($current_page, $total_pages, $base_url, $max_links = 5) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_url = $base_url . '&page=' . ($current_page - 1);
        $html .= '<a href="' . htmlspecialchars($prev_url) . '" class="pagination-btn">« Anterior</a>';
    }
    
    // Page numbers
    $start = max(1, $current_page - floor($max_links / 2));
    $end = min($total_pages, $start + $max_links - 1);
    
    if ($start > 1) {
        $html .= '<a href="' . htmlspecialchars($base_url . '&page=1') . '" class="pagination-link">1</a>';
        if ($start > 2) {
            $html .= '<span class="pagination-dots">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $class = ($i == $current_page) ? 'pagination-link active' : 'pagination-link';
        $url = $base_url . '&page=' . $i;
        $html .= '<a href="' . htmlspecialchars($url) . '" class="' . $class . '">' . $i . '</a>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span class="pagination-dots">...</span>';
        }
        $html .= '<a href="' . htmlspecialchars($base_url . '&page=' . $total_pages) . '" class="pagination-link">' . $total_pages . '</a>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $next_url = $base_url . '&page=' . ($current_page + 1);
        $html .= '<a href="' . htmlspecialchars($next_url) . '" class="pagination-btn">Următorul »</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Check if current page matches route
 */
function isActivePage($controller, $action = null) {
    $current_controller = $_GET['controller'] ?? 'public';
    $current_action = $_GET['action'] ?? 'home';
    
    if ($action) {
        return $current_controller === $controller && $current_action === $action;
    }
    
    return $current_controller === $controller;
}

/**
 * Get current URL with parameters
 */
function getCurrentUrl($exclude_params = []) {
    $url = $_SERVER['REQUEST_URI'];
    $parsed = parse_url($url);
    
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $params);
        
        foreach ($exclude_params as $param) {
            unset($params[$param]);
        }
        
        $query = http_build_query($params);
        $url = $parsed['path'] . ($query ? '?' . $query : '');
    }
    
    return $url;
}

/**
 * Build URL with parameters
 */
function buildUrl($controller, $action = null, $params = []) {
    $url = '?controller=' . urlencode($controller);
    
    if ($action) {
        $url .= '&action=' . urlencode($action);
    }
    
    foreach ($params as $key => $value) {
        $url .= '&' . urlencode($key) . '=' . urlencode($value);
    }
    
    return $url;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Check if file upload is valid
 */
function isValidUpload($file, $allowed_types = [], $max_size = 2097152) { // 2MB default
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    if (!empty($allowed_types)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime_type, $allowed_types)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Log application activity
 */
function logActivity($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $log_message = "[{$timestamp}] [{$level}] User: {$user_id}, IP: {$ip} - {$message}";
    error_log($log_message);
}

/**
 * Get user friendly file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Convert array to CSV string
 */
function arrayToCSV($data) {
    if (empty($data)) {
        return '';
    }
    
    $output = fopen('php://memory', 'w');
    
    // Add headers
    fputcsv($output, array_keys($data[0]));
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Validate Romanian phone number
 */
function validateRomanianPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Romanian mobile: +40 7xx xxx xxx or 07xx xxx xxx
    // Romanian landline: +40 2xx xxx xxx or 02xx xxx xxx
    $patterns = [
        '/^\+407[0-9]{8}$/',     // +40 7xx xxx xxx
        '/^07[0-9]{8}$/',        // 07xx xxx xxx
        '/^\+402[0-9]{8}$/',     // +40 2xx xxx xxx
        '/^02[0-9]{8}$/',        // 02xx xxx xxx
        '/^\+403[0-9]{8}$/',     // +40 3xx xxx xxx
        '/^03[0-9]{8}$/'         // 03xx xxx xxx
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $phone)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'acum ' . $time . ' secunde';
    } elseif ($time < 3600) {
        return 'acum ' . floor($time / 60) . ' minute';
    } elseif ($time < 86400) {
        return 'acum ' . floor($time / 3600) . ' ore';
    } elseif ($time < 2592000) {
        return 'acum ' . floor($time / 86400) . ' zile';
    } elseif ($time < 31104000) {
        return 'acum ' . floor($time / 2592000) . ' luni';
    } else {
        return 'acum ' . floor($time / 31104000) . ' ani';
    }
}

/**
 * Generate breadcrumb navigation
 */
function generateBreadcrumb($items) {
    if (empty($items)) {
        return '';
    }
    
    $html = '<nav class="breadcrumb">';
    $html .= '<ol>';
    
    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);
        
        if ($is_last) {
            $html .= '<li class="active">' . htmlspecialchars($item['name']) . '</li>';
        } else {
            $html .= '<li><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['name']) . '</a></li>';
        }
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    // Basic permission system - can be extended
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // For now, all authenticated users have all permissions
    // This can be extended with roles and permissions
    return true;
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}

/**
 * Debug helper - only in development
 */
function debug($data, $die = false) {
    if (defined('DEBUG') && DEBUG === true) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}
