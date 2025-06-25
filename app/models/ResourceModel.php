<?php
/**
 * ResourceModel - simplified pentru operatiuni cu resurse
 * Adaptat pentru baza de date fiiclean existenta
 */
class ResourceModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toate resursele
     */
    public function getAllResources() {
        $sql = "SELECT * FROM Resursa ORDER BY Tip, Nume";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine resurse dupa tip
     */
    public function getResourcesByType($type) {
        $sql = "SELECT * FROM Resursa WHERE Tip = ? ORDER BY Nume";
        $stmt = $this->db->query($sql, [$type]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine resursa dupa ID
     */
    public function getResourceById($id) {
        $sql = "SELECT * FROM Resursa WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Creeaza resursa noua
     */
    public function createResource($type, $name, $quantity) {
        $sql = "INSERT INTO Resursa (Tip, Nume, CantitateDisponibila) VALUES (?, ?, ?)";
        $stmt = $this->db->query($sql, [$type, $name, $quantity]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza resursa
     */
    public function updateResource($id, $type, $name, $quantity) {
        $sql = "UPDATE Resursa SET Tip = ?, Nume = ?, CantitateDisponibila = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$type, $name, $quantity, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Actualizeaza doar cantitatea resursei
     */
    public function updateResourceQuantity($id, $quantity) {
        $sql = "UPDATE Resursa SET CantitateDisponibila = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$quantity, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sterge resursa
     */
    public function deleteResource($id) {
        $sql = "DELETE FROM Resursa WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtine resurse cu stoc redus
     */
    public function getLowStockResources($threshold = 10) {
        $sql = "SELECT * FROM Resursa WHERE CantitateDisponibila < ? ORDER BY CantitateDisponibila ASC";
        $stmt = $this->db->query($sql, [$threshold]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine consumul de resurse pentru o comanda
     */
    public function getResourceConsumption($order_id) {
        $sql = "SELECT r.*, con.Cantitate as consumed_quantity
                FROM Resursa r
                JOIN Consum con ON r.id = con.idResursa
                WHERE con.idComanda = ?";
        $stmt = $this->db->query($sql, [$order_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Inregistreaza consumul de resurse
     */
    public function recordConsumption($resource_id, $order_id, $quantity) {
        $sql = "INSERT INTO Consum (idResursa, idComanda, Cantitate) VALUES (?, ?, ?)";
        $stmt = $this->db->query($sql, [$resource_id, $order_id, $quantity]);
        
        if ($stmt->rowCount() > 0) {
            $update_sql = "UPDATE Resursa SET CantitateDisponibila = CantitateDisponibila - ? WHERE id = ?";
            $this->db->query($update_sql, [$quantity, $resource_id]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Reaprovizionare resursa
     */
    public function restockResource($resource_id, $quantity) {
        $sql = "UPDATE Resursa SET CantitateDisponibila = CantitateDisponibila + ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$quantity, $resource_id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtine statistici de consum resurse
     */
    public function getResourceConsumptionStats($date_from = null, $date_to = null) {
        $sql = "SELECT 
                    r.Nume as resource_name,
                    r.Tip as resource_type,
                    SUM(con.Cantitate) as total_consumed,
                    COUNT(con.id) as usage_count,
                    AVG(con.Cantitate) as avg_consumption
                FROM Resursa r
                LEFT JOIN Consum con ON r.id = con.idResursa";
        
        $params = [];
        if ($date_from && $date_to) {
            $sql .= " LEFT JOIN Comanda c ON con.idComanda = c.id
                      WHERE c.DataProgramare BETWEEN ? AND ?";
            $params = [$date_from, $date_to];
        }
        
        $sql .= " GROUP BY r.id, r.Nume, r.Tip
                  ORDER BY total_consumed DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Cauta resurse
     */
    public function searchResources($query, $limit = 20) {
        $sql = "SELECT * FROM Resursa 
                WHERE Nume LIKE ? OR Tip LIKE ?
                ORDER BY Nume
                LIMIT ?";
        
        $search_term = '%' . $query . '%';
        $stmt = $this->db->query($sql, [$search_term, $search_term, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Verifica disponibilitatea resursei
     */
    public function checkAvailability($resource_id, $required_quantity) {
        $sql = "SELECT CantitateDisponibila FROM Resursa WHERE id = ?";
        $stmt = $this->db->query($sql, [$resource_id]);
        $resource = $stmt->fetch();
        
        if ($resource) {
            return $resource['CantitateDisponibila'] >= $required_quantity;
        }
        
        return false;
    }
    
    /**
     * Obtine numarul total de resurse
     */
    public function getTotalResourcesCount() {
        $sql = "SELECT COUNT(*) as count FROM Resursa";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Obtine numarul de resurse grupate dupa tip
     */
    public function getResourcesByTypeCount() {
        $sql = "SELECT Tip, COUNT(*) as count FROM Resursa GROUP BY Tip";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine tipurile de resurse disponibile
     */
    public function getResourceTypes() {
        return ['detergent', 'apa', 'echipament'];
    }
    
    /**
     * Obtine toate resursele pentru export
     */
    public function getAllResourcesForExport() {
        $sql = "SELECT 
                    id,
                    Tip as type,
                    Nume as name,
                    CantitateDisponibila as available_quantity
                FROM Resursa 
                ORDER BY Tip, Nume";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
