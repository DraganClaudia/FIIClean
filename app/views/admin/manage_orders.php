<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Restricționează accesul doar pentru admini
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Preia comenzile
$result = $mysqli->query("SELECT * FROM comanda ORDER BY DataProgramare DESC");

include_once __DIR__ . '/../layouts/header.php';
?>

<div class="admin-container">
<h2 class="admin-title">Administrare Comenzi</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <td><?= htmlspecialchars($comanda['idComanda']) ?></td>
                <td><?= htmlspecialchars($comanda['idClient']) ?></td>
                <td><?= htmlspecialchars($comanda['idSediu']) ?></td>
                <td><?= htmlspecialchars($comanda['NumeClient']) ?></td>
                <td><?= htmlspecialchars($comanda['TipServiciu']) ?></td>
                <td><?= htmlspecialchars($comanda['Cantitate']) ?></td>
                <td><?= htmlspecialchars($comanda['DataProgramare']) ?></td>
                <td><?= $comanda['Recurenta'] ? 'Da' : 'Nu' ?></td>
                <td><?= $comanda['Transport'] ? 'Da' : 'Nu' ?></td>
                <td><?= htmlspecialchars($comanda['Status']) ?></td>
                <td class="actiuni-cell">
                <form method="post" action="../../action/update_comanda.php">
                    <input type="hidden" name="idComanda" value="<?= $comanda['idComanda'] ?>">
                    <div class="actiuni-form">
                    <select name="status">
                        <option <?= $comanda['Status'] === 'noua' ? 'selected' : '' ?>>noua</option>
                        <option <?= $comanda['Status'] === 'in curs' ? 'selected' : '' ?>>in curs</option>
                        <option <?= $comanda['Status'] === 'finalizata' ? 'selected' : '' ?>>finalizata</option>
                        <option <?= $comanda['Status'] === 'refuzata' ? 'selected' : '' ?>>refuzata</option>
                    </select>
                    <button type="submit" class="btn-update">Actualizează</button>
                    </div>
                </form>
                </td>
            </tr>
        </thead>
        <tbody>
            <?php while ($comanda = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($comanda['idComanda']) ?></td>
                    <td><?= htmlspecialchars($comanda['idClient']) ?></td>
                    <td><?= htmlspecialchars($comanda['idSediu']) ?></td>
                    <td><?= htmlspecialchars($comanda['NumeClient']) ?></td>
                    <td><?= htmlspecialchars($comanda['TipServiciu']) ?></td>
                    <td><?= htmlspecialchars($comanda['Cantitate']) ?></td>
                    <td><?= htmlspecialchars($comanda['DataProgramare']) ?></td>
                    <td><?= $comanda['Recurenta'] ? 'Da' : 'Nu' ?></td>
                    <td><?= $comanda['Transport'] ? 'Da' : 'Nu' ?></td>
                    <td><?= htmlspecialchars($comanda['Status']) ?></td>
                    <td class="actiuni-cell">
                    <form method="post" action="../../action/update_comanda.php">
                        <input type="hidden" name="idComanda" value="<?= $comanda['idComanda'] ?>">
                        <div class="actiuni-form">
                        <select name="status">
                            <option <?= $comanda['Status'] === 'noua' ? 'selected' : '' ?>>noua</option>
                            <option <?= $comanda['Status'] === 'in curs' ? 'selected' : '' ?>>in curs</option>
                            <option <?= $comanda['Status'] === 'finalizata' ? 'selected' : '' ?>>finalizata</option>
                            <option <?= $comanda['Status'] === 'refuzata' ? 'selected' : '' ?>>refuzata</option>
                        </select>
                        <button type="submit" class="btn-update">Actualizează</button>
                        </div>
                    </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
