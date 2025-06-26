<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

// Doar utilizatori autentificați cu rol user
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header("Location: ../../index.php");
    exit;
}

include_once __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <h2>Bine ai venit, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

    <div class="dashboard-buttons">
        <a href="/CaS_FII-Clean/app/views/client/add_order.php" class="dashboard-btn">Plasează Comandă</a>
        <a href="/CaS_FII-Clean/app/views/client/my_orders.php" class="dashboard-btn">Comenzile Mele</a>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
