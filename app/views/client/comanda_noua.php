<?php
/**
 * View pentru crearea unei comenzi noi - simplified version
 */
?>

<div class="container">
    <div class="page-header">
        <h1>Comanda Noua</h1>
        <div class="breadcrumb">
            <a href="?controller=client&action=dashboard">Dashboard</a> > <span class="active">Comanda Noua</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Formular Comanda -->
    <div class="section">
        <h2>Detalii Comanda</h2>
        
        <form id="comandaForm" method="POST" action="" class="comanda-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <!-- Selectare Serviciu -->
            <div class="form-section">
                <h3>Selectati Serviciul</h3>
                <div class="servicii-grid">
                    <div class="serviciu-option">
                        <input type="radio" id="serviciu_covor" name="tip_serviciu" value="covor" required>
                        <label for="serviciu_covor" class="serviciu-card">
                            <div class="serviciu-icon">🏠</div>
                            <h4>Spalare Covoare</h4>
                            <p>Curatare profesionala covoare si mochete</p>
                        </label>
                    </div>
                    
                    <div class="serviciu-option">
                        <input type="radio" id="serviciu_auto" name="tip_serviciu" value="auto" required>
                        <label for="serviciu_auto" class="serviciu-card">
                            <div class="serviciu-icon">🚗</div>
                            <h4>Spalare Auto</h4>
                            <p>Curatare completa vehicule</p>
                        </label>
                    </div>
                    
                    <div class="serviciu-option">
                        <input type="radio" id="serviciu_textil" name="tip_serviciu" value="textil" required>
                        <label for="serviciu_textil" class="serviciu-card">
                            <div class="serviciu-icon">👕</div>
                            <h4>Curatenie Textile</h4>
                            <p>Spalare si curatare imbracaminte</p>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Selectare Sediu -->
            <div class="form-section">
                <h3>Selectati Sediul</h3>
                <div class="sedii-grid">
                    <?php if (isset($sedii_active) && !empty($sedii_active)): ?>
                        <?php foreach ($sedii_active as $sediu): ?>
                            <div class="sediu-option">
                                <input type="radio" id="sediu_<?php echo $sediu['id']; ?>" 
                                       name="sediu_id" value="<?php echo $sediu['id']; ?>" required>
                                <label for="sediu_<?php echo $sediu['id']; ?>" class="sediu-card">
                                    <h4><?php echo htmlspecialchars($sediu['Nume']); ?></h4>
                                    <p><strong>Adresa:</strong> <?php echo htmlspecialchars($sediu['Adresa'] ?? 'N/A'); ?></p>
                                    <span class="sediu-status">Disponibil</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-sedii">
                            <p>Nu exista sedii disponibile.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Programare si Optiuni -->
            <div class="form-section">
                <h3>Programare si Optiuni</h3>
                <div class="programare-grid">
                    <div class="form-group">
                        <label for="data_programare">Data Programarii *</label>
                        <input type="date" id="data_programare" name="data_programare" required
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                
                <div class="optiuni-section">
                    <div class="optiune-card">
                        <input type="checkbox" id="transport" name="transport" value="1">
                        <label for="transport" class="optiune-label">
                            <div class="optiune-icon">🚚</div>
                            <div class="optiune-content">
                                <h4>Transport la Domiciliu</h4>
                                <p>Ridicare si livrare gratuita</p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="optiune-card">
                        <input type="checkbox" id="recurenta" name="recurenta" value="1">
                        <label for="recurenta" class="optiune-label">
                            <div class="optiune-icon">🔄</div>
                            <div class="optiune-content">
                                <h4>Comanda Recurenta</h4>
                                <p>Programeaza serviciul lunar</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actiuni Formular -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Plaseaza Comanda
                </button>
                <a href="?controller=client&action=dashboard" class="btn btn-secondary">
                    Anuleaza
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.servicii-grid, .sedii-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.serviciu-option input[type="radio"],
.sediu-option input[type="radio"] {
    display: none;
}

.serviciu-card, .sediu-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    text-align: center;
}

.serviciu-card:hover, .sediu-card:hover {
    border-color: #3498db;
    box-shadow: 0 4px 15px rgba(52,152,219,0.2);
}

.serviciu-option input[type="radio"]:checked + .serviciu-card,
.sediu-option input[type="radio"]:checked + .sediu-card {
    border-color: #3498db;
    background: #e3f2fd;
}

.serviciu-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.serviciu-card h4, .sediu-card h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.serviciu-card p, .sediu-card p {
    color: #666;
    margin-bottom: 1rem;
}

.sediu-status {
    background: #d4edda;
    color: #155724;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.programare-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.optiuni-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.optiune-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.optiune-card:has(input:checked) {
    border-color: #3498db;
    background: #e3f2fd;
}

.optiune-label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.optiune-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.optiune-content h4 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
}

.optiune-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 3rem;
}

.btn {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.no-sedii {
    text-align: center;
    padding: 3rem;
    color: #666;
}

@media (max-width: 768px) {
    .servicii-grid, .sedii-grid, .optiuni-section {
        grid-template-columns: 1fr;
    }
    
    .programare-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Validare formular
document.getElementById('comandaForm').addEventListener('submit', function(e) {
    const serviciu = this.querySelector('input[name="tip_serviciu"]:checked');
    const sediu = this.querySelector('input[name="sediu_id"]:checked');
    const data = this.querySelector('#data_programare').value;
    
    if (!serviciu) {
        e.preventDefault();
        alert('Va rugam sa selectati un serviciu.');
        return;
    }
    
    if (!sediu) {
        e.preventDefault();
        alert('Va rugam sa selectati un sediu.');
        return;
    }
    
    if (!data) {
        e.preventDefault();
        alert('Va rugam sa selectati o data pentru programare.');
        return;
    }
    
    // Verifica daca data e in viitor
    const selectedDate = new Date(data);
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (selectedDate < tomorrow) {
        e.preventDefault();
        alert('Data programarii trebuie sa fie de cel putin maine.');
        return;
    }
});
</script>
