<?php
require_once __DIR__ . '/../config/db.php';

include_once __DIR__ . '/../views/layouts/header.php';
?>

<main class="container">
    <h2>Lista Itemelor din Inventar</h2>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>id</th>
                <th>Tip</th>
                <th>Nume</th>
                <th>CantitateDisponibila</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $result = $mysqli->query("SELECT * FROM resursa");

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['Tip']}</td>
                        <td>{$row['Nume']}</td>
                        <td>{$row['CantitateDisponibila']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>Nu există înregistrări în inventar.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</main>

<?php
include_once __DIR__ . '/../views/layouts/footer.php';
?>
