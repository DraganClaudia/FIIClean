<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$eroare = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $parola = $_POST['parola'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $eroare = "Adresa de email nu este validă.";
    } elseif (!preg_match('/[A-Z]/', $parola) || !preg_match('/[0-9]/', $parola)) {
        $eroare = "Parola trebuie să conțină cel puțin o literă mare și un număr.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM client WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $eroare = "Email sau username deja utilizate.";
        } else {
            $hash = password_hash($parola, PASSWORD_DEFAULT);
            $rol = 'user';
            $stmt = $mysqli->prepare("INSERT INTO client (username, email, parola, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hash, $rol);
            $stmt->execute();

            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['rol'] = $rol;

            header("Location: ../../index.php");
            exit;
        }
    }
}

include_once __DIR__ . '/../views/auth/register.php';
