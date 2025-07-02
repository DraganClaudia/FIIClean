if (!localStorage.getItem('auth_token')) {
            window.location.href = 'login.html';
        }
        
        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        if (userData.role !== 'manager') {
            alert('Acces neautorizat!');
            window.location.href = 'login.html';
        }
        
        let currentUser = null;
        let currentLocationId = null;
        
        document.addEventListener('DOMContentLoaded', loadManagerDashboard);
        
        async function loadManagerDashboard() {
            try {
                const userResponse = await fetch('../api/auth.php?action=me', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                if (userResponse.ok) {
                    const result = await userResponse.json();
                    if (result.success) {
                        currentUser = result.user;
                        currentLocationId = result.user.location_id;
                        
                        document.getElementById('manager-info').innerHTML = `
                            <strong>${result.user.first_name} ${result.user.last_name}</strong><br>
                            Locația: ${result.user.location_name || 'Nedefinită'}
                        `;
                    }
                }
                
                await Promise.all([
                    loadLocationStatistics(),
                    loadLocationOrders(),
                    loadMyWorkers(),
                    loadWorkerCompletionStatus(),
                    loadManagerAlerts()
                ]);
                
            } catch (error) {
                console.error('Eroare la încărcarea dashboard-ului manager:', error);
            }
        }
        
        async function loadLocationStatistics() {
            try {
                if (!currentLocationId) return;
                
                const response = await fetch(`../api/index.php?resource=orders&action=by-location&id=${currentLocationId}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const orders = await response.json();
                
                const today = new Date().toISOString().split('T')[0];
                const completedToday = orders.filter(order => 
                    order.status === 'completed' && 
                    order.created_at.startsWith(today)
                ).length;
                
                document.getElementById('location-orders').textContent = orders.length;
                document.getElementById('pending-orders').textContent = 
                    orders.filter(order => order.status === 'pending').length;
                document.getElementById('completed-today').textContent = completedToday;
                
            } catch (error) {
                console.error('Eroare la încărcarea statisticilor:', error);
            }
        }
        
        async function loadLocationOrders() {
            try {
                if (!currentLocationId) return;
                
                const response = await fetch(`../api/index.php?resource=orders&action=by-location&id=${currentLocationId}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const orders = await response.json();
                displayLocationOrders(orders);
                
            } catch (error) {
                document.getElementById('location-orders-list').innerHTML = '<p>Eroare la încărcarea comenzilor.</p>';
            }
        }
        
        function displayLocationOrders(orders) {
            const container = document.getElementById('location-orders-list');
            
            if (orders.length === 0) {
                container.innerHTML = '<p>Nu există comenzi pentru această locație.</p>';
                return;
            }
            
            const html = orders.map(order => `
                <div class="order-item">
                    <div class="order-info">
                        <strong>Comanda #${order.id}</strong><br>
                        <small>
                            ${order.client_name} | ${order.service_type}<br>
                            Status: ${getStatusLabel(order.status)}
                        </small>
                    </div>
                    <div class="order-actions">
                        ${order.status === 'pending' ? `
                            <button class="btn-small" style="background: #3498db; color: white;" 
                                    onclick="showAssignModal(${order.id})">
                                Asignează
                            </button>
                        ` : ''}
                        <select class="btn-small" onchange="updateOrderStatus(${order.id}, this.value)">
                            <option value="">Schimbă Status</option>
                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>În așteptare</option>
                            <option value="in_progress" ${order.status === 'in_progress' ? 'selected' : ''}>În progres</option>
                            <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completată</option>
                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Anulată</option>
                        </select>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        function getStatusLabel(status) {
            const labels = {
                'pending': '⏳ În așteptare',
                'in_progress': '🔄 În progres',
                'completed': '✅ Completată',
                'cancelled': '❌ Anulată'
            };
            return labels[status] || status;
        }
        
        async function loadMyWorkers() {
            try {
                if (!currentLocationId) return;
                
                const response = await fetch(`../api/users.php?action=workers&location_id=${currentLocationId}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const workers = await response.json();
                displayWorkers(workers);
                
                const activeWorkers = workers.filter(worker => worker.is_active).length;
                document.getElementById('active-workers').textContent = activeWorkers;
                
            } catch (error) {
                document.getElementById('workers-list').innerHTML = '<p>Eroare la încărcarea worker-ilor.</p>';
            }
        }
        
        function displayWorkers(workers) {
            const container = document.getElementById('workers-list');
            
            if (workers.length === 0) {
                container.innerHTML = '<p>Nu există worker-i asignați acestei locații.</p>';
                return;
            }
            
            const html = workers.map(worker => `
                <div class="worker-item">
                    <div>
                        <strong>${worker.first_name} ${worker.last_name}</strong><br>
                        <small>${getRoleLabel(worker.role)} | ${worker.phone || 'Fără telefon'}</small>
                    </div>
                    <span class="worker-status ${worker.is_active ? 'status-active' : 'status-inactive'}">
                        ${worker.is_active ? 'Activ' : 'Inactiv'}
                    </span>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        function getRoleLabel(role) {
            const labels = {
                'worker_transport': '🚛 Transport',
                'worker_cleaner': '🧽 Cleaner'
            };
            return labels[role] || role;
        }
        
        async function loadManagerAlerts() {
            try {
                const alerts = [
                    { type: 'info', message: 'Toate comenzile sunt actualizate' },
                    { type: 'warning', message: '3 comenzi în așteptare de asignare' }
                ];
                
                const container = document.getElementById('manager-alerts');
                
                const html = alerts.map(alert => `
                    <div style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                        <span style="color: ${alert.type === 'warning' ? '#f39c12' : '#27ae60'};">
                            ${alert.type === 'warning' ? '⚠️' : 'ℹ️'}
                        </span>
                        ${alert.message}
                    </div>
                `).join('');
                
                container.innerHTML = html;
                
            } catch (error) {
                document.getElementById('manager-alerts').innerHTML = '<p>Eroare la încărcarea alertelor.</p>';
            }
        }
        
        
        async function showAssignModal(orderId) {
            try {
                const response = await fetch(`../api/users.php?action=workers&location_id=${currentLocationId}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const workers = await response.json();
                const transportWorkers = workers.filter(w => w.role === 'worker_transport' && w.is_active);
                const cleanerWorkers = workers.filter(w => w.role === 'worker_cleaner' && w.is_active);
                
                const content = `
                    <div class="assign-form">
                        <label>Worker Transport:</label>
                        <select id="transport-worker">
                            <option value="">Selectează worker transport</option>
                            ${transportWorkers.map(w => `<option value="${w.id}">${w.first_name} ${w.last_name}</option>`).join('')}
                        </select>
                        <button onclick="assignWorker(${orderId}, 'transport')" class="btn-small" style="background: #3498db; color: white;">
                            Asignează
                        </button>
                    </div>
                    
                    <div class="assign-form">
                        <label>Worker Cleaner:</label>
                        <select id="cleaner-worker">
                            <option value="">Selectează worker cleaner</option>
                            ${cleanerWorkers.map(w => `<option value="${w.id}">${w.first_name} ${w.last_name}</option>`).join('')}
                        </select>
                        <button onclick="assignWorker(${orderId}, 'cleaner')" class="btn-small" style="background: #27ae60; color: white;">
                            Asignează
                        </button>
                    </div>
                `;
                
                document.getElementById('assign-content').innerHTML = content;
                document.getElementById('assign-modal').style.display = 'block';
                
            } catch (error) {
                alert('Eroare la încărcarea worker-ilor');
            }
        }
        
        function closeAssignModal() {
            document.getElementById('assign-modal').style.display = 'none';
        }
        
        async function assignWorker(orderId, workerType) {
            const workerId = document.getElementById(workerType === 'transport' ? 'transport-worker' : 'cleaner-worker').value;
            
            if (!workerId) {
                alert('Selectează un worker');
                return;
            }
            
            try {
                const response = await fetch(`../api/index.php?resource=orders&action=assign-worker&id=${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify({
                        worker_id: workerId,
                        worker_type: workerType
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Worker asignat cu succes!');
                    closeAssignModal();
                    loadLocationOrders(); 
                } else {
                    alert('Eroare la asignarea worker-ului');
                }
            } catch (error) {
                alert('Eroare de conexiune');
            }
        }
        
        async function updateOrderStatus(orderId, newStatus) {
            if (!newStatus) return;
            
            try {
                const response = await fetch(`../api/index.php?resource=orders&action=updateStatus&id=${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadLocationOrders();
                    loadLocationStatistics(); 
                } else {
                    alert('Eroare la actualizarea statusului');
                }
            } catch (error) {
                alert('Eroare de conexiune');
            }
        }

        async function loadWorkerCompletionStatus() {
            try {
                if (!currentLocationId) return;
                
                const response = await fetch(`../api/index.php?resource=orders&action=by-location&id=${currentLocationId}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const orders = await response.json();
                const today = new Date().toISOString().split('T')[0];
                
                const completedToday = orders.filter(order => 
                    (order.transport_status === 'completed' || order.cleaning_status === 'completed') && 
                    order.created_at && order.created_at.startsWith(today)
                );
                
                const workerStats = {};
                
                completedToday.forEach(order => {
                    if (order.transport_worker_name && order.transport_status === 'completed') {
                        if (!workerStats[order.transport_worker_name]) {
                            workerStats[order.transport_worker_name] = { transport: 0, cleaning: 0, role: 'Transport' };
                        }
                        workerStats[order.transport_worker_name].transport++;
                    }
                    
                    if (order.cleaner_worker_name && order.cleaning_status === 'completed') {
                        if (!workerStats[order.cleaner_worker_name]) {
                            workerStats[order.cleaner_worker_name] = { transport: 0, cleaning: 0, role: 'Cleaner' };
                        }
                        workerStats[order.cleaner_worker_name].cleaning++;
                    }
                });
                
                const container = document.getElementById('worker-completion-status');
                
                if (Object.keys(workerStats).length === 0) {
                    container.innerHTML = '<p>Nicio sarcină finalizată astăzi.</p>';
                    return;
                }
                
                const html = Object.entries(workerStats).map(([workerName, stats]) => `
                    <div style="padding: 0.8rem; border-bottom: 1px solid #eee; border-left: 3px solid #27ae60;">
                        <strong>✅ ${workerName}</strong> (${stats.role})<br>
                        <small>
                            ${stats.transport > 0 ? `🚛 Transport: ${stats.transport} sarcini` : ''}
                            ${stats.cleaning > 0 ? `🧽 Curățare: ${stats.cleaning} sarcini` : ''}
                        </small>
                    </div>
                `).join('');
                
                container.innerHTML = html;
                
            } catch (error) {
                document.getElementById('worker-completion-status').innerHTML = '<p>Eroare la încărcarea statusului.</p>';
            }
        }
        
        function logout() {
            if (confirm('Sigur vrei să te deconectezi?')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = 'login.html';
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('assign-modal');
            if (event.target === modal) {
                closeAssignModal();
            }
        }