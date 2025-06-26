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
        $sql = "SELECT * FROM client ORDER BY username";
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
        $sql = "INSERT INTO client (username, email, parola, rol) VALUES (?, ?, ?, 'user')";
        $defaultPassword = password_hash('defaultpass123', PASSWORD_DEFAULT);
        $stmt = $this->db->query($sql, [$nume, $email, $defaultPassword]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizeaza client
     */
    public function updateClient($id, $nume, $email, $telefon = '', $adresa = '') {
        $sql = "UPDATE client SET username = ?, email = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$nume, $email, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sterge client (doar daca nu are comenzi)
     */
    public function deleteClient($id) {
        // Verifica daca are comenzi
        $checkSql = "SELECT COUNT(*) as count FROM comanda WHERE idClient = ?";
        $checkStmt = $this->db->query($checkSql, [$id]);
        $result = $checkStmt->fetch();
    
        if ($result['count'] > 0) {
            return false; // Nu poate fi sters
        }
    
        $sql = "DELETE FROM client WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Cauta client dupa email
     */
    public function getClientByEmail($email) {
        $sql = "SELECT * FROM client WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine comenzile unui client
     */
    public function getClientComenzi($clientId) {
        $sql = "SELECT c.*, s.Nume as nume_sediu
                FROM comanda c
                LEFT JOIN sediu s ON c.idSediu = s.idSediu
                WHERE c.idClient = ?
                ORDER BY c.DataProgramare DESC";
    
        $stmt = $this->db->query($sql, [$clientId]);
        return $stmt->fetchAll();
    }
}
