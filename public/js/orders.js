document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
    loadLocationsForSelect();
    loadOrderStatistics();
    setupOrderButtons();
    setupFilterListener();
});

async function loadOrders() {
    try {
        const orders = await OrdersAPI.getAll();
        displayOrders(orders);
    } catch (error) {
        document.getElementById('orders-list').innerHTML = 'Eroare la încărcarea comenzilor.';
    }
}

function displayOrders(orders) {
    const container = document.getElementById('orders-list');
    
    if (orders.length === 0) {
        container.innerHTML = '<p>Nu există comenzi înregistrate.</p>';
        return;
    }
    
    const html = orders.map(order => `
        <div class="order-item" data-status="${order.status}">
            <h3>Comanda #${order.id}</h3>
            <p><strong>Client:</strong> ${order.client_name}</p>
            <p><strong>Serviciu:</strong> ${order.service_type}</p>
            <p><strong>Locație:</strong> ${order.location_name || 'Nu este specificată'}</p>
            <p><strong>Status:</strong> 
                <select class="status-select" data-order-id="${order.id}">
                    <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>În așteptare</option>
                    <option value="in_progress" ${order.status === 'in_progress' ? 'selected' : ''}>În progres</option>
                    <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completată</option>
                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Anulată</option>
                </select>
            </p>
            <p><strong>Preț:</strong> ${order.price ? order.price + ' RON' : 'Nu este specificat'}</p>
            <p><strong>Data creării:</strong> ${new Date(order.created_at).toLocaleDateString('ro-RO')}</p>
            ${order.notes ? `<p><strong>Observații:</strong> ${order.notes}</p>` : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
    
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            updateOrderStatus(this.dataset.orderId, this.value);
        });
    });
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
        console.error('Error loading locations for select:', error);
    }
}

async function loadOrderStatistics() {
    try {
        const stats = await OrdersAPI.getStatistics();
        displayStatistics(stats);
    } catch (error) {
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
    
    try {
        await OrdersAPI.create(formData);
        hideOrderForm();
        loadOrders();
        loadOrderStatistics();
        alert('Comanda a fost adăugată cu succes!');
    } catch (error) {
        alert('Eroare la adăugarea comenzii.');
    }
}

async function updateOrderStatus(orderId, newStatus) {
    try {
        await OrdersAPI.updateStatus(orderId, newStatus);
        loadOrderStatistics(); 
        alert('Statusul a fost actualizat!');
    } catch (error) {
        alert('Eroare la actualizarea statusului.');
        loadOrders(); 
    }
}

function hideOrderForm() {
    document.getElementById('add-order-form').style.display = 'none';
    document.getElementById('order-form').reset();
}
