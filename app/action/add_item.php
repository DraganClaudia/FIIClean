<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $depId = $_POST['depId'];
    $itemId = $_POST['itemId'];
    $numeItem = $_POST['numeItem'];
    $cantitate = $_POST['cantitate'];
    $lastChecked = $_POST['lastChecked'];

    $sql = "INSERT INTO Inventar (id, depId, itemId, NumeItem, Cantitate, LastChecked)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iiisis", $id, $depId, $itemId, $numeItem, $cantitate, $lastChecked);

    if ($stmt->execute()) {
        echo "Item adăugat cu succes!";
    } else {
        echo "Eroare la inserare: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adaugă Item în Inventar</title>
</head>
<body>
    <h2>Formular adăugare item</h2>
    <form method="post" action="">
        ID: <input type="number" name="id" required><br>
        Depozit ID: <input type="number" name="depId" required><br>
        Item ID: <input type="number" name="itemId" required><br>
        Nume Item: <input type="text" name="numeItem" required><br>
        Cantitate: <input type="number" name="cantitate" required><br>
        Last Checked: <input type="date" name="lastChecked" required><br>
        <input type="submit" value="Adaugă">
    </form>
</body>
</html>
