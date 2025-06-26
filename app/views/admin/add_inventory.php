<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Verifică dacă utilizatorul este admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSediu = (int) $_POST['idSediu'];
    $tip = $_POST['tip'];
    $nume = trim($_POST['nume']);
    $cantitate = (int) $_POST['cantitate'];

    // Verificare dacă sediul există
    $checkSediu = $mysqli->prepare("SELECT id FROM resursa WHERE id = ?");
    $checkSediu->bind_param("i", $idSediu);
    $checkSediu->execute();
    $checkSediu->store_result();

    if ($checkSediu->num_rows === 0) {
        $mesaj = "Eroare: Sediul cu ID-ul $idSediu nu există.";
    } elseif (!in_array($tip, ['detergent', 'apa', 'echipament']) || empty($nume) || $cantitate < 0) {
        $mesaj = "Date invalide. Verifică toate câmpurile.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO resursa (idSediu, Tip, Nume, CantitateDisponibila) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $idSediu, $tip, $nume, $cantitate);
        $stmt->execute();

        $mesaj = "Resursa a fost adăugată cu succes!";
    }
}


include_once __DIR__ . '/../layouts/header.php';
?>

<div class="inventory-form-container">
    <h2>Adaugă Resursă în Inventar</h2>

    <?php if ($mesaj): ?>
        <div class="alert"><?= htmlspecialchars($mesaj) ?></div>
    <?php endif; ?>

    <form method="post" class="inventory-form">
        <label for="idSediu">ID Sediu:</label>
        <input type="number" name="idSediu" id="idSediu" required min="1">

        <label for="tip">Tip Resursă:</label>
        <select name="tip" id="tip" required>
            <option value="">-- Selectează --</option>
            <option value="detergent">Detergent</option>
            <option value="apa">Apă</option>
            <option value="echipament">Echipament</option>
        </select>

        <label for="nume">Nume Resursă:</label>
        <input type="text" name="nume" id="nume" required>

        <label for="cantitate">Cantitate Disponibilă:</label>
        <input type="number" name="cantitate" id="cantitate" required min="0">

        <button type="submit">Adaugă în Inventar</button>
    </form>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
