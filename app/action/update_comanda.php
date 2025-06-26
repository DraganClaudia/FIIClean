<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Doar adminul poate face modificarea
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Verifică dacă s-au trimis datele necesare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idComanda']) && isset($_POST['status'])) {
    $id = intval($_POST['idComanda']);
    $status = trim($_POST['status']);

    // Validare opțională
    $statusuri_permise = ['noua', 'in curs', 'finalizata', 'anulata'];
    if (!in_array($status, $statusuri_permise)) {
        die("Status invalid.");
    }

    $stmt = $mysqli->prepare("UPDATE comanda SET Status = ? WHERE idComanda = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    header("Location: ../views/admin/manage_orders.php");
    exit;
} else {
    echo "Date lipsă.";
}
?>
