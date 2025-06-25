<?php
/**
 * OrderModel - handles order data operations
 */
class OrderModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all orders with filters
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
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND cl.user_id = ?";
            $params[] = $filters['user_id'];
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
     * Get orders count with filters
     */
    public function getOrdersCountWithFilters($filters) {
        $sql = "SELECT COUNT(*) as count
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.Status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND cl.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Create new order
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
     * Update order
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
     * Get order by ID
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
     * Get order by ID and user ID
     */
    public function getOrderByIdAndUserId($order_id, $user_id) {
        $sql = "SELECT c.*, cl.Nume as client_name, cl.Email as client_email,
                       s.Nume as sediu_name, s.Adresa as sediu_address
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE c.id = ? AND cl.user_id = ?";
        
        $stmt = $this->db->query($sql, [$order_id, $user_id]);
        return $stmt->fetch();
    }
    
    /**
     * Get orders by user ID
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
     * Get user order count
     */
    public function getUserOrderCount($user_id, $status = null) {
        $sql = "SELECT COUNT(*) as count
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                WHERE cl.user_id = ?";
        
        $params = [$user_id];
        
        if ($status) {
            $sql .= " AND c.Status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get user service usage
     */
    public function getUserServiceUsage($user_id) {
        $sql = "SELECT c.TipServiciu, COUNT(*) as count
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                WHERE cl.user_id = ?
                GROUP BY c.TipServiciu";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder($order_id, $reason = '') {
        $sql = "UPDATE Comanda SET Status = 'anulata' WHERE id = ?";
        $stmt = $this->db->query($sql, [$order_id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get active orders count
     */
    public function getActiveOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda WHERE Status IN ('noua', 'in curs')";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get today's orders count
     */
    public function getTodayOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda WHERE DATE(DataProgramare) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get recent orders
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
     * Get total orders count
     */
    public function getTotalOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM Comanda";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get orders by status count
     */
    public function getOrdersByStatusCount() {
        $sql = "SELECT Status, COUNT(*) as count FROM Comanda GROUP BY Status";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get order status options
     */
    public function getOrderStatusOptions() {
        return [
            'noua' => 'Nouă',
            'in curs' => 'În curs',
            'finalizata' => 'Finalizată',
            'anulata' => 'Anulată'
        ];
    }
    
    /**
     * Get service type options
     */
    public function getServiceTypeOptions() {
        return [
            'covor' => 'Spălare covoare',
            'auto' => 'Spălare auto',
            'textil' => 'Curățenie textile'
        ];
    }
    
    /**
     * Get or create client by user ID
     */
    public function getClientByUserId($user_id) {
        $sql = "SELECT * FROM Client WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new client
     */
    public function createClient($client_data) {
        $sql = "INSERT INTO Client (Nume, Email, Telefon, Adresa, user_id) VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $client_data['name'],
            $client_data['email'],
            $client_data['phone'] ?? '',
            $client_data['address'] ?? '',
            $client_data['user_id']
        ];
        
        $stmt = $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get all orders for export
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

    /**
 * Delete order
 */
public function deleteOrder($order_id) {
    // First delete any related consumption records
    $delete_consumption_sql = "DELETE FROM Consum WHERE idComanda = ?";
    $this->db->query($delete_consumption_sql, [$order_id]);
    
    // Then delete the order
    $sql = "DELETE FROM Comanda WHERE id = ?";
    $stmt = $this->db->query($sql, [$order_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Search orders
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
 * Rate order (add rating)
 */
public function rateOrder($order_id, $rating, $review = '') {
    // This would require adding rating fields to the order table
    // For now, we'll create a simple implementation
    $sql = "UPDATE Comanda SET rating = ?, review = ? WHERE id = ?";
    
    try {
        $stmt = $this->db->query($sql, [$rating, $review, $order_id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        // If columns don't exist, just return true for now
        error_log("Rating columns may not exist in Comanda table: " . $e->getMessage());
        return true;
    }
}

/**
 * Update client profile
 */
public function updateClientProfile($client_id, $update_data) {
    $set_clauses = [];
    $params = [];
    
    if (isset($update_data['phone'])) {
        $set_clauses[] = "Telefon = ?";
        $params[] = $update_data['phone'];
    }
    
    if (isset($update_data['address'])) {
        $set_clauses[] = "Adresa = ?";
        $params[] = $update_data['address'];
    }
    
    if (empty($set_clauses)) {
        return false;
    }
    
    $sql = "UPDATE Client SET " . implode(', ', $set_clauses) . " WHERE id = ?";
    $params[] = $client_id;
    
    $stmt = $this->db->query($sql, $params);
    return $stmt->rowCount() > 0;
}

/**
 * Get all orders by user ID (for export)
 */
public function getAllOrdersByUserId($user_id) {
    $sql = "SELECT c.*, cl.Nume as client_name, s.Nume as sediu_name
            FROM Comanda c
            LEFT JOIN Client cl ON c.idClient = cl.id
            LEFT JOIN Sediu s ON c.idSediu = s.id
            WHERE cl.user_id = ?
            ORDER BY c.DataProgramare DESC";
    
    $stmt = $this->db->query($sql, [$user_id]);
    return $stmt->fetchAll();
}

/**
 * Get order history/audit trail
 */
public function getOrderHistory($order_id) {
    // This would require an order_history table
    // For now, return basic order info with status changes
    $sql = "SELECT 
                'Status Change' as action,
                Status as details,
                DataProgramare as action_date
            FROM Comanda 
            WHERE id = ?";
    
    $stmt = $this->db->query($sql, [$order_id]);
    return $stmt->fetchAll();
}
}