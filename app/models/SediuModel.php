<?php
/**
 * SediuModel - operatiuni baza de date pentru sedii
 */
class SediuModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toate sediile
     */
    public function getAllSedii() {
        $sql = "SELECT s.*, COUNT(c.idComanda) as total_comenzi
                FROM sediu s
                LEFT JOIN comanda c ON s.idSediu = c.idSediu
                GROUP BY s.idSediu
                ORDER BY s.Nume";
    
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine sediu dupa ID
     */
    public function getSediuById($id) {
        $sql = "SELECT * FROM sediu WHERE idSediu = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine statistici pentru un sediu
     */
    public function getSediuStats($sediuId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN DATE(DataProgramare) = CURDATE() THEN 1 END) as comenzi_astazi,
                    COUNT(CASE WHEN MONTH(DataProgramare) = MONTH(CURDATE()) THEN 1 END) as comenzi_luna,
                    COUNT(CASE WHEN YEAR(DataProgramare) = YEAR(CURDATE()) THEN 1 END) as comenzi_an,
                    AVG(CASE WHEN Status = 'finalizata' THEN 1 ELSE 0 END) * 100 as rata_finalizare
                FROM comanda 
                WHERE idSediu = ?";
    
        $stmt = $this->db->query($sql, [$sediuId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine sedii active
     */
    public function getSediiActive() {
        $sql = "SELECT * FROM Sediu WHERE Stare = 'activ' ORDER BY Nume";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Adauga sediu nou
     */
    public function addSediu($nume, $adresa, $latitudine = null, $longitudine = null) {
        $sql = "INSERT INTO sediu (Nume, Adresa, Latitudine, Longitudine, Stare) 
                VALUES (?, ?, ?, ?, 'activ')";
    
        $stmt = $this->db->query($sql, [$nume, $adresa, $latitudine, $longitudine]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza sediu
     */
    public function updateSediu($id, $nume, $adresa, $stare, $latitudine = null, $longitudine = null) {
        $sql = "UPDATE sediu SET Nume = ?, Adresa = ?, Stare = ?, Latitudine = ?, Longitudine = ? 
                WHERE idSediu = ?";
    
        $stmt = $this->db->query($sql, [$nume, $adresa, $stare, $latitudine, $longitudine, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sterge sediu (doar daca nu are comenzi)
     */
    public function deleteSediu($id) {
        // Verifica daca are comenzi
        $checkSql = "SELECT COUNT(*) as count FROM comanda WHERE idSediu = ?";
        $checkStmt = $this->db->query($checkSql, [$id]);
        $result = $checkStmt->fetch();
    
        if ($result['count'] > 0) {
            // Nu sterge, doar dezactiveaza
            return $this->updateSediuStatus($id, 'reparatii');
        } else {
            $sql = "DELETE FROM sediu WHERE idSediu = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        }
    }
    
    /**
     * Actualizeaza status sediu
     */
    public function updateSediuStatus($id, $stare) {
        $sql = "UPDATE sediu SET Stare = ? WHERE idSediu = ?";
        $stmt = $this->db->query($sql, [$stare, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Cauta sedii
     */
    public function searchSedii($query) {
        $sql = "SELECT * FROM sediu 
                WHERE Nume LIKE ? OR Adresa LIKE ?
                ORDER BY Nume";
    
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Da sedii dupa stare
     */
    public function getSediiByStare($stare) {
        $sql = "SELECT s.*, COUNT(c.idComanda) as total_comenzi
                FROM sediu s
                LEFT JOIN comanda c ON s.idSediu = c.idSediu
                WHERE s.Stare = ?
                GROUP BY s.idSediu
                ORDER BY s.Nume";
    
        $stmt = $this->db->query($sql, [$stare]);
        return $stmt->fetchAll();
    }
}
