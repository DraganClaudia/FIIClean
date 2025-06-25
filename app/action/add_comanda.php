<?php
include 'db.php';
$nume = $_POST['nume'];
$data = $_POST['data'];
$mysqli->query("INSERT INTO Comenzi (idComanda, DataInceput, NumeClient, Status) 
VALUES (NULL, '$data', '$nume', 'in asteptare')");
echo "Comandă adăugată.";
?>
