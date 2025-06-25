<?php
/**
 * LocationModel - handles location data operations
 * Adapted for existing fiiclean database structure
 */
class LocationModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all active locations (Sediu table)
     */
    public function getAllLocations() {
        $sql = "SELECT s.*, 
                       COUNT(c.id) as total_orders,
                       AVG(CASE WHEN c.Status = 'finalizata' THEN 1 ELSE 0 END) as efficiency_rate
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                WHERE s.Stare IN ('activ', 'reparatii')
                GROUP BY s.id
                ORDER BY s.Nume";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get location by ID
     */
    public function getLocationById($id) {
        $sql = "SELECT * FROM Sediu WHERE id = ? AND Stare IN ('activ', 'reparatii')";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get locations by service type
     */
    public function getLocationsByService($serviceType) {
        $sql = "SELECT DISTINCT s.* FROM Sediu s
                JOIN Comanda c ON s.id = c.idSediu
                WHERE c.TipServiciu = ? AND s.Stare = 'activ'";
        
        $stmt = $this->db->query($sql, [$serviceType]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get location statistics
     */
    public function getLocationStats($locationId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN DATE(DataProgramare) = CURDATE() THEN 1 END) as orders_today,
                    COUNT(CASE WHEN MONTH(DataProgramare) = MONTH(CURDATE()) THEN 1 END) as orders_month,
                    COUNT(CASE WHEN YEAR(DataProgramare) = YEAR(CURDATE()) THEN 1 END) as orders_year,
                    AVG(CASE WHEN Status = 'finalizata' THEN 1 ELSE 0 END) * 100 as completion_rate
                FROM Comanda 
                WHERE idSediu = ?";
        
        $stmt = $this->db->query($sql, [$locationId]);
        return $stmt->fetch();
    }
    
    /**
     * Check if location is operational
     */
    public function isLocationOperational($locationId) {
        $sql = "SELECT s.*, 
                       CASE 
                           WHEN s.Stare = 'reparatii' THEN 'maintenance'
                           WHEN s.Stare = 'inactiv' THEN 'inactive'
                           ELSE 'operational'
                       END as operational_status
                FROM Sediu s
                WHERE s.id = ?";
        
        $stmt = $this->db->query($sql, [$locationId]);
        return $stmt->fetch();
    }
    
    /**
     * Get resource consumption for location
     */
    public function getLocationResources($locationId) {
        $sql = "SELECT r.Tip, r.Nume, r.CantitateDisponibila,
                       COALESCE(SUM(con.Cantitate), 0) as consum_total
                FROM Resursa r
                LEFT JOIN Consum con ON r.id = con.idResursa
                LEFT JOIN Comanda c ON con.idComanda = c.id
                WHERE c.idSediu = ? OR c.idSediu IS NULL
                GROUP BY r.id, r.Tip, r.Nume, r.CantitateDisponibila";
        
        $stmt = $this->db->query($sql, [$locationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get orders for location
     */
    public function getLocationOrders($locationId, $limit = 10) {
        $sql = "SELECT c.*, cl.Nume as client_name, cl.Email as client_email
                FROM Comanda c
                JOIN Client cl ON c.idClient = cl.id
                WHERE c.idSediu = ?
                ORDER BY c.DataProgramare DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$locationId, $limit]);
        return $stmt->fetchAll();
    }

    /**
    * Get all locations with statistics
     */
    public function getAllLocationsWithStats() {
      $sql = "SELECT s.*, 
                COUNT(c.id) as total_orders,
                COUNT(CASE WHEN c.Status = 'noua' THEN 1 END) as new_orders,
                COUNT(CASE WHEN c.Status = 'in curs' THEN 1 END) as in_progress_orders,
                COUNT(CASE WHEN c.Status = 'finalizata' THEN 1 END) as completed_orders,
                   VG(CASE WHEN c.Status = 'finalizata' THEN 1 ELSE 0 END) * 100 as efficiency_rate
            FROM Sediu s
            LEFT JOIN Comanda c ON s.id = c.idSediu
            GROUP BY s.id
            ORDER BY s.Nume";
    
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get total locations count
 */
    public function getTotalLocations() {
        $sql = "SELECT COUNT(*) as count FROM Sediu WHERE Stare IN ('activ', 'reparatii')";
       $stmt = $this->db->query($sql);
       $result = $stmt->fetch();
       return $result['count'] ?? 0;
    }

/**
 * Get active locations count
 */
    public function getActiveLocationsCount() {
        $sql = "SELECT COUNT(*) as count FROM Sediu WHERE Stare = 'activ'";
        $stmt = $this->db->query($sql);
       $result = $stmt->fetch();
       return $result['count'] ?? 0;
    }

/**
 * Get active locations only
 */
public function getActiveLocations() {
    $sql = "SELECT * FROM Sediu WHERE Stare = 'activ' ORDER BY Nume";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get locations by status
 */
public function getLocationsByStatus($status) {
    $sql = "SELECT * FROM Sediu WHERE Stare = ? ORDER BY Nume";
    $stmt = $this->db->query($sql, [$status]);
    return $stmt->fetchAll();
}

/**
 * Create new location
 */
public function createLocation($name, $address, $latitude = null, $longitude = null) {
    $sql = "INSERT INTO Sediu (Nume, Adresa, Latitudine, Longitudine, Stare) VALUES (?, ?, ?, ?, 'activ')";
    $stmt = $this->db->query($sql, [$name, $address, $latitude, $longitude]);
    return $this->db->lastInsertId();
}

/**
 * Update location
 */
public function updateLocation($id, $name, $address, $latitude = null, $longitude = null, $status = 'activ') {
    $sql = "UPDATE Sediu SET Nume = ?, Adresa = ?, Latitudine = ?, Longitudine = ?, Stare = ? WHERE id = ?";
    $stmt = $this->db->query($sql, [$name, $address, $latitude, $longitude, $status, $id]);
    return $stmt->rowCount() > 0;
}

/**
 * Delete location
 */
public function deleteLocation($id) {
    // Check if location has orders before deleting
    $check_sql = "SELECT COUNT(*) as count FROM Comanda WHERE idSediu = ?";
    $check_stmt = $this->db->query($check_sql, [$id]);
    $result = $check_stmt->fetch();
    
    if ($result['count'] > 0) {
        // Don't delete, just set as inactive
        $sql = "UPDATE Sediu SET Stare = 'inactiv' WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    } else {
        // Safe to delete
        $sql = "DELETE FROM Sediu WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}

/**
 * Search locations
 */
public function searchLocations($query, $limit = 20) {
    $sql = "SELECT * FROM Sediu 
            WHERE Nume LIKE ? OR Adresa LIKE ?
            ORDER BY Nume
            LIMIT ?";
    
    $search_term = '%' . $query . '%';
    $stmt = $this->db->query($sql, [$search_term, $search_term, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get all locations for export
 */
public function getAllLocationsForExport() {
    $sql = "SELECT 
                id,
                Nume as name,
                Adresa as address,
                Latitudine as latitude,
                Longitudine as longitude,
                Stare as status
            FROM Sediu 
            ORDER BY Nume";
    
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
    }
}
