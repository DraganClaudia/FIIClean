<?php
/**
 * Functii helper pentru aplicatie
 */

/**
 * Formatare data pentru afisare
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) {
        return 'N/A';
    }
    
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Formatare data si ora
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Badge-uri pentru status
 */
function getStatusBadge($status) {
    $badges = [
        'noua' => '<span class="badge badge-new">Noua</span>',
        'in curs' => '<span class="badge badge-progress">In curs</span>',
        'finalizata' => '<span class="badge badge-completed">Finalizata</span>',
        'anulata' => '<span class="badge badge-cancelled">Anulata</span>',
        'activ' => '<span class="badge badge-active">Activ</span>',
        'inactiv' => '<span class="badge badge-inactive">Inactiv</span>',
        'reparatii' => '<span class="badge badge-maintenance">Reparatii</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge">' . htmlspecialchars($status) . '</span>';
}

/**
 * Nume pentru tipuri de servicii
 */
function getServiceTypeName($type) {
    $types = [
        'covor' => 'Spalare covoare',
        'auto' => 'Spalare auto',
        'textil' => 'Curatenie textile'
    ];
    
    return $types[$type] ?? htmlspecialchars($type);
}

/**
 * Calculeaza procentaj
 */
function calculatePercentage($value, $total) {
    if ($total == 0) {
        return 0;
    }
    
    return round(($value / $total) * 100, 2);
}

/**
 * Nume pentru tipuri de resurse
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
 * Formatare numar
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
}

/**
 * Trunchiere text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return htmlspecialchars($text);
    }
    
    return htmlspecialchars(substr($text, 0, $length)) . '...';
}

/**
 * Generare paginare
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $maxLinks = 5) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Buton Anterior
    if ($currentPage > 1) {
        $prevUrl = $baseUrl . '&page=' . ($currentPage - 1);
        $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="pagination-btn">« Anterior</a>';
    }
    
    // Numerele paginilor
    $start = max(1, $currentPage - floor($maxLinks / 2));
    $end = min($totalPages, $start + $maxLinks - 1);
    
    for ($i = $start; $i <= $end; $i++) {
        $class = ($i == $currentPage) ? 'pagination-link active' : 'pagination-link';
        $url = $baseUrl . '&page=' . $i;
        $html .= '<a href="' . htmlspecialchars($url) . '" class="' . $class . '">' . $i . '</a>';
    }
    
    // Buton Urmator
    if ($currentPage < $totalPages) {
        $nextUrl = $baseUrl . '&page=' . ($currentPage + 1);
        $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="pagination-btn">Urmator »</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Verifica daca pagina curenta este activa
 */
function isActivePage($controller, $action = null) {
    $currentController = $_GET['controller'] ?? 'public';
    $currentAction = $_GET['action'] ?? 'home';
    
    if ($action) {
        return $currentController === $controller && $currentAction === $action;
    }
    
    return $currentController === $controller;
}

/**
 * Construieste URL cu parametri
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
 * Genereaza sir aleatoriu
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/62))), 1, $length);
}

/**
 * Formatare marime fisier
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Converteste array la CSV
 */
function arrayToCSV($data) {
    if (empty($data)) {
        return '';
    }
    
    $output = fopen('php://memory', 'w');
    
    // Headers
    fputcsv($output, array_keys($data[0]));
    
    // Date
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Timp scurs
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'acum ' . $time . ' secunde';
    } elseif ($time < 3600) {
        return 'acum ' . floor($time / 60) . ' minute';
    } elseif ($time < 86400) {
        return 'acum ' . floor($time / 3600) . ' ore';
    } else {
        return 'acum ' . floor($time / 86400) . ' zile';
    }
}

/**
 * Debug helper
 */
function debug($data, $die = false) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}
?>
