<?php
/**
 * View pentru listarea comenzilor clientului - simplified version
 */
?>

<div class="container">
    <div class="page-header">
        <h1>Comenzile Mele</h1>
        <div class="breadcrumb">
            <a href="?controller=client&action=dashboard">Dashboard</a> > <span class="active">Comenzile Mele</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Actiuni rapide -->
    <div class="section">
        <div class="actions-header">
            <h2>Comenzile Dumneavoastra</h2>
            <div class="actions-buttons">
                <a href="?controller=client&action=comandaNoua" class="btn btn-primary">+ Comanda Noua</a>
            </div>
        </div>
    </div>

    <!-- Lista comenzi -->
    <div class="section">
        <div class="comenzi-container">
            <?php if (isset($comenzi) && !empty($comenzi)): ?>
                <?php foreach ($comenzi as $comanda): ?>
                    <div class="comanda-card" data-comanda-id="<?php echo $comanda['id']; ?>">
                        <div class="comanda-header">
                            <div class="comanda-info">
                                <h3>Comanda #<?php echo $comanda['id']; ?></h3>
                                <div class="comanda-meta">
                                    <span class="comanda-data">📅 <?php echo formatDate($comanda['DataProgramare']); ?></span>
                                    <span class="comanda-serviciu">🔧 <?php echo getServiceTypeName($comanda['TipServiciu']); ?></span>
                                </div>
                            </div>
                            <div class="comanda-status">
                                <?php echo getStatusBadge($comanda['Status']); ?>
                            </div>
                        </div>
                        
                        <div class="comanda-body">
                            <div class="comanda-detalii">
                                <div class="detaliu-item">
                                    <span class="detaliu-icon">🏢</span>
                                    <div class="detaliu-content">
                                        <span class="detaliu-label">Sediul</span>
                                        <span class="detaliu-value"><?php echo htmlspecialchars($comanda['nume_sediu'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="detaliu-item">
                                    <span class="detaliu-icon">🚚</span>
                                    <div class="detaliu-content">
                                        <span class="detaliu-label">Transport</span>
                                        <span class="detaliu-value"><?php echo $comanda['Transport'] ? 'Inclus' : 'Nu'; ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($comanda['Recurenta']): ?>
                                <div class="detaliu-item">
                                    <span class="detaliu-icon">🔄</span>
                                    <div class="detaliu-content">
                                        <span class="detaliu-label">Recurenta</span>
                                        <span class="detaliu-value">Da</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="comanda-actions">
                                <button onclick="vizualizareComanda(<?php echo $comanda['id']; ?>)" 
                                        class="btn btn-sm btn-outline">
                                    👁️ Vezi Detalii
                                </button>
                                
                                <?php if (in_array($comanda['Status'], ['noua', 'in curs'])): ?>
                                <button onclick="anuleazaComanda(<?php echo $comanda['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    ❌ Anuleaza
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comenzi">
                    <div class="no-comenzi-icon">📋</div>
                    <h3>Nu aveti comenzi</h3>
                    <p>Nu s-au gasit comenzi pentru contul dumneavoastra.</p>
                    <div class="no-comenzi-actions">
                        <a href="?controller=client&action=comandaNoua" class="btn btn-primary">Comanda Acum</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal detalii comanda -->
<div id="comandaModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="comandaModalTitle">Detalii Comanda</h3>
            <button class="modal-close" onclick="inchideComandaModal()">&times;</button>
        </div>
        <div class="modal-body" id="comandaModalBody">
            <div class="loading">Se incarca...</div>
        </div>
    </div>
</div>

<style>
.actions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.actions-buttons {
    display: flex;
    gap: 1rem;
}

.comenzi-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.comanda-card {
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.comanda-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.comanda-header {
    background: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.comanda-info h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.comanda-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.comanda-body {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 2rem;
}

.comanda-detalii {
    flex: 1;
}

.detaliu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detaliu-icon {
    font-size: 1.2rem;
    opacity: 0.7;
}

.detaliu-content {
    display: flex;
    flex-direction: column;
}

.detaliu-label {
    font-size: 0.8rem;
    color: #666;
    font-weight: 500;
}

.detaliu-value {
    font-size: 0.9rem;
    color: #2c3e50;
    font-weight: 600;
}

.comanda-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 120px;
}

.no-comenzi {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.no-comenzi-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.no-comenzi h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.no-comenzi-actions {
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-header {
    background: #3498db;
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 2rem;
}

.loading {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

@media (max-width: 768px) {
    .actions-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .comanda-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .comanda-body {
        flex-direction: column;
        align-items: stretch;
        gap: 1.5rem;
    }
    
    .comanda-actions {
        flex-direction: row;
        flex-wrap: wrap;
        min-width: auto;
    }
}
</style>

<script>
// Vizualizare detalii comanda
function vizualizareComanda(comandaId) {
    const modal = document.getElementById('comandaModal');
    const modalBody = document.getElementById('comandaModalBody');
    const modalTitle = document.getElementById('comandaModalTitle');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se incarca detaliile comenzii...</div>';
    modalTitle.textContent = `Detalii Comanda #${comandaId}`;
    
    // Simulare AJAX request - ar trebui inlocuit cu request real
    setTimeout(() => {
        modalBody.innerHTML = `
            <div class="comanda-detalii-full">
                <h4>Informatii Comanda</h4>
                <p><strong>ID Comanda:</strong> #${comandaId}</p>
                <p><strong>Data:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Status:</strong> In curs</p>
            </div>
        `;
    }, 1000);
}

// Inchide modalul
function inchideComandaModal() {
    document.getElementById('comandaModal').style.display = 'none';
}

// Anuleaza comanda
function anuleazaComanda(comandaId) {
    if (!confirm('Sigur doriti sa anulati aceasta comanda?')) {
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '?controller=client&action=anuleazaComanda', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Comanda a fost anulata cu succes.');
                        location.reload();
                    } else {
                        alert(response.error || 'Eroare la anularea comenzii.');
                    }
                } catch (e) {
                    alert('Eroare la procesarea raspunsului.');
                }
            } else {
                alert('Eroare la anularea comenzii.');
            }
        }
    };
    
    const params = `comanda_id=${comandaId}&csrf_token=${document.querySelector('input[name="csrf_token"]').value}`;
    xhr.send(params);
}

// Inchide modalul la click in afara
document.addEventListener('click', function(e) {
    const modal = document.getElementById('comandaModal');
    if (e.target === modal) {
        inchideComandaModal();
    }
});

// Inchide modalul cu Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        inchideComandaModal();
    }
});
</script>
