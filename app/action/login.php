<?php
require_once __DIR__ . '/../config/db.php';

$eroare = null;

// Dacă utilizatorul e deja autentificat, redirecționează
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $parola = $_POST['parola'];

    $stmt = $mysqli->prepare("SELECT id, parola FROM utilizator WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();
        if (password_verify($parola, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: ../../index.php");
            exit;
        } else {
            $eroare = "Parolă incorectă.";
        }
    } else {
        $eroare = "Utilizator inexistent.";
    }
}

include_once __DIR__ . '/../views/auth/login.php';
?>