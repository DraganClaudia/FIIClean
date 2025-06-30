<?php
class Database
{
    private $db_file = '../data/database.sqlite'; //Nu crea fisierul, se creaaza automat
    private $pdo;

    public function connect(){
        try {
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
        ";

        $this->pdo->exec($sql);
    }

}
?>
