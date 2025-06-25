<?php
// app/views/public/contact.php - View Template pentru pagina de contact
// Nu mai include manual clasele - sunt gestionate de index.php
?>

<div class="container">
    <section class="contact-section">
        <div class="contact-header">
            <h1>Contactează-ne</h1>
            <p class="contact-subtitle">Suntem aici să te ajutăm cu orice întrebare sau cerere specială</p>
        </div>

        <div class="contact-content">
            <div class="contact-info">
                <h3>Informații de Contact</h3>
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-text">
                            <h4>Adresa</h4>
                            <p>Strada Principală nr. 123<br>București, România</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-text">
                            <h4>Telefon</h4>
                            <p>+40 721 123 456</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p>contact@cas-cleaning.ro</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕐</div>
                        <div class="contact-text">
                            <h4>Program</h4>
                            <p>Luni - Vineri: 08:00 - 18:00<br>Sâmbătă: 09:00 - 14:00</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-form-container">
                <h3>Trimite-ne un mesaj</h3>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form id="contactForm" method="POST" action="?controller=public&action=contact" class="contact-form">
                    <?php if (isset($csrf_token)): ?>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nume complet *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required 
                                   value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                                   placeholder="Introduceți numele complet">
                            <div id="name-error" class="error-message"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Adresa de email *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   placeholder="nume@email.com">
                            <div id="email-error" class="error-message"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Număr de telefon</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                               placeholder="+40 XXX XXX XXX">
                        <div id="phone-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subiect *</label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               required 
                               value="<?php echo htmlspecialchars($form_data['subject'] ?? ''); ?>"
                               placeholder="Subiectul mesajului">
                        <div id="subject-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Mesaj *</label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required 
                                  placeholder="Scrie aici mesajul tău..."><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                        <div id="message-error" class="error-message"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="btn-text">Trimite Mesajul</span>
                            <span class="btn-loading" style="display: none;">Se trimite...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<style>
.contact-section {
    padding: 2rem 0;
}

.contact-header {
    text-align: center;
    margin-bottom: 3rem;
}

.contact-header h1 {
    font-size: 2.5rem;
    color: #1e3a8a;
    margin-bottom: 1rem;
}

.contact-subtitle {
    font-size: 1.2rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

.contact-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-info h3,
.contact-form-container h3 {
    color: #1e3a8a;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.contact-details {
    space-y: 2rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.contact-icon {
    font-size: 2rem;
    margin-right: 1rem;
    margin-top: 0.25rem;
}

.contact-text h4 {
    color: #1e3a8a;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.contact-text p {
    color: #64748b;
    line-height: 1.6;
}

.contact-form {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #1e3a8a;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2563eb;
}

.form-group input.error,
.form-group textarea.error {
    border-color: #dc2626;
}

.error-message {
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: none;
}

.form-actions {
    text-align: center;
    margin-top: 2rem;
}

.btn-loading {
    display: none;
}

@media (max-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-form {
        padding: 1.5rem;
    }
}
</style>
