<div class="container">
    <div class="page-header">
        <h1>Management Locații</h1>
        <div class="breadcrumb">
            <a href="?controller=admin&action=dashboard">Admin</a> > <span class="active">Management Locații</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add Location Form -->
    <div class="section">
        <div class="section-header">
            <h2>Adaugă Locație Nouă</h2>
            <button id="toggleForm" class="btn btn-primary">+ Adaugă Locație</button>
        </div>
        
        <div id="addLocationForm" class="form-container" style="display: none;">
            <form id="locationForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nume Locație *</label>
                        <input type="text" id="name" name="name" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adresă *</label>
                        <input type="text" id="address" name="address" required maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="latitude">Latitudine</label>
                        <input type="number" id="latitude" name="latitude" step="0.000001" min="-90" max="90">
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude">Longitudine</label>
                        <input type="number" id="longitude" name="longitude" step="0.000001" min="-180" max="180">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Adaugă Locația</button>
                    <button type="button" id="cancelAdd" class="btn btn-secondary">Anulează</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Locations List -->
    <div class="section">
        <div class="section-header">
            <h2>Locațiile Existente</h2>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Caută locații..." class="search-input">
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="locationsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nume</th>
                        <th>Adresă</th>
                        <th>Coordonate</th>
                        <th>Status</th>
                        <th>Total Comenzi</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($locations) && !empty($locations)): ?>
                        <?php foreach ($locations as $location): ?>
                            <tr data-location-id="<?php echo $location['id']; ?>">
                                <td><?php echo $location['id']; ?></td>
                                <td><?php echo htmlspecialchars($location['Nume']); ?></td>
                                <td><?php echo htmlspecialchars($location['Adresa'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($location['Latitudine']) && !empty($location['Longitudine'])): ?>
                                        <?php echo number_format($location['Latitudine'], 6); ?>, 
                                        <?php echo number_format($location['Longitudine'], 6); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $location['Stare']; ?>">
                                        <?php echo ucfirst($location['Stare']); ?>
                                    </span>
                                </td>
                                <td><?php echo $location['total_orders'] ?? 0; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editLocation(<?php echo $location['id']; ?>)" 
                                                class="btn btn-sm btn-edit" title="Editează">
                                            ✏️
                                        </button>
                                        <button onclick="deleteLocation(<?php echo $location['id']; ?>)" 
                                                class="btn btn-sm btn-delete" title="Șterge">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">Nu există locații înregistrate</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editează Locația</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_name">Nume Locație *</label>
                    <input type="text" id="edit_name" name="name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Adresă *</label>
                    <input type="text" id="edit_address" name="address" required maxlength="255">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_latitude">Latitudine</label>
                        <input type="number" id="edit_latitude" name="latitude" step="0.000001" min="-90" max="90">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_longitude">Longitudine</label>
                        <input type="number" id="edit_longitude" name="longitude" step="0.000001" min="-180" max="180">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="activ">Activ</option>
                        <option value="inactiv">Inactiv</option>
                        <option value="reparatii">În reparații</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvează Modificările</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Anulează</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb .active {
    color: #333;
    font-weight: 600;
}

.section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.form-container {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
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

.btn-sm {
    padding: 0.5rem;
    font-size: 0.9rem;
}

.btn-edit {
    background: #f39c12;
    color: white;
}

.btn-edit:hover {
    background: #e67e22;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-delete:hover {
    background: #c0392b;
}

.search-container {
    position: relative;
}

.search-input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    width: 300px;
}

.table-container {
    overflow-x: auto;
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

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.activ {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactiv {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.reparatii {
    background: #fff3cd;
    color: #856404;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
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

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .search-input {
        width: 100%;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
// Toggle add form
document.getElementById('toggleForm').addEventListener('click', function() {
    const form = document.getElementById('addLocationForm');
    const isVisible = form.style.display !== 'none';
    form.style.display = isVisible ? 'none' : 'block';
    this.textContent = isVisible ? '+ Adaugă Locație' : 'Ascunde Formularul';
});

// Cancel add form
document.getElementById('cancelAdd').addEventListener('click', function() {
    document.getElementById('addLocationForm').style.display = 'none';
    document.getElementById('toggleForm').textContent = '+ Adaugă Locație';
    document.getElementById('locationForm').reset();
});

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#locationsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Edit location function
function editLocation(id) {
    // Get location data from table row
    const row = document.querySelector(`tr[data-location-id="${id}"]`);
    const cells = row.querySelectorAll('td');
    
    // Populate edit form
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = cells[1].textContent;
    document.getElementById('edit_address').value = cells[2].textContent;
    
    // Parse coordinates
    const coords = cells[3].textContent.trim();
    if (coords !== 'N/A') {
        const [lat, lng] = coords.split(', ');
        document.getElementById('edit_latitude').value = lat;
        document.getElementById('edit_longitude').value = lng;
    }
    
    // Set status
    const status = cells[4].querySelector('.status-badge').textContent.toLowerCase();
    document.getElementById('edit_status').value = status;
    
    // Show modal
    document.getElementById('editModal').style.display = 'flex';
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
}

// Delete location function
function deleteLocation(id) {
    if (!confirm('Sigur doriți să ștergeți această locație?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="${id}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('editModal');
    if (e.target === modal) {
        closeEditModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});
</script>