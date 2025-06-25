<?php
/**
 * ClientModel - operatiuni pentru clienti
 */
class ClientModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtine toti clientii
     */
    public function getAllClienti() {
        $sql = "SELECT * FROM Client ORDER BY Nume";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtine client dupa ID
     */
    public function getClientById($id) {
        $sql = "SELECT * FROM Client WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Creeaza client nou
     */
    public function createClient($nume, $email, $telefon = '', $adresa = '') {
        $sql = "INSERT INTO Client (Nume, Email, Telefon, Adresa) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->query($sql, [$nume, $email, $telefon, $adresa]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza client
     */
    public function updateClient($id, $nume, $email, $telefon = '', $adresa = '') {
        $sql = "UPDATE Client SET Nume = ?, Email = ?, Telefon = ?, Adresa = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$nume, $email, $telefon, $adresa, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sterge client (doar daca nu are comenzi)
     */
    public function deleteClient($id) {
        // Verifica daca are comenzi
        $checkSql = "SELECT COUNT(*) as count FROM Comanda WHERE idClient = ?";
        $checkStmt = $this->db->query($checkSql, [$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            return false; // Nu poate fi sters
        }
        
        $sql = "DELETE FROM Client WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Cauta client dupa email
     */
    public function getClientByEmail($email) {
        $sql = "SELECT * FROM Client WHERE Email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine comenzile unui client
     */
    public function getClientComenzi($clientId) {
        $sql = "SELECT c.*, s.Nume as nume_sediu
                FROM Comanda c
                LEFT JOIN Sediu s ON c.idSediu = s.id
                WHERE c.idClient = ?
                ORDER BY c.DataProgramare DESC";
        
        $stmt = $this->db->query($sql, [$clientId]);
        return $stmt->fetchAll();
    }
}
