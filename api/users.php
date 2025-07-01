<?php
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$auth = new Auth();
$database = new Database();
$db = $database->connect();
$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        handleUsersList($db, $auth);
        break;
    case 'create':
        handleCreateUser($db, $auth);
        break;
    case 'update':
        handleUpdateUser($db, $auth);
        break;
    case 'delete':
        handleDeleteUser($db, $auth);
        break;
    case 'profile':
        handleGetProfile($auth);
        break;
    case 'update-profile':
        handleUpdateProfile($db, $auth);
        break;
    case 'workers':
        handleGetWorkers($db, $auth);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function handleUsersList($db, $auth) {
    if (!$auth->hasRole('admin')) return;
    
    $role = $_GET['role'] ?? null;
    $sql = "SELECT id, username, email, first_name, last_name, phone, role, location_id, is_active, last_login, created_at FROM users WHERE 1=1";
    $params = [];
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
}

function handleCreateUser($db, $auth) {
    if (!$auth->hasRole('admin')) return;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            echo json_encode(['error' => "Field $field is required"]);
            return;
        }
    }
    
    // Verifică unicitatea
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$input['username'], $input['email']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'Username or email already exists']);
        return;
    }
    
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, phone, role, location_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([
        $input['username'],
        $input['email'],
        password_hash($input['password'], PASSWORD_DEFAULT),
        $input['first_name'],
        $input['last_name'],
        $input['phone'] ?? null,
        $input['role'],
        $input['location_id'] ?? null
    ])) {
        echo json_encode(['success' => true, 'user_id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['error' => 'User creation failed']);
    }
}

function handleUpdateUser($db, $auth) {
    if (!$auth->hasRole('admin')) return;
    
    $id = $_GET['id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    $allowedFields = ['username', 'email', 'first_name', 'last_name', 'phone', 'role', 'location_id', 'is_active'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['error' => 'No fields to update']);
        return;
    }
    
    $params[] = $id;
    $stmt = $db->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
}

function handleDeleteUser($db, $auth) {
    if (!$auth->hasRole('admin')) return;
    
    $id = $_GET['id'];
    
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetchColumn();
    
    if ($role === 'admin') {
        $adminCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($adminCount <= 1) {
            echo json_encode(['error' => 'Cannot delete the last admin']);
            return;
        }
    }
    
    $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Delete failed']);
    }
}

function handleGetProfile($auth) {
    $user = $auth->checkAuth();
    if (!$user) return;
    
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user]);
}

function handleUpdateProfile($db, $auth) {
    $user = $auth->checkAuth();
    if (!$user) return;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $allowedFields = ['first_name', 'last_name', 'phone', 'email'];
    $updates = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['error' => 'No fields to update']);
        return;
    }
    
    $params[] = $user['id'];
    $stmt = $db->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Update failed']);
    }
}

function handleGetWorkers($db, $auth) {
    $user = $auth->checkAuth();
    if (!$user) return;
    
    $locationId = $_GET['location_id'] ?? $user['location_id'];
    
    if ($user['role'] === 'manager' && $locationId != $user['location_id']) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT id, username, first_name, last_name, role, phone, is_active
        FROM users 
        WHERE location_id = ? AND role IN ('worker_transport', 'worker_cleaner')
        ORDER BY role, first_name
    ");
    $stmt->execute([$locationId]);
    $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($workers);
}
?>
