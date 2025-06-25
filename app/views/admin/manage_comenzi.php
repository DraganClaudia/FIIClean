<?php
/**
 * View pentru gestionarea comenzilor - simplified version
 */
?>

<div class="container">
    <div class="page-header">
        <h1>Gestionare Comenzi</h1>
        <div class="breadcrumb">
            <a href="?controller=admin&action=dashboard">Admin</a> > <span class="active">Gestionare Comenzi</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Filtre si Actiuni -->
    <div class="section">
        <div class="controls-header">
            <h2>Filtrare si Cautare</h2>
            <div class="controls-actions">
                <a href="?controller=admin&action=exportData&type=comenzi&csrf_token=<?php echo $csrf_token; ?>" 
                   class="btn btn-export">📄 Export CSV</a>
            </div>
        </div>
        
        <form method="GET" action="" class="filters-form">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="manageComenzi">
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="status">Status Comanda</label>
                    <select id="status" name="status">
                        <option value="">Toate statusurile</option>
                        <option value="noua">Noua</option>
                        <option value="in curs">In curs</option>
                        <option value="finalizata">Finalizata</option>
                        <option value="anulata">Anulata</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="serviciu">Tip Serviciu</label>
                    <select id="serviciu" name="serviciu">
                        <option value="">Toate serviciile</option>
                        <option value="covor">Spalare covoare</option>
                        <option value="auto">Spalare auto</option>
                        <option value="textil">Curatenie textile</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Cautare</label>
                    <input type="text" id="search" name="search" 
                           placeholder="Cauta dupa client, sediu..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtreaza</button>
                    <a href="?controller=admin&action=manageComenzi" class="btn btn-secondary">Reseteaza</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista Comenzi -->
    <div class="section">
        <div class="section-header">
            <h2>Lista Comenzilor</h2>
            <div class="comenzi-count">
                Total: <strong><?php echo count($comenzi ?? []); ?></strong> comenzi
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="comenziTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Serviciu</th>
                        <th>Sediu</th>
                        <th>Data Programare</th>
                        <th>Status</th>
                        <th>Transport</th>
                        <th>Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($comenzi) && !empty($comenzi)): ?>
                        <?php foreach ($comenzi as $comanda): ?>
                            <tr data-comanda-id="<?php echo $comanda['id']; ?>">
                                <td>#<?php echo $comanda['id']; ?></td>
                                <td><?php echo htmlspecialchars($comanda['nume_client'] ?? 'N/A'); ?></td>
                                <td><?php echo getServiceTypeName($comanda['TipServiciu']); ?></td>
                                <td><?php echo htmlspecialchars($comanda['nume_sediu'] ?? 'N/A'); ?></td>
                                <td><?php echo formatDate($comanda['DataProgramare']); ?></td>
                                <td><?php echo getStatusBadge($comanda['Status']); ?></td>
                                <td>
                                    <span class="transport-badge <?php echo $comanda['Transport'] ? 'yes' : 'no'; ?>">
                                        <?php echo $comanda['Transport'] ? 'Da' : 'Nu'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="vizualizareComanda(<?php echo $comanda['id']; ?>)" 
                                                class="btn btn-sm btn-info" title="Vezi Detalii">
                                            👁️
                                        </button>
                                        <button onclick="actualizeazaStatus(<?php echo $comanda['id']; ?>)" 
                                                class="btn btn-sm btn-edit" title="Actualizeaza Status">
                                            ✏️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">Nu exista comenzi inregistrate</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalii Comanda -->
<div id="comandaModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="comandaModalTitle">Detalii Comanda</h3>
            <button class="modal-close" onclick="inchideCamandaModal()">&times;</button>
        </div>
        <div class="modal-body" id="comandaModalBody">
            <div class="loading">Se incarca...</div>
        </div>
    </div>
</div>

<!-- Modal Actualizare Status -->
<div id="statusModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Actualizeaza Status Comanda</h3>
            <button class="modal-close" onclick="inchideStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="statusForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="updateStatus">
                <input type="hidden" id="status_comanda_id" name="comanda_id">
                
                <div class="form-group">
                    <label for="nou_status">Nou Status:</label>
                    <select id="nou_status" name="nou_status" required>
                        <option value="noua">Noua</option>
                        <option value="in curs">In curs</option>
                        <option value="finalizata">Finalizata</option>
                        <option value="anulata">Anulata</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizeaza Status</button>
                    <button type="button" onclick="inchideStatusModal()" class="btn btn-secondary">Anuleaza</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.controls-actions {
    display: flex;
    gap: 1rem;
}

.filters-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.comenzi-count {
    color: #666;
    font-size: 0.9rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.transport-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.transport-badge.yes {
    background: #d4edda;
    color: #155724;
}

.transport-badge.no {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
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

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-export {
    background: #27ae60;
    color: white;
}

.btn-export:hover {
    background: #229954;
}

.btn-sm {
    padding: 0.5rem;
    font-size: 0.8rem;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.btn-edit {
    background: #f39c12;
    color: white;
}

.btn-edit:hover {
    background: #e67e22;
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

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

@media (max-width: 768px) {
    .controls-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
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
    
    // Simulare AJAX request - in realitate ar trebui sa fie un request real
    setTimeout(() => {
        modalBody.innerHTML = `
            <div class="comanda-detalii-complete">
                <h4>Informatii Comanda</h4>
                <p><strong>ID Comanda:</strong> #${comandaId}</p>
                <p><strong>Data:</strong> ${new Date().toLocaleDateString()}</p>
                <p><strong>Status:</strong> In curs</p>
                <p><strong>Recurenta:</strong> Nu</p>
                
                <h4>Informatii Client</h4>
                <p><strong>Nume:</strong> Client Test</p>
                <p><strong>Email:</strong> client@test.ro</p>
                
                <h4>Informatii Serviciu</h4>
                <p><strong>Tip:</strong> Spalare covoare</p>
                <p><strong>Transport:</strong> Da</p>
            </div>
        `;
    }, 1000);
}

// Inchide modalul de detalii
function inchideCamandaModal() {
    document.getElementById('comandaModal').style.display = 'none';
}

// Actualizeaza status comanda
function actualizeazaStatus(comandaId) {
    document.getElementById('status_comanda_id').value = comandaId;
    document.getElementById('statusModal').style.display = 'flex';
}

// Inchide modalul de status
function inchideStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
    document.getElementById('statusForm').reset();
}

// Submit form status
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Simulare request AJAX
    setTimeout(() => {
        alert('Status actualizat cu succes!');
        inchideStatusModal();
        location.reload();
    }, 500);
});

// Inchide modalurile la click in afara
document.addEventListener('click', function(e) {
    const comandaModal = document.getElementById('comandaModal');
    const statusModal = document.getElementById('statusModal');
    
    if (e.target === comandaModal) {
        inchideCamandaModal();
    }
    if (e.target === statusModal) {
        inchideStatusModal();
    }
});

// Inchide modalurile cu Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        inchideCamandaModal();
        inchideStatusModal();
    }
});
</script>
