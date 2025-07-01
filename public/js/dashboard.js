document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    setupAutoRefresh();
});

async function loadDashboardData() {
    try {
        await Promise.all([
            loadOverallStatistics(),
            loadAlertsAndNotifications(),
            loadRecentActivity()
        ]);
        
        updateLastUpdateTime();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadOverallStatistics() {
    try {
        const [locations, orderStats, resourceStats] = await Promise.all([
            LocationsAPI.getAll(),
            OrdersAPI.getStatistics(),
            ResourcesAPI.getStatistics()
        ]);
        
        document.getElementById('total-locations').textContent = locations.length;
        document.getElementById('total-orders').textContent = orderStats.total_orders || 0;
        document.getElementById('total-resources').textContent = resourceStats.total_resources || 0;
        document.getElementById('total-revenue').textContent = 
            orderStats.total_revenue ? parseFloat(orderStats.total_revenue).toFixed(2) + ' RON' : '0 RON';
            
    } catch (error) {
        console.error('Error loading overall statistics:', error);
    }
}

async function loadAlertsAndNotifications() {
    try {
        const lowStockItems = await ResourcesAPI.getLowStock();
        const pendingOrders = await OrdersAPI.getAll();
        const pendingCount = pendingOrders.filter(order => order.status === 'pending').length;
        
        const alertsHtml = `
            <div class="alerts-grid">
                ${lowStockItems.length > 0 ? `
                    <div class="alert-card low-stock">
                        <h4>⚠️ Stoc Redus</h4>
                        <p>${lowStockItems.length} resurse au stoc sub pragul minim</p>
                        <button onclick="window.location.href='resources.html'">Vezi Detalii</button>
                    </div>
                ` : ''}
                
                ${pendingCount > 0 ? `
                    <div class="alert-card pending-orders">
                        <h4>📋 Comenzi în Așteptare</h4>
                        <p>${pendingCount} comenzi așteaptă procesare</p>
                        <button onclick="window.location.href='orders.html'">Vezi Comenzi</button>
                    </div>
                ` : ''}
                
                ${lowStockItems.length === 0 && pendingCount === 0 ? `
                    <div class="alert-card all-good">
                        <h4>✅ Totul în Regulă</h4>
                        <p>Nu există alerte active în sistem</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        document.getElementById('alerts-dashboard').innerHTML = alertsHtml;
        
    } catch (error) {
        document.getElementById('alerts-dashboard').innerHTML = '<p>Eroare la încărcarea alertelor.</p>';
    }
}

async function loadRecentActivity() {
    try {
        const orders = await OrdersAPI.getAll();
        const recentOrders = orders.slice(0, 5); // Ultimele 5 comenzi
        
        const activityHtml = recentOrders.map(order => `
            <div class="activity-item">
                <div class="activity-icon">📋</div>
                <div class="activity-content">
                    <h4>Comanda #${order.id}</h4>
                    <p>Client: ${order.client_name} | Serviciu: ${order.service_type}</p>
                    <p>Status: <span class="status-${order.status}">${order.status}</span></p>
                    <small>${new Date(order.created_at).toLocaleString('ro-RO')}</small>
                </div>
            </div>
        `).join('');
        
        document.getElementById('activity-feed').innerHTML = 
            activityHtml || '<p>Nu există activitate recentă.</p>';
            
    } catch (error) {
        document.getElementById('activity-feed').innerHTML = '<p>Eroare la încărcarea activității.</p>';
    }
}

function updateLastUpdateTime() {
    document.getElementById('last-update').textContent = new Date().toLocaleString('ro-RO');
}

function setupAutoRefresh() {
    // Refresh dashboard la fiecare 5 minute
    setInterval(loadDashboardData, 5 * 60 * 1000);
}
