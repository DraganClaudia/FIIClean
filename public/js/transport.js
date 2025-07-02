if (!localStorage.getItem('auth_token')) {
            window.location.href = 'login.html';
        }
        
        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        if (userData.role !== 'worker_transport') {
            alert('Acces neautorizat!');
            window.location.href = 'login.html';
        }
        
        let currentUser = null;
        
        document.addEventListener('DOMContentLoaded', loadTransportDashboard);
        
        async function loadTransportDashboard() {
            try {
                const userResponse = await fetch('../api/auth.php?action=me', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                if (userResponse.ok) {
                    const result = await userResponse.json();
                    if (result.success) {
                        currentUser = result.user;
                        
                        document.getElementById('transport-info').innerHTML = `
                            <strong>${result.user.first_name} ${result.user.last_name}</strong><br>
                            Locația: ${result.user.location_name || 'Nedefinită'}
                        `;
                    }
                }
                
                await Promise.all([
                    loadMyTasks(),
                    loadDailyRoute(),
                    loadMyProgress(),
                    loadManagerContact()
                ]);
                
            } catch (error) {
                console.error('Eroare la încărcarea dashboard-ului transport:', error);
            }
        }
        
        async function loadMyTasks() {
            try {
                const response = await fetch('../api/index.php?resource=orders&action=assigned', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const tasks = await response.json();
                displayMyTasks(tasks);
                updateTaskStatistics(tasks);
                
            } catch (error) {
                document.getElementById('my-tasks').innerHTML = '<p>Eroare la încărcarea sarcinilor.</p>';
            }
        }
        
        function displayMyTasks(tasks) {
            const container = document.getElementById('my-tasks');
            
            if (tasks.length === 0) {
                container.innerHTML = '<p>Nu aveți sarcini de transport asignate.</p>';
                return;
            }
            
            const html = tasks.map(task => `
                <div class="task-item ${getTaskClass(task)}">
                    <h4>Comanda #${task.id} ${getPriorityIcon(task)}</h4>
                    <p><strong>Client:</strong> ${task.client_name}</p>
                    <p><strong>Serviciu:</strong> ${task.service_type}</p>
                    
                    <div class="route-info">
                        <p><strong>📍 Ridicare:</strong> ${task.pickup_address || 'Nedefinită'}</p>
                        <p><strong>🎯 Livrare:</strong> ${task.delivery_address || 'Nedefinită'}</p>
                        ${task.scheduled_date ? `<p><strong>⏰ Programat:</strong> ${new Date(task.scheduled_date).toLocaleString('ro-RO')}</p>` : ''}
                    </div>
                    
                    <p><strong>Status Transport:</strong> ${getTransportStatusLabel(task.transport_status)}</p>
                    
                    ${task.notes ? `<p><strong>Observații:</strong> ${task.notes}</p>` : ''}
                    
                    <div class="task-actions">
                        ${task.transport_status === 'pending' ? `
                            <button class="btn-action btn-start" onclick="updateTaskStatus(${task.id}, 'in_progress')">
                                🚀 Pornește Transport
                            </button>
                        ` : ''}
                        
                        ${task.transport_status === 'in_progress' ? `
                            <button class="btn-action btn-complete" onclick="updateTaskStatus(${task.id}, 'completed')">
                                ✅ Finalizează Transport
                            </button>
                        ` : ''}
                        
                        <button class="btn-action btn-problem" onclick="reportProblem(${task.id})">
                            ⚠️ Raportează Problemă
                        </button>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        function getTaskClass(task) {
            if (task.transport_status === 'completed') return 'completed';
            
            if (task.scheduled_date) {
                const scheduledTime = new Date(task.scheduled_date);
                const now = new Date();
                const diffHours = (scheduledTime - now) / (1000 * 60 * 60);
                if (diffHours <= 2 && diffHours > 0) return 'urgent';
            }
            
            return '';
        }
        
        function getPriorityIcon(task) {
            if (task.scheduled_date) {
                const scheduledTime = new Date(task.scheduled_date);
                const now = new Date();
                const diffHours = (scheduledTime - now) / (1000 * 60 * 60);
                
                if (diffHours <= 1 && diffHours > 0) return '<span class="priority-high">🔴 URGENT</span>';
                if (diffHours <= 4 && diffHours > 0) return '<span class="priority-normal">🟡 PRIORITATE</span>';
            }
            return '<span class="priority-low">🟢 NORMAL</span>';
        }
        
        function getTransportStatusLabel(status) {
            const labels = {
                'pending': '⏳ În așteptare',
                'in_progress': '🚛 În transport',
                'completed': '✅ Finalizat'
            };
            return labels[status] || status;
        }
        
        function updateTaskStatistics(tasks) {
            const today = new Date().toISOString().split('T')[0];
            
            document.getElementById('total-tasks').textContent = tasks.length;
            document.getElementById('pending-tasks').textContent = 
                tasks.filter(task => task.transport_status === 'pending').length;
            document.getElementById('in-progress-tasks').textContent = 
                tasks.filter(task => task.transport_status === 'in_progress').length;
            document.getElementById('completed-today').textContent = 
                tasks.filter(task => 
                    task.transport_status === 'completed' && 
                    task.created_at.startsWith(today)
                ).length;
        }
        
        function getRouteStatusColor(status) {
            const colors = {
                'completed': '#27ae60',
                'current': '#f39c12',
                'pending': '#95a5a6'
            };
            return colors[status] || '#95a5a6';
        }
        
        function getRouteStatusIcon(status) {
            const icons = {
                'completed': '✅',
                'current': '🔄',
                'pending': '⏳'
            };
            return icons[status] || '⏳';
        }
        
        
        async function updateTaskStatus(taskId, newStatus) {
            try {
                const response = await fetch(`../api/index.php?resource=orders&action=updateStatus&id=${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify({ transport_status: newStatus })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadMyTasks(); 
                    if (newStatus === 'in_progress') {
                        alert('Transport pornit! Succes pe drum! 🚛');
                    } else if (newStatus === 'completed') {
                        alert('Transport finalizat cu succes! 🎉');
                    }
                } else {
                    alert('Eroare la actualizarea statusului');
                }
            } catch (error) {
                alert('Eroare de conexiune');
            }
        }
        
        function reportProblem(taskId) {
            const problem = prompt('Descrieți problema întâlnită:');
            if (problem) {
                alert(`Problema pentru comanda #${taskId} a fost raportată: "${problem}"\nManagerul va fi notificat.`);
            }
        }
        
        function logout() {
            if (confirm('Sigur vrei să te deconectezi?')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = 'login.html';
            }
        }