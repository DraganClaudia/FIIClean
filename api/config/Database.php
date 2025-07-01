<?php
class Database
{
    private $db_file;
    private $pdo;

    public function __construct() {
        // Calea corectă către api/data/database.sqlite
        $this->db_file = __DIR__ . '/../data/database.sqlite';
    }

    public function connect(){
        try {
            // Creează directorul dacă nu există
            $dataDir = dirname($this->db_file);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }
            
            // Creează fișierul dacă nu există
            if (!file_exists($this->db_file)) {
                touch($this->db_file);
            }
            
            $this->pdo = new PDO('sqlite:' . $this->db_file);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->pdo;
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            return false;
        }
    }

    public function initTables(){
        $sql = "
            CREATE TABLE IF NOT EXISTS locations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                address TEXT NOT NULL,
                latitude REAL NOT NULL,
                longitude REAL NOT NULL,
                services TEXT,
                status TEXT DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                location_id INTEGER,
                client_name TEXT NOT NULL,
                client_phone TEXT,
                client_email TEXT,
                service_type TEXT NOT NULL,
                pickup_address TEXT,
                delivery_address TEXT,
                scheduled_date DATETIME,
                status TEXT DEFAULT 'pending',
                price DECIMAL(10,2),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (location_id) REFERENCES locations (id)
            );

             CREATE TABLE IF NOT EXISTS resources (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                location_id INTEGER,
                resource_type TEXT NOT NULL,
                name TEXT NOT NULL,
                quantity INTEGER DEFAULT 0,
                unit TEXT DEFAULT 'bucati',
                min_threshold INTEGER DEFAULT 10,
                cost_per_unit DECIMAL(10,2),
                supplier TEXT,
                last_restocked DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (location_id) REFERENCES locations (id)
            );
                CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                phone TEXT,
                role TEXT NOT NULL DEFAULT 'client',
                location_id INTEGER,
                is_active INTEGER DEFAULT 1,
                last_login DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (location_id) REFERENCES locations (id)
            );
            ";
            
        $this->pdo->exec($sql);
        $this->createAdminUser();
    }

    private function createAdminUser() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, role, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'admin',
                'admin@fiiclean.ro',
                password_hash('admin123', PASSWORD_DEFAULT),
                'Administrator',
                'Sistem',
                'admin',
                1
            ]);
        }
    }
}
?>
