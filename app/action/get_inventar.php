<?php
include 'db.php';

$result = $conn->query("SELECT * FROM resursa");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
