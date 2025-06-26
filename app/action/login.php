<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$eroare = null;

// Redirecționează dacă ești deja logat
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $parola = $_POST['parola'];

    // Căutare după username
    $stmt = $mysqli->prepare("SELECT id, parola, rol, username FROM client WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $rol, $fetchedUsername);
            $stmt->fetch();

            if (password_verify($parola, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $fetchedUsername;
                $_SESSION['rol'] = $rol;

                if ($rol === 'admin') {
                    header("Location: ../../index.php?controller=admin&action=dashboard");
                } else {
                    header("Location: ../../index.php?controller=client&action=dashboard");
                }
                exit;
            } else {
                $eroare = "Parolă incorectă.";
            }
        } else {
            $eroare = "Username inexistent.";
        }
        $stmt->close();
    } else {
        $eroare = "Eroare la interogarea bazei de date.";
    }
}

include_once __DIR__ . '/../views/auth/login.php';
