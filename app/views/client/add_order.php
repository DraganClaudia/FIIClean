<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../../index.php");
    exit;
}

include_once __DIR__ . '/../layouts/header.php';

$mesaj = '';
$eroare = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSediu = (int)$_POST['idSediu'];
    $tipServiciu = $_POST['tip_serviciu'];
    $cantitate = (int)$_POST['cantitate'];
    $idClient = $_SESSION['user_id'];
    $data = date('Y-m-d');
    $recurenta = 0;
    $transport = 0;

    // Verifică dacă sediul există
    $check = $mysqli->prepare("SELECT Stare FROM sediu WHERE idSediu = ?");
    $check->bind_param("i", $idSediu);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $eroare = "ID-ul sediului introdus nu există.";
    } else {
        $check->bind_result($stare);
        $check->fetch();
        if ($stare === 'reparatii') {
            $eroare = "Sediul selectat este în reparații. Te rugăm să alegi alt sediu.";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO comanda (idClient, idSediu, TipServiciu, DataProgramare, Recurenta, Transport, Status, Cantitate) VALUES (?, ?, ?, ?, ?, ?, 'noua', ?)");
            $stmt->bind_param("iissiii", $idClient, $idSediu, $tipServiciu, $data, $recurenta, $transport, $cantitate);
            $stmt->execute();
            $mesaj = "Comanda a fost plasată cu succes!";
        }
    }
}
?>

<div class="order-container">
    <h2>Plasează o Comandă</h2>

    <?php if ($mesaj): ?>
        <p class="success-msg"><?= htmlspecialchars($mesaj) ?></p>
    <?php elseif ($eroare): ?>
        <p class="error-msg"><?= htmlspecialchars($eroare) ?></p>
    <?php endif; ?>

    <form method="post" class="order-form">
        <label for="idSediu">ID Sediu:</label>
        <input type="number" name="idSediu" required>

        <label for="tip_serviciu">Serviciu:</label>
        <select name="tip_serviciu" required>
            <option value="covoare">Covoare</option>
            <option value="autoturisme">Autoturisme</option>
            <option value="imbracaminte">Îmbrăcăminte</option>
        </select>

        <label for="cantitate">Cantitate / Număr:</label>
        <input type="number" name="cantitate" min="1" required>

        <button type="submit" class="order-btn">Trimite comanda</button>
    </form>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
