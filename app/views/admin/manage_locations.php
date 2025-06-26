<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Actualizare stare locație
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idSediu'], $_POST['stare'])) {
    $stmt = $mysqli->prepare("UPDATE sediu SET Stare = ? WHERE id = ?");
    $stmt->bind_param("si", $_POST['stare'], $_POST['idSediu']);
    $stmt->execute();
    header("Location: manage_locations.php");
    exit;
}

// Preluare locații existente
$result = $mysqli->query("SELECT * FROM sediu");

include_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <h2 style="text-align:center;">Administrare Locații</h2>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nume</th>
                <th>Stare</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($loc = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($loc['idSediu']) ?></td>
                    <td><?= htmlspecialchars($loc['Nume']) ?></td>
                    <td><?= htmlspecialchars($loc['Stare']) ?></td>
                    <td class="actiuni-cell">
                    <form method="post" action="" class="actiuni-form">
                    <input type="hidden" name="idSediu" value="<?= $loc['idSediu'] ?>">
                    <select name="stare">
                    <option value="activ" <?= $loc['Stare'] === 'activ' ? 'selected' : '' ?>>Activ</option>
                    <option value="reparatii" <?= $loc['Stare'] === 'reparatii' ? 'selected' : '' ?>>În reparații</option>
                    </select>
                    <button type="submit" class="btn-update">Actualizează</button>
                    </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
