<div class="auth-container">
    <div class="auth-box">
        <h2>Inregistrare</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required 
                   placeholder="Username (min 3 caractere)">
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   placeholder="adresa@email.com">

            <label for="password">Parola</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Parola (min 6 caractere)">
                   
            <label for="confirm_password">Confirma Parola</label>
            <input type="password" id="confirm_password" name="confirm_password" required 
                   placeholder="Confirma parola">

            <button type="submit">Inregistrare</button>
        </form>
        
        <div class="auth-links">
            <p>Aveti deja cont? <a href="?controller=auth&action=login">Conectati-va aici</a></p>
        </div>
    </div>
</div>

<script>
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    
    // Validare username
    if (username.length < 3) {
        alert('Username-ul trebuie sa aiba cel putin 3 caractere');
        return false;
    }
    
    // Validare email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Adresa de email nu este valida');
        return false;
    }
    
    // Validare parola
    if (password.length < 6) {
        alert('Parola trebuie sa aiba cel putin 6 caractere');
        return false;
    }
    
    // Verificare potrivire parole
    if (password !== confirmPassword) {
        alert('Parolele nu se potrivesc');
        return false;
    }
    
    return true;
}
</script>

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
