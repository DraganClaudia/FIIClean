<?php
/**
 * LocationModel - simplified pentru operatiuni cu locatii/sedii
 * Adaptat pentru baza de date fiiclean existenta
 */
class LocationModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toate locatiile active
     */
    public function getAllLocations() {
        $sql = "SELECT s.*, COUNT(c.id) as total_comenzi
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                WHERE s.Stare IN ('activ', 'reparatii')
                GROUP BY s.id
                ORDER BY s.Nume";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine locatie dupa ID
     */
    public function getLocationById($id) {
        $sql = "SELECT * FROM Sediu WHERE id = ? AND Stare IN ('activ', 'reparatii')";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine locatii dupa tip serviciu
     */
    public function getLocationsByService($serviceType) {
        $sql = "SELECT DISTINCT s.* FROM Sediu s
                JOIN Comanda c ON s.id = c.idSediu
                WHERE c.TipServiciu = ? AND s.Stare = 'activ'";
        
        $stmt = $this->db->query($sql, [$serviceType]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine statistici pentru o locatie
     */
    public function getLocationStats($locationId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN DATE(DataProgramare) = CURDATE() THEN 1 END) as comenzi_astazi,
                    COUNT(CASE WHEN MONTH(DataProgramare) = MONTH(CURDATE()) THEN 1 END) as comenzi_luna,
                    COUNT(CASE WHEN YEAR(DataProgramare) = YEAR(CURDATE()) THEN 1 END) as comenzi_an,
                    AVG(CASE WHEN Status = 'finalizata' THEN 1 ELSE 0 END) * 100 as rata_finalizare
                FROM Comanda 
                WHERE idSediu = ?";
        
        $stmt = $this->db->query($sql, [$locationId]);
        return $stmt->fetch();
    }
    
    /**
     * Verifica daca locatia este operationala
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
     * Obtine resursele pentru o locatie
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
     * Obtine comenzile pentru o locatie
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
     * Obtine toate locatiile cu statistici
     */
    public function getAllLocationsWithStats() {
        $sql = "SELECT s.*, 
                    COUNT(c.id) as total_comenzi,
                    COUNT(CASE WHEN c.Status = 'noua' THEN 1 END) as comenzi_noi,
                    COUNT(CASE WHEN c.Status = 'in curs' THEN 1 END) as comenzi_in_curs,
                    COUNT(CASE WHEN c.Status = 'finalizata' THEN 1 END) as comenzi_finalizate,
                    AVG(CASE WHEN c.Status = 'finalizata' THEN 1 ELSE 0 END) * 100 as rata_eficienta
                FROM Sediu s
                LEFT JOIN Comanda c ON s.id = c.idSediu
                GROUP BY s.id
                ORDER BY s.Nume";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtine numarul total de locatii
     */
    public function getTotalLocations() {
        $sql = "SELECT COUNT(*) as count FROM Sediu WHERE Stare IN ('activ', 'reparatii')";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Obtine numarul de locatii active
     */
    public function getActiveLocationsCount() {
        $sql = "SELECT COUNT(*) as count FROM Sediu WHERE Stare = 'activ'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Obtine doar locatiile active
     */
    public function getActiveLocations() {
        $sql = "SELECT * FROM Sediu WHERE Stare = 'activ' ORDER BY Nume";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtine locatii dupa status
     */
    public function getLocationsByStatus($status) {
        $sql = "SELECT * FROM Sediu WHERE Stare = ? ORDER BY Nume";
        $stmt = $this->db->query($sql, [$status]);
        return $stmt->fetchAll();
    }

    /**
     * Creeaza locatie noua
     */
    public function createLocation($name, $address, $latitude = null, $longitude = null) {
        $sql = "INSERT INTO Sediu (Nume, Adresa, Latitudine, Longitudine, Stare) VALUES (?, ?, ?, ?, 'activ')";
        $stmt = $this->db->query($sql, [$name, $address, $latitude, $longitude]);
        return $this->db->lastInsertId();
    }

    /**
     * Actualizeaza locatie
     */
    public function updateLocation($id, $name, $address, $latitude = null, $longitude = null, $status = 'activ') {
        $sql = "UPDATE Sediu SET Nume = ?, Adresa = ?, Latitudine = ?, Longitudine = ?, Stare = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$name, $address, $latitude, $longitude, $status, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Sterge locatie
     */
    public function deleteLocation($id) {
        // Verifica daca locatia are comenzi inainte de stergere
        $check_sql = "SELECT COUNT(*) as count FROM Comanda WHERE idSediu = ?";
        $check_stmt = $this->db->query($check_sql, [$id]);
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            // Nu sterge, doar seteaza ca inactiva
            $sql = "UPDATE Sediu SET Stare = 'inactiv' WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        } else {
            // Sigur de sters
            $sql = "DELETE FROM Sediu WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        }
    }

    /**
     * Cauta locatii
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
     * Obtine toate locatiile pentru export
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
