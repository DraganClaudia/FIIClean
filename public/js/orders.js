 let currentUser = null;
        let userRole = null;

        document.addEventListener('DOMContentLoaded', async function() {
            await loadUserRole();
            setupUI();
            await Promise.all([
                loadOrders(),
                loadLocationsForSelect(),
                loadOrderStatistics()
            ]);
            setupEventListeners();
        });

        async function loadUserRole() {
            try {
                if (checkAuthStatus()) {
                    const userData = await AuthAPI.getMe();
                    if (userData.success) {
                        currentUser = userData.user;
                        userRole = userData.user.role;
                    }
                }
            } catch (error) {
                console.error('Eroare la încărcarea utilizatorului:', error);
            }
        }

        function setupUI() {
            if (['admin', 'manager'].includes(userRole)) {
                document.getElementById('add-order-btn').style.display = 'inline-block';
            }
        }

        async function loadOrders() {
            try {
                let orders;
                
                if (userRole === 'client') {
                    orders = await OrdersAPI.getMyOrders();
                } else if (userRole === 'worker_transport' || userRole === 'worker_cleaner') {
                    orders = await OrdersAPI.getAssigned();
                } else {
                    orders = await OrdersAPI.getAll();
                }
                
                displayOrders(orders);
            } catch (error) {
                document.getElementById('orders-list').innerHTML = '<p>Eroare la încărcarea comenzilor: ' + error.message + '</p>';
            }
        }

        function displayOrders(orders) {
            const container = document.getElementById('orders-list');
            const canModifyStatus = ['admin', 'manager'].includes(userRole);
            
            if (!Array.isArray(orders) || orders.length === 0) {
                container.innerHTML = '<p>Nu există comenzi înregistrate.</p>';
                return;
            }
            
            container.innerHTML = '';
            const template = document.getElementById('order-item-template');
            
            orders.forEach(order => {
                const clone = template.content.cloneNode(true);
                
                const orderItem = clone.querySelector('.order-item');
                orderItem.setAttribute('data-status', order.status || 'pending');
                
                clone.querySelector('.order-title').textContent = `Comanda #${order.id}`;
                
                const serviceType = clone.querySelector('.service-type');
                serviceType.textContent = getServiceName(order.service_type);
                serviceType.className = `service-type service-${order.service_type}`;
                
                clone.querySelector('.client-name').textContent = order.client_name || 'Necunoscut';
                
                const phoneElement = clone.querySelector('.client-phone');
                if (order.client_phone) {
                    phoneElement.querySelector('span').textContent = order.client_phone;
                } else {
                    phoneElement.style.display = 'none';
                }
                
                const emailElement = clone.querySelector('.client-email');
                if (order.client_email) {
                    emailElement.querySelector('span').textContent = order.client_email;
                } else {
                    emailElement.style.display = 'none';
                }
                
                clone.querySelector('.location-name').textContent = order.location_name || 'Nu este specificată';
                
                const pickupElement = clone.querySelector('.pickup-address');
                if (order.pickup_address) {
                    pickupElement.querySelector('span').textContent = order.pickup_address;
                } else {
                    pickupElement.style.display = 'none';
                }
                
                const deliveryElement = clone.querySelector('.delivery-address');
                if (order.delivery_address) {
                    deliveryElement.querySelector('span').textContent = order.delivery_address;
                } else {
                    deliveryElement.style.display = 'none';
                }
                
                const statusContainer = clone.querySelector('.status-container');
                if (canModifyStatus) {
                    const select = document.createElement('select');
                    select.className = 'status-select';
                    select.setAttribute('data-order-id', order.id);
                    
                    ['pending', 'in_progress', 'completed', 'cancelled'].forEach(status => {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = getStatusLabel(status);
                        option.selected = (order.status === status) || (!order.status && status === 'pending');
                        select.appendChild(option);
                    });
                    
                    statusContainer.appendChild(select);
                } else {
                    const span = document.createElement('span');
                    span.className = `status-${order.status || 'pending'}`;
                    span.textContent = getStatusLabel(order.status);
                    statusContainer.appendChild(span);
                }
                
                const priceElement = clone.querySelector('.order-price');
                if (order.price) {
                    priceElement.querySelector('span').textContent = `${order.price} RON`;
                } else {
                    priceElement.style.display = 'none';
                }
                
                clone.querySelector('.created-date').textContent = 
                    order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută';
                
                const scheduledElement = clone.querySelector('.scheduled-date');
                if (order.scheduled_date) {
                    scheduledElement.querySelector('span').textContent = 
                        new Date(order.scheduled_date).toLocaleString('ro-RO');
                } else {
                    scheduledElement.style.display = 'none';
                }
                
                const notesElement = clone.querySelector('.order-notes');
                if (order.notes) {
                    notesElement.querySelector('span').textContent = order.notes;
                } else {
                    notesElement.style.display = 'none';
                }
                
                const workerStatusElement = clone.querySelector('.worker-status');
                if (['worker_transport', 'worker_cleaner'].includes(userRole)) {
                    if (userRole === 'worker_transport' && order.transport_status) {
                        const transportSpan = workerStatusElement.querySelector('.transport-status span');
                        transportSpan.textContent = getTransportStatusLabel(order.transport_status);
                        transportSpan.className = `status-${order.transport_status}`;
                        workerStatusElement.querySelector('.cleaning-status').style.display = 'none';
                    } else if (userRole === 'worker_cleaner' && order.cleaning_status) {
                        const cleaningSpan = workerStatusElement.querySelector('.cleaning-status span');
                        cleaningSpan.textContent = getCleaningStatusLabel(order.cleaning_status);
                        cleaningSpan.className = `status-${order.cleaning_status}`;
                        workerStatusElement.querySelector('.transport-status').style.display = 'none';
                    } else {
                        workerStatusElement.style.display = 'none';
                    }
                } else {
                    workerStatusElement.style.display = 'none';
                }
                
                container.appendChild(clone);
            });
            
            if (canModifyStatus) {
                container.addEventListener('change', function(e) {
                    if (e.target.classList.contains('status-select')) {
                        const orderId = e.target.getAttribute('data-order-id');
                        updateOrderStatus(orderId, e.target.value);
                    }
                });
            }
        }

        function getTransportStatusLabel(status) {
            const labels = {
                'pending': '⏳ În așteptare',
                'in_progress': '🚛 În transport',
                'completed': '✅ Finalizat'
            };
            return labels[status] || status;
        }

        function getCleaningStatusLabel(status) {
            const labels = {
                'pending': '⏳ În așteptare',
                'in_progress': '🧹 În curățare',
                'completed': '✅ Finalizat'
            };
            return labels[status] || status;
        }

        async function loadLocationsForSelect() {
            try {
                const locations = await LocationsAPI.getAll();
                const select = document.getElementById('location-select');
                
                locations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = location.name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Eroare la încărcarea locațiilor:', error);
            }
        }

        async function loadOrderStatistics() {
            try {
                const stats = await OrdersAPI.getStatistics();
                displayStatistics(stats);
            } catch (error) {
                document.getElementById('stats-content').innerHTML = '<p>Eroare la încărcarea statisticilor.</p>';
            }
        }

        function displayStatistics(stats) {
            const container = document.getElementById('stats-content');
            
            const html = `
                <div class="stat-box">
                    <span class="stat-number">${stats.total_orders || 0}</span>
                    <span>Total Comenzi</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">${stats.pending_orders || 0}</span>
                    <span>În Așteptare</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">${stats.completed_orders || 0}</span>
                    <span>Completate</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">${stats.total_revenue ? parseFloat(stats.total_revenue).toFixed(2) + ' RON' : '0 RON'}</span>
                    <span>Venit Total</span>
                </div>
            `;
            
            container.innerHTML = html;
        }

        function setupEventListeners() {
            document.getElementById('add-order-btn')?.addEventListener('click', function() {
                document.getElementById('add-order-form').style.display = 'block';
            });
            
            document.getElementById('cancel-order-btn')?.addEventListener('click', function() {
                hideOrderForm();
            });
            
            document.getElementById('order-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                addOrder();
            });
            
            document.getElementById('status-filter').addEventListener('change', function() {
                filterOrdersByStatus(this.value);
            });
        }

        function filterOrdersByStatus(status) {
            const orders = document.querySelectorAll('.order-item');
            
            orders.forEach(order => {
                if (status === 'all' || order.dataset.status === status) {
                    order.style.display = 'block';
                } else {
                    order.style.display = 'none';
                }
            });
        }

        async function addOrder() {
            const formData = {
                location_id: document.getElementById('location-select').value,
                client_name: document.getElementById('client-name').value,
                client_phone: document.getElementById('client-phone').value,
                client_email: document.getElementById('client-email').value,
                service_type: document.getElementById('service-type').value,
                pickup_address: document.getElementById('pickup-address').value,
                delivery_address: document.getElementById('delivery-address').value,
                scheduled_date: document.getElementById('scheduled-date').value || null,
                price: document.getElementById('price').value || null,
                notes: document.getElementById('notes').value
            };
            
            // Validare
            if (!formData.client_name || !formData.service_type) {
                alert('Numele clientului și tipul serviciului sunt obligatorii!');
                return;
            }
            
            try {
                const result = await OrdersAPI.create(formData);
                
                if (result.success) {
                    alert('Comanda a fost adăugată cu succes!');
                    hideOrderForm();
                    loadOrders();
                    loadOrderStatistics();
                } else {
                    alert('Eroare la adăugarea comenzii: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la adăugarea comenzii: ' + error.message);
            }
        }

        async function updateOrderStatus(orderId, newStatus) {
            if (!newStatus) return;
            
            try {
                const result = await OrdersAPI.updateStatus(orderId, newStatus);
                
                if (result.success) {
                    loadOrderStatistics();
                    alert('Statusul a fost actualizat!');
                } else {
                    alert('Eroare la actualizarea statusului: ' + (result.message || 'Eroare necunoscută'));
                    loadOrders(); 
                }
            } catch (error) {
                alert('Eroare la actualizarea statusului: ' + error.message);
                loadOrders(); 
            }
        }

        function hideOrderForm() {
            document.getElementById('add-order-form').style.display = 'none';
            document.getElementById('order-form').reset();
        }