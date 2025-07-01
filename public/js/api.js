const API_BASE = '../api/index.php';

async function apiRequest(endpoint, options = {}) {
    try {
        const url = `${API_BASE}?${endpoint}`;
        console.log('Making API request to:', url);
        console.log('Request options:', options);
        
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },  
            ...options
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        return data;
    } catch (error) {
        console.error('API Request failed:', error);
        throw error;
    }
}

const LocationsAPI = {
    getAll: () => apiRequest('resource=locations&action=list'),
    
    create: (locationData) => apiRequest('resource=locations&action=create', {
        method: 'POST',
        body: JSON.stringify(locationData),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),
    
    updateStatus: (locationId, status) => apiRequest(`resource=locations&action=update-status&id=${locationId}`, {
        method: 'PUT',
        body: JSON.stringify({ status }),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    })
};

const OrdersAPI = {
    getAll: () => apiRequest('resource=orders&action=list'),

    getByLocation: (locationId) => apiRequest(`resource=orders&action=by-location&id=${locationId}`),

    getMyOrders: () => apiRequest('resource=orders&action=my-orders', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getAssigned: () => apiRequest('resource=orders&action=assigned', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    create: (orderData) => {
        console.log('Creating order with data:', orderData);
        return apiRequest('resource=orders&action=create', {
            method: 'POST',
            body: JSON.stringify(orderData),
            headers: {
                'Content-Type': 'application/json',
                'Authorization': localStorage.getItem('auth_token') ? 
                    `Bearer ${localStorage.getItem('auth_token')}` : undefined
            }
        });
    },

    updateStatus: (orderId, statusData) => apiRequest(`resource=orders&action=updateStatus&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify(typeof statusData === 'string' ? { status: statusData } : statusData),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    assignWorker: (orderId, workerId, workerType) => apiRequest(`resource=orders&action=assign-worker&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify({
            worker_id: workerId,
            worker_type: workerType
        }),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getStatistics: () => apiRequest('resource=orders&action=statistics'),

    getByTransportWorker: (workerId) => apiRequest(`resource=orders&action=by-transport-worker&id=${workerId}`, {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getByCleaningWorker: (workerId) => apiRequest(`resource=orders&action=by-cleaning-worker&id=${workerId}`, {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    })
};

const ResourcesAPI = {
    getAll: () => apiRequest('resource=resources&action=list'),
    
    getByLocation: (locationId) => apiRequest(`resource=resources&action=by-location&id=${locationId}`),
    
    getLowStock: (threshold) => {
        const params = threshold ? `&threshold=${threshold}` : '';
        return apiRequest(`resource=resources&action=low-stock${params}`);
    },
    
    create: (data) => apiRequest('resource=resources&action=create', {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    addQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=addQuantity`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    }),

    subtractQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=subtractQuantity`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    }),

    delete: (name, locationId) => apiRequest(`resource=resources&action=delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&location_id=${encodeURIComponent(locationId)}`
    })
};

const UsersAPI = {
    getList: (role = null) => {
        const params = role ? `&role=${role}` : '';
        return apiRequest(`../api/users.php?action=list${params}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            }
        });
    },

    create: (userData) => apiRequest('../api/users.php?action=create', {
        method: 'POST',
        body: JSON.stringify(userData),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    update: (userId, userData) => apiRequest(`../api/users.php?action=update&id=${userId}`, {
        method: 'PUT',
        body: JSON.stringify(userData),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    delete: (userId) => apiRequest(`../api/users.php?action=delete&id=${userId}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getProfile: () => apiRequest('../api/users.php?action=profile', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    updateProfile: (profileData) => apiRequest('../api/users.php?action=update-profile', {
        method: 'POST',
        body: JSON.stringify(profileData),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getWorkers: (locationId = null) => {
        const params = locationId ? `&location_id=${locationId}` : '';
        return apiRequest(`../api/users.php?action=workers${params}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            }
        });
    }
};

const AuthAPI = {
    login: (username, password) => apiRequest('../api/auth.php?action=login', {
        method: 'POST',
        body: JSON.stringify({ username, password })
    }),

    register: (userData) => apiRequest('../api/auth.php?action=register', {
        method: 'POST',
        body: JSON.stringify(userData)
    }),

    getMe: () => apiRequest('../api/auth.php?action=me', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    logout: () => apiRequest('../api/auth.php?action=logout', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    changePassword: (currentPassword, newPassword) => apiRequest('../api/auth.php?action=change-password', {
        method: 'POST',
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        }),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    })
};

const ExportAPI = {
    exportData: (type, format) => {
        const url = `../api/index.php?resource=export&type=${type}&format=${format}`;
        window.open(url, '_blank');
    }
};

// === FUNCȚII HELPER PENTRU COMPATIBILITATE ===

// Pentru client.html - încărcare comenzi proprii
async function loadMyOrdersForClient() {
    try {
        console.log('Loading my orders for client...');
        const orders = await OrdersAPI.getMyOrders();
        console.log('My orders loaded:', orders);
        displayOrdersForClient(orders);
    } catch (error) {
        console.error('Error loading my orders:', error);
        document.getElementById('orders-list').innerHTML = `<p>Eroare la încărcarea comenzilor: ${error.message}</p>`;
    }
}

function displayOrdersForClient(orders) {
    const container = document.getElementById('orders-list');
    
    if (!Array.isArray(orders) || orders.length === 0) {
        container.innerHTML = '<p>Nu aveți comenzi înregistrate încă.</p>';
        return;
    }
    
    const html = orders.map(order => `
        <div class="order-card ${order.status || 'pending'}">
            <h3>Comanda #${order.id}</h3>
            <p><strong>Serviciu:</strong> ${getServiceNameForClient(order.service_type)}</p>
            <p><strong>Status:</strong> ${getStatusLabelForClient(order.status)}</p>
            <p><strong>Data:</strong> ${order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută'}</p>
            ${order.scheduled_date ? `<p><strong>Programat pentru:</strong> ${new Date(order.scheduled_date).toLocaleString('ro-RO')}</p>` : ''}
            ${order.price ? `<p><strong>Preț:</strong> ${order.price} RON</p>` : ''}
            ${order.pickup_address ? `<p><strong>Ridicare din:</strong> ${order.pickup_address}</p>` : ''}
            ${order.delivery_address ? `<p><strong>Livrare la:</strong> ${order.delivery_address}</p>` : ''}
            ${order.notes ? `<p><strong>Observații:</strong> ${order.notes}</p>` : ''}
            ${order.location_name ? `<p><strong>Locația:</strong> ${order.location_name}</p>` : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function getServiceNameForClient(serviceType) {
    const services = {
        'covoare': 'Spălare Covoare',
        'auto': 'Spălare Auto',
        'haine': 'Curățare Haine',
        'textile': 'Curățare Textile'
    };
    return services[serviceType] || serviceType;
}

function getStatusLabelForClient(status) {
    const labels = {
        'pending': '⏳ În așteptare',
        'in_progress': '🔄 În progres',
        'completed': '✅ Completată',
        'cancelled': '❌ Anulată'
    };
    return labels[status] || '⏳ În așteptare';
}

// Funcție pentru crearea comenzii din client.html
async function createOrderFromClient(orderData) {
    console.log('Creating order from client with data:', orderData);
    
    // Validare
    if (!orderData.service_type || !orderData.location_id || !orderData.pickup_address) {
        throw new Error('Serviciul, locația și adresa de ridicare sunt obligatorii!');
    }
    
    try {
        const result = await OrdersAPI.create(orderData);
        console.log('Client order creation result:', result);
        
        if (result.success) {
            return { success: true, message: 'Comanda a fost plasată cu succes!' };
        } else {
            throw new Error(result.message || 'Eroare la plasarea comenzii');
        }
    } catch (error) {
        console.error('Error creating order from client:', error);
        throw error;
    }
}

// === FUNCȚII PENTRU ORDERS.HTML ===

async function loadOrders() {
    try {
        console.log('Loading orders...');
        const orders = await OrdersAPI.getAll();
        console.log('Orders loaded:', orders);
        displayOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('orders-list').innerHTML = `<p>Eroare la încărcarea comenzilor: ${error.message}</p>`;
    }
}

function displayOrders(orders) {
    const container = document.getElementById('orders-list');
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
    const canModifyStatus = ['admin', 'manager'].includes(userData.role);
    
    if (!Array.isArray(orders) || orders.length === 0) {
        container.innerHTML = '<p>Nu există comenzi înregistrate.</p>';
        return;
    }
    
    const html = orders.map(order => `
        <div class="order-item" data-status="${order.status || 'pending'}">
            <h3>Comanda #${order.id}</h3>
            <p><strong>Client:</strong> ${order.client_name || 'Necunoscut'}</p>
            <p><strong>Serviciu:</strong> ${order.service_type || 'Nedefinit'}</p>
            <p><strong>Locație:</strong> ${order.location_name || 'Nu este specificată'}</p>
            <p><strong>Status:</strong> 
                ${canModifyStatus ? `
                    <select class="status-select" data-order-id="${order.id}">
                        <option value="pending" ${(order.status === 'pending' || !order.status) ? 'selected' : ''}>În așteptare</option>
                        <option value="in_progress" ${order.status === 'in_progress' ? 'selected' : ''}>În progres</option>
                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completată</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Anulată</option>
                    </select>
                ` : `<span style="font-weight: bold; color: ${getStatusColorForDisplay(order.status)}">${getStatusLabelForDisplay(order.status)}</span>`}
            </p>
            <p><strong>Preț:</strong> ${order.price ? order.price + ' RON' : 'Nu este specificat'}</p>
            <p><strong>Data creării:</strong> ${order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută'}</p>
            ${order.pickup_address ? `<p><strong>Adresa de ridicare:</strong> ${order.pickup_address}</p>` : ''}
            ${order.delivery_address ? `<p><strong>Adresa de livrare:</strong> ${order.delivery_address}</p>` : ''}
            ${order.notes ? `<p><strong>Observații:</strong> ${order.notes}</p>` : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    // Adaugă event listeners pentru select-urile de status doar dacă utilizatorul poate modifica
    if (canModifyStatus) {
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                updateOrderStatus(this.dataset.orderId, this.value);
            });
        });
    }
}

function getStatusColorForDisplay(status) {
    const colors = {
        'pending': '#f39c12',
        'in_progress': '#3498db',
        'completed': '#27ae60',
        'cancelled': '#e74c3c'
    };
    return colors[status] || '#95a5a6';
}

function getStatusLabelForDisplay(status) {
    const labels = {
        'pending': 'În așteptare',
        'in_progress': 'În progres',
        'completed': 'Completată',
        'cancelled': 'Anulată'
    };
    return labels[status] || status;
}

async function loadLocationsForSelect() {
    try {
        console.log('Loading locations for select...');
        const locations = await LocationsAPI.getAll();
        console.log('Locations loaded:', locations);
        
        const select = document.getElementById('location-select');
        
        if (!Array.isArray(locations)) {
            console.error('Locations is not an array:', locations);
            return;
        }
        
        // Șterge opțiunile existente (păstrează prima)
        while (select && select.children.length > 1) {
            select.removeChild(select.lastChild);
        }
        
        if (select) {
            locations.forEach(location => {
                const option = document.createElement('option');
                option.value = location.id;
                option.textContent = location.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading locations for select:', error);
    }
}

async function loadOrderStatistics() {
    try {
        console.log('Loading order statistics...');
        const stats = await OrdersAPI.getStatistics();
        console.log('Statistics loaded:', stats);
        displayStatistics(stats);
    } catch (error) {
        console.error('Error loading statistics:', error);
        const statsContainer = document.getElementById('stats-content');
        if (statsContainer) {
            statsContainer.innerHTML = 'Eroare la încărcarea statisticilor.';
        }
    }
}

function displayStatistics(stats) {
    const container = document.getElementById('stats-content');
    if (!container) return;
    
    const html = `
        <div class="stats-grid">
            <div class="stat-item">
                <h4>Total comenzi</h4>
                <p class="stat-number">${stats.total_orders || 0}</p>
            </div>
            <div class="stat-item">
                <h4>În așteptare</h4>
                <p class="stat-number">${stats.pending_orders || 0}</p>
            </div>
            <div class="stat-item">
                <h4>Completate</h4>
                <p class="stat-number">${stats.completed_orders || 0}</p>
            </div>
            <div class="stat-item">
                <h4>Venit total</h4>
                <p class="stat-number">${stats.total_revenue ? parseFloat(stats.total_revenue).toFixed(2) + ' RON' : '0 RON'}</p>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

function setupOrderButtons() {
    const addBtn = document.getElementById('add-order-btn');
    const cancelBtn = document.getElementById('cancel-order-btn');
    const form = document.getElementById('order-form');
    
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const formElement = document.getElementById('add-order-form');
            if (formElement) {
                formElement.style.display = 'block';
            }
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            hideOrderForm();
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            addOrder();
        });
    }
}

function setupFilterListener() {
    const filter = document.getElementById('status-filter');
    if (filter) {
        filter.addEventListener('change', function() {
            filterOrdersByStatus(this.value);
        });
    }
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
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
    
    // Verifică din nou dacă poate adăuga comenzi
    if (!['admin', 'manager'].includes(userData.role)) {
        alert('Nu aveți permisiuni pentru a adăuga comenzi');
        return;
    }
    
    const formData = {
        location_id: document.getElementById('location-select')?.value || null,
        client_name: document.getElementById('client-name')?.value,
        client_phone: document.getElementById('client-phone')?.value || null,
        client_email: document.getElementById('client-email')?.value || null,
        service_type: document.getElementById('service-type')?.value,
        pickup_address: document.getElementById('pickup-address')?.value || null,
        delivery_address: document.getElementById('delivery-address')?.value || null,
        scheduled_date: document.getElementById('scheduled-date')?.value || null,
        price: document.getElementById('price')?.value || null,
        notes: document.getElementById('notes')?.value || null
    };
    
    console.log('Adding order with data:', formData);
    
    // Validare de bază
    if (!formData.client_name || !formData.service_type) {
        alert('Numele clientului și tipul serviciului sunt obligatorii!');
        return;
    }
    
    try {
        const result = await OrdersAPI.create(formData);
        console.log('Order creation result:', result);
        
        if (result.success) {
            alert('Comanda a fost adăugată cu succes!');
            hideOrderForm();
            loadOrders();
            loadOrderStatistics();
        } else {
            alert('Eroare la adăugarea comenzii: ' + (result.message || 'Eroare necunoscută'));
        }
    } catch (error) {
        console.error('Error adding order:', error);
        alert('Eroare la adăugarea comenzii: ' + error.message);
    }
}

async function updateOrderStatus(orderId, newStatus) {
    if (!newStatus) return;
    
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
    
    // Verifică din nou dacă poate modifica statusul
    if (!['admin', 'manager'].includes(userData.role)) {
        alert('Nu aveți permisiuni pentru a modifica statusul comenzilor');
        loadOrders(); // Reîncarcă pentru a reveni la starea anterioară
        return;
    }
    
    try {
        console.log(`Updating order ${orderId} to status ${newStatus}`);
        const result = await OrdersAPI.updateStatus(orderId, newStatus);
        console.log('Status update result:', result);
        
        if (result.success) {
            loadOrderStatistics(); 
            alert('Statusul a fost actualizat!');
        } else {
            alert('Eroare la actualizarea statusului: ' + (result.message || 'Eroare necunoscută'));
            loadOrders(); // Reîncarcă pentru a reveni la starea anterioară
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Eroare la actualizarea statusului: ' + error.message);
        loadOrders(); // Reîncarcă pentru a reveni la starea anterioară
    }
}

function hideOrderForm() {
    const form = document.getElementById('add-order-form');
    const orderForm = document.getElementById('order-form');
    
    if (form) {
        form.style.display = 'none';
    }
    if (orderForm) {
        orderForm.reset();
    }
}

// Export pentru utilizare în alte fișiere
if (typeof window !== 'undefined') {
    window.OrdersAPI = OrdersAPI;
    window.LocationsAPI = LocationsAPI;
    window.ResourcesAPI = ResourcesAPI;
    window.UsersAPI = UsersAPI;
    window.AuthAPI = AuthAPI;
    window.ExportAPI = ExportAPI;
    
    // Funcții helper pentru client.html
    window.loadMyOrdersForClient = loadMyOrdersForClient;
    window.createOrderFromClient = createOrderFromClient;
    window.displayOrdersForClient = displayOrdersForClient;
    window.getStatusLabelForClient = getStatusLabelForClient;
    window.getServiceNameForClient = getServiceNameForClient;
    
    // Funcții helper pentru orders.html
    window.loadOrders = loadOrders;
    window.displayOrders = displayOrders;
    window.loadLocationsForSelect = loadLocationsForSelect;
    window.loadOrderStatistics = loadOrderStatistics;
    window.displayStatistics = displayStatistics;
    window.setupOrderButtons = setupOrderButtons;
    window.setupFilterListener = setupFilterListener;
    window.filterOrdersByStatus = filterOrdersByStatus;
    window.addOrder = addOrder;
    window.updateOrderStatus = updateOrderStatus;
    window.hideOrderForm = hideOrderForm;
}
