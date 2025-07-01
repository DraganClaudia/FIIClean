<?php
class JWT {
    private static $secret = 'fiiclean_secret_2024';
    
    public static function create($userId, $username, $role, $locationId = null) {
        $data = [
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'location_id' => $locationId,
            'created' => time(),
            'expires' => time() + (24 * 60 * 60) // 24 ore
        ];
        
        $json = json_encode($data);
        $base64 = base64_encode($json);
        $signature = hash_hmac('sha256', $base64, self::$secret);
        
        return $base64 . '.' . $signature;
    }
    
    public static function verify($token) {
        if (!$token) return false;
        
        $parts = explode('.', $token);
        if (count($parts) !== 2) return false;
        
        $base64 = $parts[0];
        $signature = $parts[1];
        
        $expectedSignature = hash_hmac('sha256', $base64, self::$secret);
        if ($signature !== $expectedSignature) return false;
        
        $json = base64_decode($base64);
        $data = json_decode($json, true);
        
        if (!$data) return false;
        
        if ($data['expires'] < time()) return false;
        
        return $data;
    }
}
?>
