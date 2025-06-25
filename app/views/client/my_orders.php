<div class="container">
    <div class="page-header">
        <h1>Comenzile Mele</h1>
        <div class="breadcrumb">
            <a href="?controller=client&action=dashboard">Dashboard</a> > <span class="active">Comenzile Mele</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Filters and Controls -->
    <div class="section">
        <div class="controls-header">
            <h2>Filtrare și Căutare</h2>
            <div class="controls-actions">
                <a href="?controller=client&action=newOrder" class="btn btn-primary">+ Comandă Nouă</a>
                <a href="?controller=client&action=exportOrders&csrf_token=<?php echo $csrf_token; ?>" 
                   class="btn btn-export">📄 Export CSV</a>
            </div>
        </div>
        
        <form method="GET" action="" class="filters-form">
            <input type="hidden" name="controller" value="client">
            <input type="hidden" name="action" value="myOrders">
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="status">Status Comandă</label>
                    <select id="status" name="status">
                        <option value="">Toate statusurile</option>
                        <?php if (isset($filter_options['status'])): ?>
                            <?php foreach ($filter_options['status'] as $key => $value): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo ($filters['status'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($value); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="service">Tip Serviciu</label>
                    <select id="service" name="service">
                        <option value="">Toate serviciile</option>
                        <?php if (isset($filter_options['service'])): ?>
                            <?php foreach ($filter_options['service'] as $key => $value): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo ($filters['service'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($value); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Căutare</label>
                    <input type="text" id="search" name="search" 
                           placeholder="Caută după ID, locație..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtrează</button>
                    <a href="?controller=client&action=myOrders" class="btn btn-secondary">Resetează</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div class="section">
        <div class="section-header">
            <h2>Lista Comenzilor</h2>
            <div class="orders-count">
                Total: <strong><?php echo $pagination['total_orders'] ?? 0; ?></strong> comenzi
            </div>
        </div>
        
        <div class="orders-container">
            <?php if (isset($orders) && !empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card" data-order-id="<?php echo $order['id']; ?>">
                        <div class="order-header">
                            <div class="order-main-info">
                                <h3>Comanda #<?php echo $order['id']; ?></h3>
                                <div class="order-meta">
                                    <span class="order-date">📅 <?php echo formatDate($order['DataProgramare']); ?></span>
                                    <span class="order-service">🔧 <?php echo getServiceTypeName($order['TipServiciu']); ?></span>
                                </div>
                            </div>
                            <div class="order-status">
                                <?php echo getStatusBadge($order['Status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-details">
                                <div class="detail-row">
                                    <div class="detail-item">
                                        <span class="detail-icon">🏢</span>
                                        <div class="detail-content">
                                            <span class="detail-label">Locația</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($order['sediu_name'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-icon">🚚</span>
                                        <div class="detail-content">
                                            <span class="detail-label">Transport</span>
                                            <span class="detail-value"><?php echo $order['Transport'] ? 'Inclus' : 'Nu'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($order['Recurenta']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">🔄</span>
                                        <div class="detail-content">
                                            <span class="detail-label">Recurentă</span>
                                            <span class="detail-value">Da</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-outline">
                                    👁️ Vezi Detalii
                                </button>
                                
                                <?php if ($order['Status'] === 'finalizata'): ?>
                                <button onclick="rateOrder(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-primary">
                                    ⭐ Evaluează
                                </button>
                                <?php elseif (in_array($order['Status'], ['noua', 'in curs'])): ?>
                                <button onclick="cancelOrder(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-danger">
                                    ❌ Anulează
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="getOrderHistory(<?php echo $order['id']; ?>)" 
                                        class="btn btn-sm btn-info">
                                    📋 Istoric
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-orders">
                    <div class="no-orders-icon">📋</div>
                    <h3>Nu aveți comenzi</h3>
                    <p>Nu s-au găsit comenzi cu criteriile selectate.</p>
                    <div class="no-orders-actions">
                        <a href="?controller=client&action=newOrder" class="btn btn-primary">Comandă Acum</a>
                        <a href="?controller=client&action=myOrders" class="btn btn-secondary">Vezi Toate</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="pagination-section">
            <?php
            $base_url = '?controller=client&action=myOrders';
            if (!empty($filters['status'])) $base_url .= '&status=' . urlencode($filters['status']);
            if (!empty($filters['service'])) $base_url .= '&service=' . urlencode($filters['service']);
            if (!empty($_GET['search'])) $base_url .= '&search=' . urlencode($_GET['search']);
            
            echo generatePagination(
                $pagination['current_page'],
                $pagination['total_pages'],
                $base_url
            );
            ?>
        </div>
        <?php endif; ?>
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

<!-- Rate Order Modal -->
<div id="rateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Evaluează Comanda</h3>
            <button class="modal-close" onclick="closeRateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="ratingForm" onsubmit="submitRating(event)">
                <input type="hidden" id="rate_order_id" name="order_id">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="rating-section">
                    <label>Rating (1-5 stele):</label>
                    <div class="star-rating">
                        <span class="star" data-rating="1">⭐</span>
                        <span class="star" data-rating="2">⭐</span>
                        <span class="star" data-rating="3">⭐</span>
                        <span class="star" data-rating="4">⭐</span>
                        <span class="star" data-rating="5">⭐</span>
                    </div>
                    <input type="hidden" id="rating_value" name="rating" required>
                </div>
                
                <div class="form-group">
                    <label for="review">Comentariu (opțional):</label>
                    <textarea id="review" name="review" rows="4" 
                              placeholder="Cum ați evalua serviciul nostru?"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Trimite Evaluarea</button>
                    <button type="button" onclick="closeRateModal()" class="btn btn-secondary">Anulează</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order History Modal -->
<div id="historyModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="historyModalTitle">Istoric Comandă</h3>
            <button class="modal-close" onclick="closeHistoryModal()">&times;</button>
        </div>
        <div class="modal-body" id="historyModalBody">
            <div class="loading">Se încarcă...</div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb .active {
    color: #333;
    font-weight: 600;
}

.section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.controls-actions {
    display: flex;
    gap: 1rem;
}

.filters-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.orders-count {
    color: #666;
    font-size: 0.9rem;
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.order-header {
    background: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.order-main-info h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.order-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.order-body {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 2rem;
}

.order-details {
    flex: 1;
}

.detail-row {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 150px;
}

.detail-icon {
    font-size: 1.2rem;
    opacity: 0.7;
}

.detail-content {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.8rem;
    color: #666;
    font-weight: 500;
}

.detail-value {
    font-size: 0.9rem;
    color: #2c3e50;
    font-weight: 600;
}

.order-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
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

.no-orders-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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
    font-size: 0.9rem;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-export {
    background: #27ae60;
    color: white;
}

.btn-export:hover {
    background: #229954;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
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

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.pagination-section {
    margin-top: 2rem;
    text-align: center;
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

.rating-section {
    margin-bottom: 1.5rem;
    text-align: center;
}

.star-rating {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1rem 0;
}

.star {
    font-size: 2rem;
    cursor: pointer;
    opacity: 0.3;
    transition: opacity 0.3s ease;
}

.star.active,
.star:hover {
    opacity: 1;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .controls-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .controls-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-body {
        flex-direction: column;
        align-items: stretch;
        gap: 1.5rem;
    }
    
    .detail-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-actions {
        flex-direction: row;
        flex-wrap: wrap;
        min-width: auto;
    }
    
    .no-orders-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .order-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .detail-item {
        min-width: auto;
    }
}
</style>

<script>
// View order details
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

// Display order details
function displayOrderDetails(order) {
    const modalBody = document.getElementById('orderModalBody');
    
    modalBody.innerHTML = `
        <div class="order-details-full">
            <div class="detail-section">
                <h4>Informații Comandă</h4>
                <div class="detail-grid">
                    <div class="detail-item"><span class="label">ID:</span><span class="value">#${order.id}</span></div>
                    <div class="detail-item"><span class="label">Serviciu:</span><span class="value">${getServiceTypeName(order.TipServiciu)}</span></div>
                    <div class="detail-item"><span class="label">Data:</span><span class="value">${order.DataProgramare}</span></div>
                    <div class="detail-item"><span class="label">Status:</span><span class="value">${getStatusText(order.Status)}</span></div>
                    <div class="detail-item"><span class="label">Transport:</span><span class="value">${order.Transport ? 'Inclus' : 'Nu'}</span></div>
                    <div class="detail-item"><span class="label">Recurentă:</span><span class="value">${order.Recurenta ? 'Da' : 'Nu'}</span></div>
                </div>
            </div>
            <div class="detail-section">
                <h4>Locația Serviciului</h4>
                <p><strong>Nume:</strong> ${order.sediu_name || 'N/A'}</p>
                <p><strong>Adresa:</strong> ${order.sediu_address || 'N/A'}</p>
            </div>
        </div>
    `;
}

// Close order modal
function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Rate order
function rateOrder(orderId) {
    document.getElementById('rate_order_id').value = orderId;
    document.getElementById('rateModal').style.display = 'flex';
    
    // Reset star rating
    document.querySelectorAll('.star').forEach(star => star.classList.remove('active'));
    document.getElementById('rating_value').value = '';
    document.getElementById('review').value = '';
}

// Close rate modal
function closeRateModal() {
    document.getElementById('rateModal').style.display = 'none';
}

// Star rating functionality
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('rating_value').value = rating;
        
        document.querySelectorAll('.star').forEach(s => s.classList.remove('active'));
        for (let i = 1; i <= rating; i++) {
            document.querySelector(`[data-rating="${i}"]`).classList.add('active');
        }
    });
});

// Submit rating
function submitRating(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '?controller=client&action=rateOrder', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Evaluarea a fost trimisă cu succes!');
                        closeRateModal();
                        location.reload();
                    } else {
                        alert(response.error || 'Eroare la trimiterea evaluării.');
                    }
                } catch (e) {
                    alert('Eroare la procesarea răspunsului.');
                }
            } else {
                alert('Eroare la trimiterea evaluării.');
            }
        }
    };
    
    xhr.send(formData);
}

// Cancel order
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
    
    const params = `order_id=${orderId}&csrf_token=${document.querySelector('input[name="csrf_token"]').value}`;
    xhr.send(params);
}

// Get order history
function getOrderHistory(orderId) {
    const modal = document.getElementById('historyModal');
    const modalBody = document.getElementById('historyModalBody');
    const modalTitle = document.getElementById('historyModalTitle');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se încarcă istoricul...</div>';
    modalTitle.textContent = `Istoric Comandă #${orderId}`;
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?controller=client&action=getOrderHistory&id=${orderId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        displayOrderHistory(response.history);
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

// Display order history
function displayOrderHistory(history) {
    const modalBody = document.getElementById('historyModalBody');
    
    if (!history || history.length === 0) {
        modalBody.innerHTML = '<p class="no-data">Nu există istoric disponibil pentru această comandă.</p>';
        return;
    }
    
    let historyHtml = '<div class="history-timeline">';
    history.forEach(item => {
        historyHtml += `
            <div class="history-item">
                <div class="history-date">${item.action_date}</div>
                <div class="history-content">
                    <strong>${item.action}</strong>
                    <p>${item.details}</p>
                </div>
            </div>
        `;
    });
    historyHtml += '</div>';
    
    modalBody.innerHTML = historyHtml;
}

// Close history modal
function closeHistoryModal() {
    document.getElementById('historyModal').style.display = 'none';
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
    const modals = ['orderModal', 'rateModal', 'historyModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeOrderModal();
        closeRateModal();
        closeHistoryModal();
    }
});
</script>