if (!localStorage.getItem('auth_token')) {
            window.location.href = 'login.html';
        }
        
        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        if (userData.role !== 'admin') {
            alert('Acces neautorizat!');
            window.location.href = 'login.html';
        }
        
        document.addEventListener('DOMContentLoaded', loadAdminDashboard);
        
        async function loadAdminDashboard() {
            try {
                await Promise.all([
                    loadStatistics(),
                    loadUsers(),
                    loadRecentOrders(),
                    loadLocations(),
                    loadSystemAlerts(),
                    loadLocationsForSelect(),
                    loadAdvancedStats()
                ]);
                
                updateAdminInfo();
                
            } catch (error) {
                console.error('Eroare la încărcarea dashboard-ului admin:', error);
            }
        }
        
        async function updateAdminInfo() {
            try {
                const response = await fetch('../api/auth.php?action=me', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        document.getElementById('admin-info').innerHTML = `
                            Bun venit, <strong>${result.user.first_name} ${result.user.last_name}</strong><br>
                            <small>Ultima conectare: ${result.user.last_login ? new Date(result.user.last_login).toLocaleString('ro-RO') : 'Prima conectare'}</small>
                        `;
                    }
                }
            } catch (error) {
                console.error('Eroare la încărcarea informațiilor admin:', error);
            }
        }
        
        async function loadStatistics() {
            try {
                const [usersResponse, locationsResponse, ordersResponse] = await Promise.all([
                    fetch('../api/users.php?action=list', {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    }),
                    fetch('../api/index.php?resource=locations&action=list'),
                    fetch('../api/index.php?resource=orders&action=list', {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    })
                ]);
                
                const users = await usersResponse.json();
                const locations = await locationsResponse.json();
                const orders = await ordersResponse.json();
                
                document.getElementById('total-users').textContent = users.length;
                document.getElementById('total-locations').textContent = locations.length;
                document.getElementById('total-orders').textContent = orders.length;
                document.getElementById('pending-orders').textContent = 
                    orders.filter(order => order.status === 'pending').length;
                
            } catch (error) {
                console.error('Eroare la încărcarea statisticilor:', error);
            }
        }
        
        async function loadAdvancedStats() {
            try {
                const response = await fetch('../api/index.php?resource=orders&action=statistics', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                if (response.ok) {
                    const stats = await response.json();
                    
                    const today = new Date().toISOString().split('T')[0];
                    const ordersResponse = await fetch('../api/index.php?resource=orders&action=list', {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    });
                    const orders = await ordersResponse.json();
                    
                    const completedToday = orders.filter(order => 
                        order.status === 'completed' && 
                        order.created_at && order.created_at.startsWith(today)
                    ).length;
                    
                    const totalRevenue = orders.reduce((sum, order) => 
                        sum + (parseFloat(order.price) || 0), 0
                    );
                    
                    const completionRate = orders.length > 0 ? 
                        ((orders.filter(order => order.status === 'completed').length / orders.length) * 100).toFixed(1) : 0;
                    
                    const usersResponse = await fetch('../api/users.php?action=list', {
                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                    });
                    const users = await usersResponse.json();
                    const activeUsers = users.filter(user => user.is_active).length;
                    
                    document.getElementById('monthly-revenue').textContent = totalRevenue.toFixed(2) + ' RON';
                    document.getElementById('completed-today').textContent = completedToday;
                    document.getElementById('active-users').textContent = activeUsers;
                    document.getElementById('completion-rate').textContent = completionRate + '%';
                }
            } catch (error) {
                console.error('Eroare la încărcarea alertelor:', error); // ADAUGĂ ACEST LOG
                document.getElementById('system-alerts').innerHTML = '<p>Eroare la încărcarea alertelor.</p>';
            }
        }
        
        async function loadUsers() {
            try {
                const response = await fetch('../api/users.php?action=list', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const users = await response.json();
                displayUsers(users);
            } catch (error) {
                document.getElementById('users-list').innerHTML = '<p>Eroare la încărcarea utilizatorilor.</p>';
            }
        }
        
        function displayUsers(users) {
            const container = document.getElementById('users-list');
            
            if (users.length === 0) {
                container.innerHTML = '<p>Nu există utilizatori.</p>';
                return;
            }
            
            container.innerHTML = '';
            const template = document.getElementById('user-item-template');
            
            users.forEach(user => {
                const clone = template.content.cloneNode(true);
                
                clone.querySelector('.user-name').textContent = `${user.first_name} ${user.last_name}`;
                clone.querySelector('.user-details').textContent = `${user.email} | ${getRoleLabel(user.role)}`;
                
                if (user.last_login) {
                    clone.querySelector('.user-last-login').textContent = 
                        `Ultima conectare: ${new Date(user.last_login).toLocaleDateString('ro-RO')}`;
                } else {
                    clone.querySelector('.user-last-login').style.display = 'none';
                }
                
                const deleteBtn = clone.querySelector('.user-delete-btn');
                deleteBtn.setAttribute('data-user-id', user.id);
                deleteBtn.setAttribute('data-user-active', user.is_active);
                
                container.appendChild(clone);
            });
            
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('user-delete-btn')) {
                    const userId = e.target.getAttribute('data-user-id');
                    const isActive = e.target.getAttribute('data-user-active');
                    toggleUserStatus(userId, isActive);
                }
            });
        }
        
        function getRoleLabel(role) {
            const labels = {
                'admin': '👑 Admin',
                'manager': '👨‍💼 Manager',
                'client': '👤 Client',
                'worker_transport': '🚛 Transport',
                'worker_cleaner': '🧽 Cleaner'
            };
            return labels[role] || role;
        }
        
        async function loadRecentOrders() {
            try {
                const response = await fetch('../api/index.php?resource=orders&action=list', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const orders = await response.json();
                const recentOrders = orders.slice(0, 5);
                
                const container = document.getElementById('recent-orders');
                
                if (recentOrders.length === 0) {
                    container.innerHTML = '<p>Nu există comenzi recente.</p>';
                    return;
                }
                
                const html = recentOrders.map(order => `
                    <div style="padding: 0.8rem; border-bottom: 1px solid #eee; border-left: 3px solid ${getStatusColor(order.status)};">
                        <strong>Comanda #${order.id}</strong><br>
                        <small>${order.client_name} | ${order.service_type}</small><br>
                        <small style="color: ${getStatusColor(order.status)}; font-weight: bold;">${getStatusLabel(order.status)}</small>
                        ${order.price ? `<br><small>💰 ${order.price} RON</small>` : ''}
                    </div>
                `).join('');
                
                container.innerHTML = html;
                
            } catch (error) {
                document.getElementById('recent-orders').innerHTML = '<p>Eroare la încărcarea comenzilor.</p>';
            }
        }
        
        function getStatusColor(status) {
            const colors = {
                'pending': '#f39c12',
                'in_progress': '#3498db',
                'completed': '#27ae60',
                'cancelled': '#e74c3c'
            };
            return colors[status] || '#95a5a6';
        }
        
        function getStatusLabel(status) {
            const labels = {
                'pending': 'În așteptare',
                'in_progress': 'În progres',
                'completed': 'Completată',
                'cancelled': 'Anulată'
            };
            return labels[status] || status;
        }
        
        async function loadLocations() {
            try {
                const response = await fetch('../api/index.php?resource=locations&action=list');
                const locations = await response.json();
                
                const container = document.getElementById('locations-list');
                
                if (locations.length === 0) {
                    container.innerHTML = '<p>Nu există locații.</p>';
                    return;
                }
                
                const html = locations.map(location => `
                    <div style="padding: 0.8rem; border-bottom: 1px solid #eee;">
                        <strong>📍 ${location.name}</strong><br>
                        <small>${location.address}</small><br>
                        <small>
                            Status: 
                            <select onchange="updateLocationStatus(${location.id}, this.value)" style="color: #333; background: white; border: 1px solid #ddd; padding: 0.2rem;">
                                <option value="active" ${location.status === 'active' ? 'selected' : ''}>🟢 Activ</option>
                                <option value="inactive" ${location.status === 'inactive' ? 'selected' : ''}>🔴 Inactiv</option>
                                <option value="maintenance" ${location.status === 'maintenance' ? 'selected' : ''}>🔧 Mentenanță</option>
                            </select>
                        </small>
                        ${location.services ? `<br><small>Servicii: ${location.services}</small>` : ''}
                        <br><small style="color: #666;">ID: ${location.id} | Creat: ${new Date(location.created_at).toLocaleDateString('ro-RO')}</small>
                    </div>
                `).join('');
                
                container.innerHTML = html;
                
            } catch (error) {
                document.getElementById('locations-list').innerHTML = '<p>Eroare la încărcarea locațiilor.</p>';
            }
        }
        
        async function loadSystemAlerts() {
            try {
                const alerts = [];
                
                const usersResponse = await fetch('../api/users.php?action=list', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                const users = await usersResponse.json();
                const inactiveUsers = users.filter(user => !user.is_active).length;
                
                if (inactiveUsers > 0) {
                    alerts.push({
                        type: 'warning',
                        message: `${inactiveUsers} utilizatori dezactivați în sistem`
                    });
                }
                
                const ordersResponse = await fetch('../api/index.php?resource=orders&action=list', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                const orders = await ordersResponse.json();
                const pendingOrders = orders.filter(order => order.status === 'pending').length;
                
                if (pendingOrders > 5) {
                    alerts.push({
                        type: 'warning',
                        message: `${pendingOrders} comenzi în așteptare necesită atenție`
                    });
                }
                
                const completedOrders = orders.filter(order => order.status === 'completed').length;
                if (orders.length > 0 && (completedOrders / orders.length) > 0.8) {
                    alerts.push({
                        type: 'success',
                        message: 'Performanță excelentă: >80% comenzi finalizate'
                    });
                }
                
                if (alerts.length === 0) {
                    alerts.push({
                        type: 'info',
                        message: 'Toate sistemele funcționează normal'
                    });
                }
                
                const container = document.getElementById('system-alerts');
                
                const html = alerts.map(alert => `
                    <div class="alert-item alert-${alert.type}">
                        <span style="font-weight: bold;">
                            ${alert.type === 'warning' ? '⚠️' : alert.type === 'success' ? '✅' : 'ℹ️'}
                        </span>
                        ${alert.message}
                    </div>
                `).join('');
                
                container.innerHTML = html;
                
            } catch (error) {
                document.getElementById('system-alerts').innerHTML = '<p>Eroare la încărcarea alertelor.</p>';
            }
        }
        
        async function loadLocationsForSelect() {
            try {
                const response = await fetch('../api/index.php?resource=locations&action=list');
                const locations = await response.json();
                
                const select = document.getElementById('location_id');
                
                locations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = location.name;
                    select.appendChild(option);
                });
                
            } catch (error) {
                console.error('Eroare la încărcarea locațiilor pentru select:', error);
            }
        }
        
        
        function showCreateUserModal() {
            document.getElementById('user-modal').style.display = 'block';
        }
        
        function closeUserModal() {
            document.getElementById('user-modal').style.display = 'none';
            document.getElementById('user-form').reset();
        }
        
        async function toggleUserStatus(userId, currentStatus) {
            if (!confirm('Sigur vrei să ștergi acest utilizator? Această acțiune nu poate fi anulată!')) {
                return;
            }
            
            try {
                const response = await fetch(`../api/users.php?action=delete&id=${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    loadUsers(); 
                    loadStatistics(); 
                    loadSystemAlerts(); 
                    alert('Utilizatorul a fost șters cu succes!');
                } else {
                    alert('Eroare la ștergerea utilizatorului: ' + (result.error || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare de conexiune');
                console.error('Eroare delete user:', error);
            }
        }
                
        document.getElementById('user-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userData = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                phone: document.getElementById('phone').value,
                role: document.getElementById('role').value,
                location_id: document.getElementById('location_id').value || null
            };
            
            if (userData.password.length < 6) {
                alert('Parola trebuie să aibă cel puțin 6 caractere');
                return;
            }
            
            if ((userData.role === 'manager' || userData.role.startsWith('worker_')) && !userData.location_id) {
                alert('Managerii și worker-ii trebuie să aibă o locație asignată');
                return;
            }
            
            try {
                const response = await fetch('../api/users.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify(userData)
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Utilizatorul a fost creat cu succes!');
                    closeUserModal();
                    loadUsers();
                    loadStatistics();
                    loadAdvancedStats();
                } else {
                    alert('Eroare la crearea utilizatorului: ' + (result.error || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare de conexiune');
                console.error('Eroare create user:', error);
            }
        });
        
        function logout() {
            if (confirm('Sigur vrei să te deconectezi?')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = 'login.html';
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('user-modal');
            if (event.target === modal) {
                closeUserModal();
            }
        }
        
        setInterval(() => {
            loadStatistics();
            loadAdvancedStats();
            loadSystemAlerts();
        }, 5 * 60 * 1000);


        function showCreateLocationModal() {
            document.getElementById('location-modal').style.display = 'block';
        }

        function closeLocationModal() {
            document.getElementById('location-modal').style.display = 'none';
            document.getElementById('location-form').reset();
        }

        document.getElementById('location-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const locationData = {
                name: document.getElementById('location-name').value,
                address: document.getElementById('location-address').value,
                latitude: document.getElementById('location-latitude').value || null,
                longitude: document.getElementById('location-longitude').value || null,
                services: document.getElementById('location-services').value
            };
            
            try {
                const response = await fetch('../api/index.php?resource=locations&action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify(locationData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Locația a fost creată cu succes!');
                    closeLocationModal();
                    loadLocations();
                } else {
                    alert('Eroare la crearea locației: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare de conexiune');
            }
        });

        async function updateLocationStatus(locationId, newStatus) {
            try {
                const response = await fetch(`../api/index.php?resource=locations&action=update-status&id=${locationId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadLocations(); 
                    alert('Status-ul locației a fost actualizat cu succes!');
                } else {
                    alert('Eroare la actualizarea status-ului: ' + (result.error || 'Eroare necunoscută'));
                    loadLocations();
                }
            } catch (error) {
                alert('Eroare de conexiune');
                loadLocations(); 
            }
        }