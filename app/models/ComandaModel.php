<?php
/**
 * ComandaModel - operatiuni pentru comenzi
 */
class ComandaModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toate comenzile cu detalii
     */
    public function getAllComenzi() {
        $sql = "SELECT c.*, cl.Nume as nume_client, cl.Email as email_client, 
                       s.Nume as nume_sediu, s.Adresa as adresa_sediu
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                ORDER BY c.DataProgramare DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine comanda dupa ID
     */
    public function getComandaById($id) {
        $sql = "SELECT c.*, cl.Nume as nume_client, cl.Email as email_client,
                       s.Nume as nume_sediu, s.Adresa as adresa_sediu
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE c.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Creeaza comanda noua
     */
    public function createComanda($clientId, $sediuId, $tipServiciu, $dataProgramare, $recurenta = false, $transport = false) {
        $sql = "INSERT INTO Comanda (idClient, idSediu, TipServiciu, DataProgramare, Recurenta, Transport, Status) 
                VALUES (?, ?, ?, ?, ?, ?, 'noua')";
        
        $stmt = $this->db->query($sql, [
            $clientId, $sediuId, $tipServiciu, $dataProgramare, 
            $recurenta ? 1 : 0, $transport ? 1 : 0
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza status comanda
     */
    public function updateComandaStatus($id, $status) {
        $sql = "UPDATE Comanda SET Status = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$status, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Obtine comenzi dupa status
     */
    public function getComenziByStatus($status) {
        $sql = "SELECT c.*, cl.Nume as nume_client, s.Nume as nume_sediu
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE c.Status = ?
                ORDER BY c.DataProgramare DESC";
        
        $stmt = $this->db->query($sql, [$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine comenzi pentru un sediu
     */
    public function getComenziForSediu($sediuId, $limit = null) {
        $sql = "SELECT c.*, cl.Nume as nume_client
                FROM Comanda c
                LEFT JOIN Client cl ON c.idClient = cl.id
                WHERE c.idSediu = ?
                ORDER BY c.DataProgramare DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->db->query($sql, [$sediuId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Statistici comenzi
     */
    public function getStatsGeneral() {
        $sql = "SELECT 
                    COUNT(*) as total_comenzi,
                    COUNT(CASE WHEN Status = 'noua' THEN 1 END) as comenzi_noi,
                    COUNT(CASE WHEN Status = 'in curs' THEN 1 END) as comenzi_in_curs,
                    COUNT(CASE WHEN Status = 'finalizata' THEN 1 END) as comenzi_finalizate,
                    COUNT(CASE WHEN DATE(DataProgramare) = CURDATE() THEN 1 END) as comenzi_astazi
                FROM Comanda";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Sterge comanda
     */
    public function deleteComanda($id) {
        // Sterge intai consumurile asociate
        $sql1 = "DELETE FROM Consum WHERE idComanda = ?";
        $this->db->query($sql1, [$id]);
        
        // Apoi sterge comanda
        $sql2 = "DELETE FROM Comanda WHERE id = ?";
        $stmt = $this->db->query($sql2, [$id]);
        return $stmt->rowCount() > 0;
    }
}
