<?php
class Security {
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            $data = strip_tags($data, '<p><br><strong><em><ul><ol><li>');
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $data = preg_replace('/[<>"\']/', '', $data);
            return trim($data);
        }
        
        return $data;
    }
    public static function validateField($value, $type) {
        $value = self::sanitizeInput($value);
        
        switch($type) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : false;
            case 'phone':
                return preg_match('/^[0-9+\-\s()]*$/', $value) ? $value : false;
            case 'number':
                return is_numeric($value) ? $value : false;
            case 'text':
                return strlen($value) <= 1000 ? $value : false;
            default:
                return $value;
        }
    }
}
?>
