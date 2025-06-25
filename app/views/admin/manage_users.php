<div class="container">
    <div class="page-header">
        <h1>Management Utilizatori</h1>
        <div class="breadcrumb">
            <a href="?controller=admin&action=dashboard">Admin</a> > <span class="active">Management Utilizatori</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="section">
        <div class="section-header">
            <h2>Adaugă Utilizator Nou</h2>
            <button id="toggleUserForm" class="btn btn-primary">+ Adaugă Utilizator</button>
        </div>
        
        <div id="addUserForm" class="form-container" style="display: none;">
            <form id="userForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required maxlength="50" 
                               pattern="[a-zA-Z0-9_]+" title="Doar litere, cifre și underscore">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Parolă *</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small class="help-text">Minimum 6 caractere, cu cel puțin o literă mare și o cifră</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmă Parola *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Adaugă Utilizatorul</button>
                    <button type="button" id="cancelAddUser" class="btn btn-secondary">Anulează</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <div class="section">
        <div class="section-header">
            <h2>Utilizatorii Existenți</h2>
            <div class="search-container">
                <input type="text" id="searchUsers" placeholder="Caută utilizatori..." class="search-input">
            </div>
        </div>
        
        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Data Înregistrării</th>
                        <th>Status</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($users) && !empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?php echo $user['id']; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo formatDate($user['created_at'] ?? date('Y-m-d')); ?></td>
                                <td>
                                    <span class="status-badge active">Activ</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                                class="btn btn-sm btn-edit" title="Editează">
                                            ✏️
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                                class="btn btn-sm btn-delete" title="Șterge">
                                            🗑️
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">Nu există utilizatori înregistrați</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="section">
        <h2>Statistici Utilizatori</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo count($users ?? []); ?></h3>
                    <p>Total Utilizatori</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3><?php echo count($users ?? []); ?></h3>
                    <p>Utilizatori Activi</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <h3>0</h3>
                    <p>Înregistrări Astăzi</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editează Utilizatorul</h3>
            <button class="modal-close" onclick="closeEditUserModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editUserForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_user_id" name="id">
                
                <div class="form-group">
                    <label for="edit_username">Username *</label>
                    <input type="text" id="edit_username" name="username" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" id="edit_email" name="email" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="edit_password">Parolă Nouă</label>
                    <input type="password" id="edit_password" name="password" minlength="6">
                    <small class="help-text">Lasă gol dacă nu vrei să schimbi parola</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvează Modificările</button>
                    <button type="button" onclick="closeEditUserModal()" class="btn btn-secondary">Anulează</button>
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

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

.help-text {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.85rem;
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

.status-badge.active {
    background: #d4edda;
    color: #155724;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.stat-content h3 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.stat-content p {
    color: #666;
    margin: 0;
    font-weight: 500;
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
    max-width: 500px;
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Toggle add user form
document.getElementById('toggleUserForm').addEventListener('click', function() {
    const form = document.getElementById('addUserForm');
    const isVisible = form.style.display !== 'none';
    form.style.display = isVisible ? 'none' : 'block';
    this.textContent = isVisible ? '+ Adaugă Utilizator' : 'Ascunde Formularul';
});

// Cancel add user form
document.getElementById('cancelAddUser').addEventListener('click', function() {
    document.getElementById('addUserForm').style.display = 'none';
    document.getElementById('toggleUserForm').textContent = '+ Adaugă Utilizator';
    document.getElementById('userForm').reset();
});

// Password confirmation validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Parolele nu se potrivesc!');
        return false;
    }
    
    // Validate password strength
    if (!/^(?=.*[A-Z])(?=.*\d).{6,}$/.test(password)) {
        e.preventDefault();
        alert('Parola trebuie să conțină cel puțin o literă mare, o cifră și să aibă minim 6 caractere!');
        return false;
    }
});

// Search functionality
document.getElementById('searchUsers').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Edit user function
function editUser(id) {
    const row = document.querySelector(`tr[data-user-id="${id}"]`);
    const cells = row.querySelectorAll('td');
    
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = cells[1].textContent;
    document.getElementById('edit_email').value = cells[2].textContent;
    document.getElementById('edit_password').value = '';
    
    document.getElementById('editUserModal').style.display = 'flex';
}

// Close edit user modal
function closeEditUserModal() {
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('editUserForm').reset();
}

// Delete user function
function deleteUser(id) {
    if (!confirm('Sigur doriți să ștergeți acest utilizator?')) {
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
    const modal = document.getElementById('editUserModal');
    if (e.target === modal) {
        closeEditUserModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditUserModal();
    }
});
</script>