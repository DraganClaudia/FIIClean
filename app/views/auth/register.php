<?php include_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Înregistrare</h2>
        <?php if (!empty($eroare)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($eroare); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="parola">Parolă</label>
            <input type="password" id="parola" name="parola" required>

            <button type="submit">Înregistrează-te</button>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
