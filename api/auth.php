<?php
require_once __DIR__ . '/helpers/JWT.php';
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->connect();
$action = $_GET['action'] ?? 'login';

switch($action) {
    case 'login':
        handleLogin($db);
        break;
    case 'register':
        handleRegister($db);
        break;
    case 'me':
        handleMe();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'change-password':
        handleChangePassword($db);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
function handleLogin($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['error' => 'Username and password required']);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT u.*, l.name as location_name 
        FROM users u 
        LEFT JOIN locations l ON u.location_id = l.id 
        WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['error' => 'Invalid credentials']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE users SET last_login = datetime('now') WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    $token = JWT::create($user['id'], $user['username'], $user['role'], $user['location_id']);
    
    unset($user['password']);
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => $user
    ]);
}

function handleRegister($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['username', 'email', 'password', 'first_name', 'last_name'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            echo json_encode(['error' => "Field $field is required"]);
            return;
        }
    }
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email']);
        return;
    }
    
    if (strlen($input['password']) < 6) {
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$input['username'], $input['email']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'Username or email already exists']);
        return;
    }
    
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, phone, role) 
        VALUES (?, ?, ?, ?, ?, ?, 'client')
    ");
    
    try {
        $stmt->execute([
            $input['username'],
            $input['email'],
            password_hash($input['password'], PASSWORD_DEFAULT),
            $input['first_name'],
            $input['last_name'],
            $input['phone'] ?? null
        ]);
        
        $userId = $db->lastInsertId();
        
        $token = JWT::create($userId, $input['username'], 'client', null);
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'token' => $token,
            'user_id' => $userId
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Registration failed']);
    }
}

function handleMe() {
    $auth = new Auth();
    $user = $auth->checkAuth();
    
    if ($user) {
        unset($user['password']);
        echo json_encode(['success' => true, 'user' => $user]);
    }
}

function handleLogout() {
    echo json_encode(['success' => true, 'message' => 'Logged out']);
}

function handleChangePassword($db) {
    $auth = new Auth();
    $user = $auth->checkAuth();
    if (!$user) return;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword)) {
        echo json_encode(['error' => 'Current and new password required']);
        return;
    }
    
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['error' => 'Current password incorrect']);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode(['error' => 'New password must be at least 6 characters']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Password changed']);
}
?>
