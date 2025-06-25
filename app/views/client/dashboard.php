<div class="container">
    <div class="welcome-header">
        <h1>Bun venit, <?php echo htmlspecialchars($user['username'] ?? 'Client'); ?>!</h1>
        <p class="welcome-subtitle">Dashboard-ul dumneavoastră personal pentru serviciile CaS</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Statistics Overview -->
    <div class="stats-section">
        <h2>Statisticile Dumneavoastră</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                    <p>Total Comenzi</p>
                </div>
            </div>
            
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                    <p>Comenzi În Așteptare</p>
                </div>
            </div>
            
            <div class="stat-card progress">
                <div class="stat-icon">🔄</div>
                <div class="stat-content">
                    <h3><?php echo $stats['in_progress_orders'] ?? 0; ?></h3>
                    <p>Comenzi În Curs</p>
                </div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3><?php echo $stats['completed_orders'] ?? 0; ?></h3>
                    <p>Comenzi Finalizate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section">
        <h2>Acțiuni Rapide</h2>
        <div class="quick-actions">
            <a href="?controller=client&action=newOrder" class="action-card new-order">
                <div class="action-icon">➕</div>
                <h4>Comandă Nouă</h4>
                <p>Plasează o comandă nouă pentru serviciile noastre</p>
            </a>
            
            <a href="?controller=client&action=myOrders" class="action-card my-orders">
                <div class="action-icon">📋</div>
                <h4>Comenzile Mele</h4>
                <p>Vezi toate comenzile tale și statusul lor</p>
            </a>
            
            <a href="?controller=public&action=contact" class="action-card contact">
                <div class="action-icon">📞</div>
                <h4>Contact Support</h4>
                <p>Contactează echipa noastră pentru asistență</p>
            </a>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="section">
        <div class="section-header">
            <h2>Comenzile Recente</h2>
            <a href="?controller=client&action=myOrders" class="btn btn-primary">Vezi Toate Comenzile</a>
        </div>
        
        <div class="orders-container">
            <?php if (isset($recent_orders) && !empty($recent_orders)): ?>
                <?php foreach ($recent_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">
                                <h4>Comanda #<?php echo $order['id']; ?></h4>
                                <span class="order-date"><?php echo formatDate($order['DataProgramare']); ?></span>
                            </div>
                            <div class="order-status">
                                <?php echo getStatusBadge($order['Status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-content">
                            <div class="order-details">
                                <div class="detail-item">
                                    <span class="label">Serviciu:</span>
                                    <span class="value"><?php echo getServiceTypeName($order['TipServiciu']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Locația:</span>
                                    <span class="value"><?php echo htmlspecialchars($order['sediu_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Transport:</span>
                                    <span class="value"><?php echo $order['Transport'] ? '✅ Inclus' : '❌ Nu'; ?></span>
                                </div>
                                <?php if ($order['Recurenta']): ?>
                                <div class="detail-item">
                                    <span class="label">Recurentă:</span>
                                    <span class="value">✅ Da</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-actions">
                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-outline">
                                    Vezi Detalii
                                </button>
                                <?php if (in_array($order['Status'], ['noua', 'in curs'])): ?>
                                <button onclick="cancelOrder(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    Anulează
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-orders">
                    <div class="no-orders-icon">📋</div>
                    <h3>Nu aveți comenzi încă</h3>
                    <p>Începeți prin a plasa prima dumneavoastră comandă!</p>
                    <a href="?controller=client&action=newOrder" class="btn btn-primary">Comandă Acum</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Service Usage -->
    <?php if (isset($service_usage) && !empty($service_usage)): ?>
    <div class="section">
        <h2>Serviciile Dumneavoastră Favorite</h2>
        <div class="service-usage">
            <?php foreach ($service_usage as $usage): ?>
                <div class="usage-item">
                    <div class="usage-icon">
                        <?php 
                        echo $usage['TipServiciu'] === 'covor' ? '🏠' : 
                            ($usage['TipServiciu'] === 'auto' ? '🚗' : '👕'); 
                        ?>
                    </div>
                    <div class="usage-content">
                        <h4><?php echo getServiceTypeName($usage['TipServiciu']); ?></h4>
                        <p><?php echo $usage['count']; ?> comenzi</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Locations -->
    <div class="section">
        <h2>Locațiile Noastre</h2>
        <div class="locations-grid">
            <?php if (isset($locations) && !empty($locations)): ?>
                <?php foreach ($locations as $location): ?>
                    <div class="location-card">
                        <div class="location-header">
                            <h4><?php echo htmlspecialchars($location['Nume']); ?></h4>
                            <span class="location-status active">Disponibil</span>
                        </div>
                        <div class="location-info">
                            <p><strong>📍 Adresa:</strong> <?php echo htmlspecialchars($location['Adresa'] ?? 'N/A'); ?></p>
                            <?php if (!empty($location['Latitudine']) && !empty($location['Longitudine'])): ?>
                            <p><strong>🗺️ Coordonate:</strong> 
                               <?php echo number_format($location['Latitudine'], 4); ?>, 
                               <?php echo number_format($location['Longitudine'], 4); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="location-actions">
                            <button onclick="viewLocationDetails(<?php echo $location['id']; ?>)" 
                                    class="btn btn-sm btn-outline">
                                Vezi Detalii
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">Nu există locații disponibile în acest moment.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="orderModalTitle">Detalii Comandă</h3>
            <button class="modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="modal-body" id="orderModalBody">
            <div class="loading">Se încarcă...</div>
        </div>
    </div>
</div>

<!-- Location Details Modal -->
<div id="locationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="locationModalTitle">Detalii Locație</h3>
            <button class="modal-close" onclick="closeLocationModal()">&times;</button>
        </div>
        <div class="modal-body" id="locationModalBody">
            <div class="loading">Se încarcă...</div>
        </div>
    </div>
</div>

<style>
.welcome-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}

.welcome-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.welcome-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0;
}

.stats-section {
    margin-bottom: 3rem;
}

.stats-section h2 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
    border-left: 4px solid #3498db;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-card.pending {
    border-left-color: #f39c12;
}

.stat-card.progress {
    border-left-color: #e67e22;
}

.stat-card.completed {
    border-left-color: #27ae60;
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.stat-content h3 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.stat-content p {
    color: #666;
    margin: 0;
    font-weight: 500;
}

.section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.section h2 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    text-align: center;
    border: 2px solid transparent;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.action-card.new-order:hover {
    border-color: #27ae60;
    background: #f0fff4;
}

.action-card.my-orders:hover {
    border-color: #3498db;
    background: #f0f8ff;
}

.action-card.contact:hover {
    border-color: #e67e22;
    background: #fff8f0;
}

.action-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.action-card h4 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.action-card p {
    color: #666;
    margin: 0;
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.order-id h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
}

.order-date {
    color: #666;
    font-size: 0.9rem;
}

.order-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 2rem;
}

.order-details {
    flex: 1;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.detail-item .label {
    font-weight: 500;
    color: #666;
}

.detail-item .value {
    font-weight: 600;
    color: #2c3e50;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
    flex-direction: column;
    min-width: 120px;
}

.no-orders {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.no-orders-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.no-orders h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.service-usage {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.usage-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e9ecef;
}

.usage-icon {
    font-size: 2.5rem;
}

.usage-content h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1rem;
}

.usage-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.location-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.location-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.location-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.location-header h4 {
    margin: 0;
    color: #2c3e50;
}

.location-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.location-status.active {
    background: #d4edda;
    color: #155724;
}

.location-info {
    margin-bottom: 1rem;
}

.location-info p {
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
}

.location-actions {
    text-align: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-header {
    background: #3498db;
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 2rem;
}

.loading {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

@media (max-width: 768px) {
    .welcome-header h1 {
        font-size: 2rem;
    }
    
    .welcome-subtitle {
        font-size: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions,
    .locations-grid,
    .service-usage {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-content {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .order-actions {
        flex-direction: row;
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
    
    .stat-icon {
        font-size: 2.5rem;
    }
    
    .stat-content h3 {
        font-size: 2rem;
    }
}
</style>

<script>
// View order details via AJAX
function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const modalBody = document.getElementById('orderModalBody');
    const modalTitle = document.getElementById('orderModalTitle');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se încarcă detaliile comenzii...</div>';
    modalTitle.textContent = `Detalii Comandă #${orderId}`;
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?controller=client&action=viewOrder&id=${orderId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        displayOrderDetails(response.order);
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-error">${response.error}</div>`;
                    }
                } catch (e) {
                    modalBody.innerHTML = '<div class="alert alert-error">Eroare la parsarea răspunsului.</div>';
                }
            } else {
                modalBody.innerHTML = '<div class="alert alert-error">Eroare la încărcarea datelor.</div>';
            }
        }
    };
    
    xhr.send();
}

// Display order details in modal
function displayOrderDetails(order) {
    const modalBody = document.getElementById('orderModalBody');
    
    modalBody.innerHTML = `
        <div class="order-details-full">
            <div class="detail-section">
                <h4>Informații Comandă</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">ID Comandă:</span>
                        <span class="value">#${order.id}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Serviciu:</span>
                        <span class="value">${getServiceTypeName(order.TipServiciu)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Data Programare:</span>
                        <span class="value">${order.DataProgramare}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status:</span>
                        <span class="value">${getStatusText(order.Status)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Transport:</span>
                        <span class="value">${order.Transport ? 'Inclus' : 'Nu'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Recurentă:</span>
                        <span class="value">${order.Recurenta ? 'Da' : 'Nu'}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Locația Serviciului</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Nume:</span>
                        <span class="value">${order.sediu_name || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Adresa:</span>
                        <span class="value">${order.sediu_address || 'N/A'}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Close order modal
function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// View location details via AJAX
function viewLocationDetails(locationId) {
    const modal = document.getElementById('locationModal');
    const modalBody = document.getElementById('locationModalBody');
    const modalTitle = document.getElementById('locationModalTitle');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se încarcă detaliile locației...</div>';
    modalTitle.textContent = 'Detalii Locație';
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?controller=public&action=getLocationDetails&id=${locationId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    displayLocationDetails(response);
                } catch (e) {
                    modalBody.innerHTML = '<div class="alert alert-error">Eroare la parsarea răspunsului.</div>';
                }
            } else {
                modalBody.innerHTML = '<div class="alert alert-error">Eroare la încărcarea datelor.</div>';
            }
        }
    };
    
    xhr.send();
}

// Display location details
function displayLocationDetails(data) {
    const modalBody = document.getElementById('locationModalBody');
    const modalTitle = document.getElementById('locationModalTitle');
    
    if (data.error) {
        modalBody.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
        return;
    }
    
    const location = data.location;
    modalTitle.textContent = `Detalii: ${location.Nume}`;
    
    modalBody.innerHTML = `
        <div class="location-details-full">
            <div class="detail-section">
                <h4>Informații Generale</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="label">Nume:</span>
                        <span class="value">${location.Nume}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Adresa:</span>
                        <span class="value">${location.Adresa || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status:</span>
                        <span class="value">${location.Stare === 'activ' ? 'Disponibil' : 'Indisponibil'}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Servicii Disponibile</h4>
                <div class="services-list">
                    <span class="service-tag">🏠 Spălare covoare</span>
                    <span class="service-tag">🚗 Spălare auto</span>
                    <span class="service-tag">👕 Curățenie textile</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Program</h4>
                <p><strong>Luni - Vineri:</strong> 08:00 - 18:00<br>
                <strong>Sâmbătă:</strong> 09:00 - 15:00<br>
                <strong>Duminică:</strong> Închis</p>
            </div>
            
            <div class="modal-actions">
                <a href="?controller=client&action=newOrder&location=${location.id}" class="btn btn-primary">
                    Comandă la Această Locație
                </a>
            </div>
        </div>
    `;
}

// Close location modal
function closeLocationModal() {
    document.getElementById('locationModal').style.display = 'none';
}

// Cancel order function
function cancelOrder(orderId) {
    if (!confirm('Sigur doriți să anulați această comandă?')) {
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '?controller=client&action=cancelOrder', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Comanda a fost anulată cu succes.');
                        location.reload();
                    } else {
                        alert(response.error || 'Eroare la anularea comenzii.');
                    }
                } catch (e) {
                    alert('Eroare la procesarea răspunsului.');
                }
            } else {
                alert('Eroare la anularea comenzii.');
            }
        }
    };
    
    const params = `order_id=${orderId}&csrf_token=${window.csrfToken || ''}`;
    xhr.send(params);
}

// Helper functions
function getServiceTypeName(type) {
    const types = {
        'covor': 'Spălare covoare',
        'auto': 'Spălare auto',
        'textil': 'Curățenie textile'
    };
    return types[type] || type;
}

function getStatusText(status) {
    const statuses = {
        'noua': 'Nouă',
        'in curs': 'În curs',
        'finalizata': 'Finalizată',
        'anulata': 'Anulată'
    };
    return statuses[status] || status;
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    const orderModal = document.getElementById('orderModal');
    const locationModal = document.getElementById('locationModal');
    
    if (e.target === orderModal) {
        closeOrderModal();
    }
    if (e.target === locationModal) {
        closeLocationModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeOrderModal();
        closeLocationModal();
    }
});
</script>
