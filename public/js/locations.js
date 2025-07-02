 let currentUser = null;
        let allLocations = [];

        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
        });

        async function initializePage() {
            const token = localStorage.getItem('auth_token');
            const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
            
            if (!token) {
                showAccessDenied();
                return;
            }

            currentUser = userData;
            
            setupNavigation();
            
            await loadLocations();
            setupEventListeners();
            
            document.getElementById('main-content').style.display = 'block';
        }

        function showAccessDenied() {
            document.getElementById('access-denied').style.display = 'block';
        }

        function setupNavigation() {
            document.getElementById('dashboard-nav').href = 'admin.html';
        }

        function setupEventListeners() {
            document.getElementById('status-filter').addEventListener('change', function() {
                filterByStatus(this.value);
            });
            
            document.getElementById('location-form').addEventListener('submit', function(e) {
                e.preventDefault();
                createLocation();
            });
        }

        async function loadLocations() {
            try {
                allLocations = await LocationsAPI.getAll();
                displayLocations(allLocations);
            } catch (error) {
                console.error('Eroare la încărcarea locațiilor:', error);
                document.getElementById('locations-list').innerHTML = `<p>Eroare la încărcarea locațiilor: ${error.message}</p>`;
            }
        }

        function displayLocations(locations) {
            const container = document.getElementById('locations-list');
            
            if (!Array.isArray(locations) || locations.length === 0) {
                container.innerHTML = '<p>Nu există locații înregistrate.</p>';
                return;
            }
            
            const html = locations.map(location => createLocationHTML(location)).join('');
            container.innerHTML = html;
            
            setupLocationEventListeners();
        }

        function createLocationHTML(location) {
            const statusClass = location.status || 'active';
            const statusIcon = getStatusIcon(location.status);
            const statusLabel = getStatusLabel(location.status);
            
            return `
                <div class="location-item ${statusClass}" data-status="${location.status}" data-id="${location.id}">
                    <div class="location-info">
                        <h3>${statusIcon} ${location.name}</h3>
                        <p><strong>📍 Adresă:</strong> ${location.address}</p>
                        
                        ${location.latitude && location.longitude ? `
                            <p><strong>🌍 Coordonate:</strong> ${location.latitude}, ${location.longitude}</p>
                        ` : ''}
                        
                        <p><strong>🛠️ Servicii:</strong> ${formatServices(location.services)}</p>
                        
                        <p><strong>📅 Creat:</strong> ${new Date(location.created_at).toLocaleDateString('ro-RO')}</p>
                        ${location.updated_at && location.updated_at !== location.created_at ? `
                            <p><strong>🔄 Actualizat:</strong> ${new Date(location.updated_at).toLocaleDateString('ro-RO')}</p>
                        ` : ''}
                        
                        <p><strong>🆔 ID:</strong> ${location.id}</p>
                    </div>
                    
                    <div class="location-actions">
                        ${this.userRole === 'admin' ? `
                            <select class="status-select status-${statusClass}" data-location-id="${location.id}" onchange="updateLocationStatus(${location.id}, this.value)">
                                <!-- opțiuni status -->
                            </select>
                            <button class="action-btn btn-primary" onclick="viewLocationDetails(${location.id})">📊 Detalii</button>
                            <button class="action-btn btn-warning" onclick="editLocation(${location.id})">✏️ Editează</button>
                        ` : `
                            <span class="location-status">Status: ${getStatusLabel(location.status)}</span>
                        `}
                    </div>
                </div>
            `;
        }   
        function getStatusIcon(status) {
            const icons = {
                'active': '🟢',
                'inactive': '🔴',
                'maintenance': '🔧'
            };
            return icons[status] || '🟢';
        }

        function getStatusLabel(status) {
            const labels = {
                'active': 'Activ',
                'inactive': 'Inactiv', 
                'maintenance': 'Mentenanță'
            };
            return labels[status] || 'Activ';
        }

        function formatServices(services) {
            if (!services) return 'Nu sunt specificate';
            
            const serviceIcons = {
                'covoare': '🧽 Covoare',
                'auto': '🚗 Auto',
                'haine': '👔 Haine',
                'textile': '🏠 Textile'
            };
            
            return services.split(',').map(service => {
                const trimmed = service.trim();
                return serviceIcons[trimmed] || trimmed;
            }).join(', ');
        }


        function filterByStatus(status) {
            const locations = document.querySelectorAll('.location-item');
            
            locations.forEach(location => {
                if (status === 'all' || location.dataset.status === status) {
                    location.style.display = 'flex';
                } else {
                    location.style.display = 'none';
                }
            });
            
            document.getElementById('status-filter').value = status;
        }

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
                    alert(`Status-ul locației a fost actualizat la: ${getStatusLabel(newStatus)}`);
                    await loadLocations(); 
                } else {
                    alert('Eroare la actualizarea status-ului: ' + (result.error || 'Eroare necunoscută'));
                    await loadLocations(); 
                }
            } catch (error) {
                alert('Eroare de conexiune: ' + error.message);
                await loadLocations();
            }
        }

        function showCreateLocationModal() {
            document.getElementById('location-modal').style.display = 'block';
        }

        function closeLocationModal() {
            document.getElementById('location-modal').style.display = 'none';
            document.getElementById('location-form').reset();
        }

        async function createLocation() {
            const locationData = {
                name: document.getElementById('location-name').value,
                address: document.getElementById('location-address').value,
                latitude: document.getElementById('location-latitude').value || null,
                longitude: document.getElementById('location-longitude').value || null,
                services: document.getElementById('location-services').value || null
            };
            
            if (!locationData.name || !locationData.address) {
                alert('Numele și adresa locației sunt obligatorii!');
                return;
            }
            
            try {
                const result = await LocationsAPI.create(locationData);
                
                if (result.success) {
                    alert('Locația a fost creată cu succes!');
                    closeLocationModal();
                    await loadLocations();
                } else {
                    alert('Eroare la crearea locației: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la crearea locației: ' + error.message);
            }
        }

        function refreshLocations() {
            loadLocations();
            document.getElementById('status-filter').value = 'all';
        }

        async function viewLocationDetails(locationId) {
            try {
                const orders = await OrdersAPI.getByLocation(locationId);
                const location = allLocations.find(loc => loc.id == locationId);
                
                if (!location) {
                    alert('Locația nu a fost găsită!');
                    return;
                }
                
                const activeOrders = orders.filter(order => order.status !== 'completed' && order.status !== 'cancelled').length;
                const completedOrders = orders.filter(order => order.status === 'completed').length;
                
                alert(`📊 Detalii pentru ${location.name}:\n\n` +
                      `📍 Adresă: ${location.address}\n` +
                      `📋 Comenzi active: ${activeOrders}\n` +
                      `✅ Comenzi completate: ${completedOrders}\n` +
                      `📅 Creat: ${new Date(location.created_at).toLocaleDateString('ro-RO')}\n` +
                      `🛠️ Servicii: ${location.services || 'Nu sunt specificate'}`);
                      
            } catch (error) {
                alert('Eroare la încărcarea detaliilor: ' + error.message);
            }
        }

        function editLocation(locationId) {
            const location = allLocations.find(loc => loc.id == locationId);
            
            if (!location) {
                alert('Locația nu a fost găsită!');
                return;
            }
            
            document.getElementById('location-name').value = location.name;
            document.getElementById('location-address').value = location.address;
            document.getElementById('location-latitude').value = location.latitude || '';
            document.getElementById('location-longitude').value = location.longitude || '';
            document.getElementById('location-services').value = location.services || '';
            
            document.querySelector('#location-modal h2').textContent = 'Editează Locația';
            
            alert('Funcția de editare va fi implementată în curând.\nPentru moment, puteți crea o locație nouă cu datele actualizate.');
            
            showCreateLocationModal();
        }

        window.onclick = function(event) {
            const modal = document.getElementById('location-modal');
            if (event.target === modal) {
                closeLocationModal();
            }
        }