if (!localStorage.getItem('auth_token')) {
            window.location.href = 'login.html';
        }
        
        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        if (userData.role !== 'worker_cleaner') {
            alert('Acces neautorizat!');
            window.location.href = 'login.html';
        }
        
        let currentUser = null;
        let workTimer = null;
        let currentTaskId = null;
        let timerSeconds = 0;
        
        document.addEventListener('DOMContentLoaded', loadCleanerDashboard);
        
        async function loadCleanerDashboard() {
            try {
                const userResponse = await fetch('../api/auth.php?action=me', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                if (userResponse.ok) {
                    const result = await userResponse.json();
                    if (result.success) {
                        currentUser = result.user;
                        
                        document.getElementById('cleaner-info').innerHTML = `
                            <strong>${result.user.first_name} ${result.user.last_name}</strong><br>
                            Locația: ${result.user.location_name || 'Nedefinită'}
                        `;
                    }
                }
                
                await Promise.all([
                    loadMyCleaningTasks(),
                ]);
                
            } catch (error) {
                console.error('Eroare la încărcarea dashboard-ului cleaner:', error);
            }
        }
        
        async function loadMyCleaningTasks() {
            try {
                const response = await fetch('../api/index.php?resource=orders&action=assigned', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
                });
                
                const tasks = await response.json();
                displayCleaningTasks(tasks);
                updateTaskStatistics(tasks);
                
            } catch (error) {
                document.getElementById('my-cleaning-tasks').innerHTML = '<p>Eroare la încărcarea sarcinilor.</p>';
            }
        }
        
        function displayCleaningTasks(tasks) {
            const container = document.getElementById('my-cleaning-tasks');
            
            if (tasks.length === 0) {
                container.innerHTML = '<p>Nu aveți sarcini de curățare asignate.</p>';
                return;
            }
            
            const html = tasks.map(task => `
                <div class="cleaning-task ${getTaskClass(task)}">
                    <h4>Comanda #${task.id} ${getPriorityIcon(task)}</h4>
                    <p><strong>Client:</strong> ${task.client_name}</p>
                    
                    <div class="service-type service-${task.service_type}">
                        ${getServiceIcon(task.service_type)} ${getServiceLabel(task.service_type)}
                    </div>
                    
                    <div class="cleaning-details">
                        <p><strong>📍 Locație:</strong> ${task.pickup_address || 'Nedefinită'}</p>
                        ${task.scheduled_date ? `<p><strong>⏰ Programat:</strong> ${new Date(task.scheduled_date).toLocaleString('ro-RO')}</p>` : ''}
                        <p><strong>Status:</strong> ${getCleaningStatusLabel(task.cleaning_status)}</p>
                        ${task.price ? `<p><strong>💰 Valoare:</strong> ${task.price} RON</p>` : ''}
                    </div>
                    
                    ${task.notes ? `<p><strong>📝 Observații:</strong> ${task.notes}</p>` : ''}
                    
                    <div class="task-actions">
                        ${task.cleaning_status === 'pending' ? `
                            <button class="btn-action btn-start" onclick="startCleaningTask(${task.id})">
                                🧹 Începe Curățare
                            </button>
                        ` : ''}
                        
                        ${task.cleaning_status === 'in_progress' ? `
                            <button class="btn-action btn-complete" onclick="completeCleaningTask(${task.id})">
                                ✅ Finalizează Curățare
                            </button>
                            <button class="btn-action btn-pause" onclick="pauseCleaningTask(${task.id})">
                                ⏸️ Pauză
                            </button>
                        ` : ''}
                        
                        <button class="btn-action btn-problem" onclick="reportCleaningProblem(${task.id})">
                            ⚠️ Raportează Problemă
                        </button>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        function getTaskClass(task) {
            if (task.cleaning_status === 'completed') return 'completed';
            
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
                
                if (diffHours <= 1 && diffHours > 0) return '<span style="color: #e74c3c;">🔴 URGENT</span>';
                if (diffHours <= 4 && diffHours > 0) return '<span style="color: #f39c12;">🟡 PRIORITATE</span>';
            }
            return '<span style="color: #27ae60;">🟢 NORMAL</span>';
        }
        
        function getServiceIcon(serviceType) {
            const icons = {
                'covoare': '🧽',
                'auto': '🚗',
                'haine': '👔',
                'textile': '🏠'
            };
            return icons[serviceType] || '🧹';
        }
        
        function getServiceLabel(serviceType) {
            const labels = {
                'covoare': 'Spălare Covoare',
                'auto': 'Spălare Auto',
                'haine': 'Curățare Haine',
                'textile': 'Curățare Textile'
            };
            return labels[serviceType] || serviceType;
        }
        
        function getCleaningStatusLabel(status) {
            const labels = {
                'pending': '⏳ În așteptare',
                'in_progress': '🧹 În curățare',
                'completed': '✅ Finalizat'
            };
            return labels[status] || status;
        }
        
        function updateTaskStatistics(tasks) {
            const today = new Date().toISOString().split('T')[0];
            
            document.getElementById('total-tasks').textContent = tasks.length;
            document.getElementById('pending-tasks').textContent = 
                tasks.filter(task => task.cleaning_status === 'pending').length;
            document.getElementById('in-progress-tasks').textContent = 
                tasks.filter(task => task.cleaning_status === 'in_progress').length;
            document.getElementById('completed-today').textContent = 
                tasks.filter(task => 
                    task.cleaning_status === 'completed' && 
                    task.created_at.startsWith(today)
                ).length;
        }      
        
        async function startCleaningTask(taskId) {
            try {
                const response = await fetch(`../api/index.php?resource=orders&action=updateStatus&id=${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    body: JSON.stringify({ cleaning_status: 'in_progress' })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    currentTaskId = taskId;
                    document.getElementById('current-task').textContent = `Comanda #${taskId}`;
                    startTimer();
                    loadMyCleaningTasks();
                    alert('Curățare începută! Succes! 🧹');
                } else {
                    alert('Eroare la începerea curățării');
                }
            } catch (error) {
                alert('Eroare de conexiune');
            }
        }
        
        async function completeCleaningTask(taskId) {
            if (confirm('Confirmați finalizarea curățării? Asigurați-vă că toate standardele de calitate sunt îndeplinite.')) {
                try {
                    const response = await fetch(`../api/index.php?resource=orders&action=updateStatus&id=${taskId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                        },
                        body: JSON.stringify({ cleaning_status: 'completed' })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (currentTaskId === taskId) {
                            stopTimer();
                        }
                        loadMyCleaningTasks();
                        alert('Curățare finalizată cu succes! 🎉');
                    } else {
                        alert('Eroare la finalizarea curățării');
                    }
                } catch (error) {
                    alert('Eroare de conexiune');
                }
            }
        }
        
        function pauseCleaningTask(taskId) {
            if (currentTaskId === taskId) {
                pauseTimer();
                alert('Curățare pusă în pauză');
            }
        }
        
        function reportCleaningProblem(taskId) {
            const problems = [
                'Pete greu de îndepărtat',
                'Materiale deteriorate',
                'Echipament defect',
                'Produse de curățare insuficiente',
                'Altă problemă'
            ];
            
            const problemType = prompt(`Selectați tipul problemei pentru comanda #${taskId}:\n\n${problems.map((p, i) => `${i + 1}. ${p}`).join('\n')}\n\nSau descrieți problema:`);
            
            if (problemType) {
                alert(`Problema pentru comanda #${taskId} a fost raportată: "${problemType}"\nManagerul va fi notificat.`);
            }
        }
        
        function logout() {
            if (confirm('Sigur vrei să te deconectezi?')) {
                if (workTimer) {
                    if (confirm('Aveți timerul pornit. Doriți să îl opriți înainte de deconectare?')) {
                        stopTimer();
                    }
                }
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_data');
                window.location.href = 'login.html';
            }
        }