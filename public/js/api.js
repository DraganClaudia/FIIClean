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
        body: JSON.stringify(locationData)
    })
};

const OrdersAPI = {
    getAll: () => apiRequest('resource=orders&action=list'),

    getByLocation: (locationId) => apiRequest(`resource=orders&action=by-location&id=${locationId}`),

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

    updateStatus: (orderId, status) => apiRequest(`resource=orders&action=updateStatus&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify({status: status}),
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    }),

    getStatistics: () => apiRequest('resource=orders&action=statistics'),

    getMyOrders: () => apiRequest('resource=orders&action=my-orders', {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
    })
};

const ResourcesAPI = {
    getAll: () => apiRequest('resource=resources&action=list'),
    
    create: (data) => apiRequest('resource=resources&action=create', {
        method: 'POST',
        body: JSON.stringify(data)
    }),

    addQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=addQuantity`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    }),

    subtractQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=subtractQuantity`, {
        method: 'PUT',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    })
};

// === UPDATED ORDERS.JS ===
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    loadLocationsForSelect();
    loadOrderStatistics();
    setupOrderButtons();
    setupFilterListener();
});

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
                <select class="status-select" data-order-id="${order.id}">
                    <option value="pending" ${(order.status === 'pending' || !order.status) ? 'selected' : ''}>În așteptare</option>
                    <option value="in_progress" ${order.status === 'in_progress' ? 'selected' : ''}>În progres</option>
                    <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completată</option>
                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Anulată</option>
                </select>
            </p>
            <p><strong>Preț:</strong> ${order.price ? order.price + ' RON' : 'Nu este specificat'}</p>
            <p><strong>Data creării:</strong> ${order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută'}</p>
            ${order.pickup_address ? `<p><strong>Adresa de ridicare:</strong> ${order.pickup_address}</p>` : ''}
            ${order.delivery_address ? `<p><strong>Adresa de livrare:</strong> ${order.delivery_address}</p>` : ''}
            ${order.notes ? `<p><strong>Observații:</strong> ${order.notes}</p>` : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    // Adaugă event listeners pentru select-urile de status
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            updateOrderStatus(this.dataset.orderId, this.value);
        });
    });
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
        document.getElementById('stats-content').innerHTML = 'Eroare la încărcarea statisticilor.';
    }
}

function displayStatistics(stats) {
    const container = document.getElementById('stats-content');
    
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
    document.getElementById('add-order-btn').addEventListener('click', function() {
        document.getElementById('add-order-form').style.display = 'block';
    });
    
    document.getElementById('cancel-order-btn').addEventListener('click', function() {
        hideOrderForm();
    });
    
    document.getElementById('order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addOrder();
    });
}

function setupFilterListener() {
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
        location_id: document.getElementById('location-select').value || null,
        client_name: document.getElementById('client-name').value,
        client_phone: document.getElementById('client-phone').value || null,
        client_email: document.getElementById('client-email').value || null,
        service_type: document.getElementById('service-type').value,
        pickup_address: document.getElementById('pickup-address').value || null,
        delivery_address: document.getElementById('delivery-address').value || null,
        scheduled_date: document.getElementById('scheduled-date').value || null,
        price: document.getElementById('price').value || null,
        notes: document.getElementById('notes').value || null
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
    document.getElementById('add-order-form').style.display = 'none';
    document.getElementById('order-form').reset();
}

// === CLIENT.HTML ORDERS FUNCTIONS ===

// Funcție pentru client.html - încărcare comenzi proprii
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
            <p><strong>Serviciu:</strong> ${order.service_type || 'Nedefinit'}</p>
            <p><strong>Status:</strong> ${getStatusLabelForClient(order.status)}</p>
            <p><strong>Data:</strong> ${order.created_at ? new Date(order.created_at).toLocaleDateString('ro-RO') : 'Necunoscută'}</p>
            ${order.scheduled_date ? `<p><strong>Programat pentru:</strong> ${new Date(order.scheduled_date).toLocaleDateString('ro-RO')}</p>` : ''}
            ${order.price ? `<p><strong>Preț:</strong> ${order.price} RON</p>` : ''}
            ${order.pickup_address ? `<p><strong>Ridicare din:</strong> ${order.pickup_address}</p>` : ''}
            ${order.delivery_address ? `<p><strong>Livrare la:</strong> ${order.delivery_address}</p>` : ''}
            ${order.notes ? `<p><strong>Observații:</strong> ${order.notes}</p>` : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
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

// Export pentru utilizare în alte fișiere
if (typeof window !== 'undefined') {
    window.OrdersAPI = OrdersAPI;
    window.loadMyOrdersForClient = loadMyOrdersForClient;
    window.createOrderFromClient = createOrderFromClient;
    window.displayOrdersForClient = displayOrdersForClient;
    window.getStatusLabelForClient = getStatusLabelForClient;
}
