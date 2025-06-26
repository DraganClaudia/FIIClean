<?php
/**
 * ResourceModel - simplified pentru operatiuni cu resurse
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
        $sql = "SELECT r.*, s.Nume as nume_sediu FROM resursa r 
                LEFT JOIN sediu s ON r.idSediu = s.idSediu 
                ORDER BY r.Tip, r.Nume";
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
    public function createResource($sediuId, $type, $name, $quantity) {
        $sql = "INSERT INTO resursa (idSediu, Tip, Nume, CantitateDisponibila) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->query($sql, [$sediuId, $type, $name, $quantity]);
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
     * Obtine tipurile de resurse disponibile
     */
    public function getResourceTypes() {
        $sql = "SELECT DISTINCT Tip FROM Resursa ORDER BY Tip";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll();
        return array_column($result, 'Tip');
    }
}
