if (!checkAuthStatus()) {
            window.location.href = 'login.html';
        }

        let currentUser = null;

        document.addEventListener('DOMContentLoaded', loadClientDashboard);

        async function loadClientDashboard() {
            try {
                const userData = await AuthAPI.getMe();
                if (userData.success) {
                    currentUser = userData.user;
                    document.getElementById('user-info').innerHTML = `
                        <strong>${userData.user.first_name} ${userData.user.last_name}</strong><br>
                        📧 ${userData.user.email}
                        ${userData.user.phone ? `<br>📞 ${userData.user.phone}` : ''}
                    `;
                }
                
                await Promise.all([
                    loadMyOrders(),
                    loadLocations()
                ]);
                
                const quickService = localStorage.getItem('quick_order_service');
                if (quickService) {
                    localStorage.removeItem('quick_order_service');
                    quickOrder(quickService);
                }
                
            } catch (error) {
                console.error('Eroare la încărcarea dashboard-ului:', error);
            }
        }

        async function loadMyOrders() {
            try {
                const orders = await OrdersAPI.getMyOrders();
                displayOrders(orders);
            } catch (error) {
                document.getElementById('orders-list').innerHTML = '<p>Eroare la încărcarea comenzilor.</p>';
            }
        }

        function displayOrders(orders) {
            const container = document.getElementById('orders-list');
            
            if (!Array.isArray(orders) || orders.length === 0) {
                container.innerHTML = '<p>Nu aveți comenzi înregistrate încă.</p>';
                return;
            }
            
            const html = orders.slice(0, 5).map(order => `
                <div class="order-item ${order.status || 'pending'}" style="margin-bottom: 1rem;">
                    <h4>Comanda #${order.id}</h4>
                    <p><strong>Serviciu:</strong> ${getServiceName(order.service_type)}</p>
                    <p><strong>Status:</strong> ${getStatusLabel(order.status)}</p>
                    <p><strong>Data:</strong> ${order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută'}</p>
                    ${order.scheduled_date ? `<p><strong>Programat pentru:</strong> ${new Date(order.scheduled_date).toLocaleString('ro-RO')}</p>` : ''}
                    ${order.price ? `<p><strong>Preț:</strong> ${order.price} RON</p>` : ''}
                    ${order.pickup_address ? `<p><strong>Ridicare din:</strong> ${order.pickup_address}</p>` : ''}
                    ${order.delivery_address ? `<p><strong>Livrare la:</strong> ${order.delivery_address}</p>` : ''}
                </div>
            `).join('');
            
            container.innerHTML = html + (orders.length > 5 ? 
                '<div style="text-align: center; margin-top: 1rem;"><a href="orders.html" class="btn btn-info">Vezi Toate Comenzile</a></div>' : '');
        }

        async function loadLocations() {
            try {
                const locations = await LocationsAPI.getAll();
                const select = document.getElementById('location-select');
                
                while (select.children.length > 1) {
                    select.removeChild(select.lastChild);
                }
                
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

        function quickOrder(serviceType = null) {
            if (serviceType) {
                document.getElementById('service-type').value = serviceType;
            }
            showNewOrderModal();
        }

        function showNewOrderModal() {
            document.getElementById('order-modal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('order-modal').style.display = 'none';
            document.getElementById('new-order-form').reset();
        }

        document.getElementById('new-order-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const orderData = {
                service_type: document.getElementById('service-type').value,
                location_id: document.getElementById('location-select').value,
                pickup_address: document.getElementById('pickup-address').value,
                delivery_address: document.getElementById('delivery-address').value,
                scheduled_date: document.getElementById('scheduled-date').value || null,
                notes: document.getElementById('order-notes').value
            };
            
            if (!orderData.service_type || !orderData.location_id || !orderData.pickup_address) {
                alert('Serviciul, locația și adresa de ridicare sunt obligatorii!');
                return;
            }
            
            try {
                const result = await OrdersAPI.create(orderData);
                
                if (result.success) {
                    alert('Comanda a fost plasată cu succes!');
                    closeOrderModal();
                    loadMyOrders();
                } else {
                    alert('Eroare la plasarea comenzii: ' + (result.message || result.error || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare de conexiune: ' + error.message);
            }
        });

        window.onclick = function(event) {
            const modal = document.getElementById('order-modal');
            if (event.target === modal) {
                closeOrderModal();
            }
        }