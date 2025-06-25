<div class="auth-container">
    <div class="auth-box">
        <h2>Autentificare</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required 
                   placeholder="Introduceti username-ul">

            <label for="password">Parola</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Introduceti parola">

            <button type="submit">Login</button>
        </form>
        
        <div class="auth-links">
            <p>Nu aveti cont? <a href="?controller=auth&action=register">Inregistrati-va aici</a></p>
        </div>
    </div>
</div>

<style>
.auth-links {
    text-align: center;
    margin-top: 1rem;
}

.auth-links a {
    color: #60a5fa;
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}
</style>
