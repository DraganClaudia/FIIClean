<?php
function start_template($title = "Aplicatie Spalatorie") {
    echo <<<HTML
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>$title</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9f9f9; }
        header, footer { background: #333; color: white; padding: 10px 20px; }
        nav a { margin-right: 15px; color: white; text-decoration: none; }
        nav a:hover { text-decoration: underline; }
        main { padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <header>
        <h1>Management Spalatorii</h1>
        <nav>
            <a href="index.php">Acasa</a>
            <a href="adauga_item.php">Adauga Item</a>
            <a href="list_inventar.php">Inventar</a>
            <a href="adauga_comanda.php">Comenzi</a>
        </nav>
    </header>
    <main>
    <div class="container">
HTML;
}

function end_template() {
    echo <<<HTML
    </div>
    </main>
    <footer>
        <p>&copy; 2025 Aplicatie PHP-MySQL – Facultatea de Informatica</p>
    </footer>
</body>
</html>
HTML;
}
?>
