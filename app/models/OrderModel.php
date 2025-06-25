<?php
/**
 * OrderModel - simplified pentru operatiuni cu comenzi
 * Adaptat pentru baza de date fiiclean existenta
 */
class OrderModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toate comenzile cu filtre
     */
    public function getOrdersWithFilters($filters) {
        $sql = "SELECT c.*, cl.Nume as client_name, cl.Email as client_email, 
                       s.Nume as sediu_name, s.Adresa as sediu_address
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.Status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['location_id'])) {
            $sql .= " AND c.idSediu = ?";
            $params[] = $filters['location_id'];
        }
        
        if (!empty($filters['service_type'])) {
            $sql .= " AND c.TipServiciu = ?";
            $params[] = $filters['service_type'];
        }
        
        $sql .= " ORDER BY c.DataProgramare DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Creeaza comanda noua
     */
    public function createOrder($order_data) {
        $sql = "INSERT INTO Comanda (idClient, idSediu, TipServiciu, DataProgramare, Recurenta, Transport, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $order_data['client_id'],
            $order_data['location_id'],
            $order_data['service_type'],
            $order_data['scheduled_date'],
            $order_data['recurring'] ? 1 : 0,
            $order_data['transport'] ? 1 : 0,
            $order_data['status'] ?? 'noua'
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza comanda
     */
    public function updateOrder($order_id, $update_data) {
        $set_clauses = [];
        $params = [];
        
        if (isset($update_data['status'])) {
            $set_clauses[] = "Status = ?";
            $params[] = $update_data['status'];
        }
        
        if (empty($set_clauses)) {
            return false;
        }
        
        $sql = "UPDATE Comanda SET " . implode(', ', $set_clauses) . " WHERE id = ?";
        $params[] = $order_id;
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtine comanda dupa ID
     */
    public function getOrderById($order_id) {
        $sql = "SELECT c.*, cl.Nume as client_name, cl.Email as client_email,
                       s.Nume as sediu_name, s.Adresa as sediu_address
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE c.id = ?";
        
        $stmt = $this->db->query($sql, [$order_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine comenzi dupa utilizator
     */
    public function getOrdersByUserId($user_id, $limit = null) {
        $sql = "SELECT c.*, cl.Nume as client_name, s.Nume as sediu_name
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE cl.user_id = ?
                ORDER BY c.DataProgramare DESC";
        
        $params = [$user_id];
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Anuleaza comanda
     */
    public function cancelOrder($order_id, $reason = '') {
        $sql = "UPDATE Comanda SET Status = 'anulata' WHERE id = ?";
        $stmt = $this->db->query($sql, [$order_id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtine numarul de comenzi active
     */
    public function getActiveOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda WHERE Status IN ('noua', 'in curs')";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Obtine numarul de comenzi de astazi
     */
    public function getTodayOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda WHERE DATE(DataProgramare) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Obtine comenzi recente
     */
    public function getRecentOrders($limit = 10) {
        $sql = "SELECT c.*, cl.Nume as client_name, s.Nume as sediu_name
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                ORDER BY c.id DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine numarul total de comenzi
     */
    public function getTotalOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Obtine comenzi grupate dupa status
     */
    public function getOrdersByStatusCount() {
        $sql = "SELECT Status, COUNT(*) as count FROM Comanda GROUP BY Status";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine optiuni pentru status comenzi
     */
    public function getOrderStatusOptions() {
        return [
            'noua' => 'Noua',
            'in curs' => 'In curs',
            'finalizata' => 'Finalizata',
            'anulata' => 'Anulata'
        ];
    }
    
    /**
     * Obtine optiuni pentru tipuri servicii
     */
    public function getServiceTypeOptions() {
        return [
            'covor' => 'Spalare covoare',
            'auto' => 'Spalare auto',
            'textil' => 'Curatenie textile'
        ];
    }
    
    /**
     * Sterge comanda
     */
    public function deleteOrder($order_id) {
        // Sterge intai consumurile asociate
        $delete_consumption_sql = "DELETE FROM Consum WHERE idComanda = ?";
        $this->db->query($delete_consumption_sql, [$order_id]);
        
        // Apoi sterge comanda
        $sql = "DELETE FROM Comanda WHERE id = ?";
        $stmt = $this->db->query($sql, [$order_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Cauta comenzi
     */
    public function searchOrders($query, $limit = 20) {
        $sql = "SELECT c.*, cl.Nume as client_name, s.Nume as sediu_name
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE cl.Nume LIKE ? OR s.Nume LIKE ? OR c.TipServiciu LIKE ?
                ORDER BY c.DataProgramare DESC
                LIMIT ?";
        
        $search_term = '%' . $query . '%';
        $stmt = $this->db->query($sql, [$search_term, $search_term, $search_term, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtine toate comenzile pentru export
     */
    public function getAllOrdersForExport() {
        $sql = "SELECT c.id, c.TipServiciu, c.DataProgramare, c.Status, c.Recurenta, c.Transport,
                       cl.Nume as client_name, cl.Email as client_email,
                       s.Nume as sediu_name, s.Adresa as sediu_address
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                ORDER BY c.id DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
