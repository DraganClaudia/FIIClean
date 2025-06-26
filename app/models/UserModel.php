<?php
/**
 * UserModel - operatiuni pentru utilizatori
 */
class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Autentifica utilizator
     */
    public function authenticateUser($username, $password) {
        $sql = "SELECT id, username, email, parola, rol FROM client WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($password, $user['parola'])) {
            unset($user['parola']);
            return $user;
        }
    
        return false;
    }
    
    /**
     * Creeaza utilizator nou
     */
    public function createUser($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "INSERT INTO client (username, email, parola, rol) VALUES (?, ?, ?, 'user')";
        $stmt = $this->db->query($sql, [$username, $email, $hashedPassword]);
    
        return $this->db->lastInsertId();
    }
    
    /**
     * Verifica daca utilizatorul exista
     */
    public function userExists($username, $email) {
        $sql = "SELECT id FROM client WHERE username = ? OR email = ?";
        $stmt = $this->db->query($sql, [$username, $email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Obtine utilizator dupa ID
     */
        public function getUserById($id) {
        $sql = "SELECT id, username, email, rol FROM client WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtine toti utilizatorii
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, email, rol FROM client ORDER BY username";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizeaza utilizator
     */
    public function updateUser($id, $username, $email, $password = null) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE client SET username = ?, email = ?, parola = ? WHERE id = ?";
            $params = [$username, $email, $hashedPassword, $id];
        } else {
            $sql = "UPDATE client SET username = ?, email = ? WHERE id = ?";
            $params = [$username, $email, $id];
        }
    
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Sterge utilizator
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM client WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
