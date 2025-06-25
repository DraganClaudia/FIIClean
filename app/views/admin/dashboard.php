<div class="container">
    <div class="admin-header">
        <h1>Dashboard Admin</h1>
        <div class="breadcrumb">
            <span>Admin</span> > <span class="active">Dashboard</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_locations'] ?? 0; ?></h3>
                <p>Total Locații</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_users'] ?? 0; ?></h3>
                <p>Utilizatori</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3><?php echo $stats['active_orders'] ?? 0; ?></h3>
                <p>Comenzi Active</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3><?php echo $stats['today_orders'] ?? 0; ?></h3>
                <p>Comenzi Astăzi</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="section">
        <div class="section-header">
            <h2>Comenzi Recente</h2>
            <a href="?controller=admin&action=viewReports&type=orders" class="btn btn-primary">Vezi Toate</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Serviciu</th>
                        <th>Locație</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recent_orders) && !empty($recent_orders)): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['client_name'] ?? 'N/A'); ?></td>
                                <td><?php echo getServiceTypeName($order['TipServiciu']); ?></td>
                                <td><?php echo htmlspecialchars($order['sediu_name'] ?? 'N/A'); ?></td>
                                <td><?php echo getStatusBadge($order['Status']); ?></td>
                                <td><?php echo formatDate($order['DataProgramare']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">Nu există comenzi recente</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Location Statistics -->
    <div class="section">
        <div class="section-header">
            <h2>Statistici Locații</h2>
            <a href="?controller=admin&action=manageLocations" class="btn btn-primary">Gestionează Locații</a>
        </div>
        
        <div class="locations-grid">
            <?php if (isset($location_stats) && !empty($location_stats)): ?>
                <?php foreach ($location_stats as $location): ?>
                    <div class="location-card">
                        <div class="location-header">
                            <h4><?php echo htmlspecialchars($location['Nume']); ?></h4>
                            <span class="status-badge <?php echo $location['Stare']; ?>">
                                <?php echo ucfirst($location['Stare']); ?>
                            </span>
                        </div>
                        <div class="location-stats">
                            <div class="stat-item">
                                <span class="label">Total comenzi:</span>
                                <span class="value"><?php echo $location['total_orders'] ?? 0; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="label">Noi:</span>
                                <span class="value"><?php echo $location['new_orders'] ?? 0; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="label">În curs:</span>
                                <span class="value"><?php echo $location['in_progress_orders'] ?? 0; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="label">Finalizate:</span>
                                <span class="value"><?php echo $location['completed_orders'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">Nu există locații disponibile</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section">
        <h2>Acțiuni Rapide</h2>
        <div class="quick-actions">
            <a href="?controller=admin&action=manageLocations" class="action-card">
                <div class="action-icon">🏢</div>
                <h4>Gestionează Locații</h4>
                <p>Adaugă, editează sau șterge locații</p>
            </a>
            
            <a href="?controller=admin&action=manageUsers" class="action-card">
                <div class="action-icon">👥</div>
                <h4>Gestionează Utilizatori</h4>
                <p>Administrează conturile utilizatorilor</p>
            </a>
            
            <a href="?controller=admin&action=viewReports" class="action-card">
                <div class="action-icon">📊</div>
                <h4>Vezi Rapoarte</h4>
                <p>Analizează statistici și rapoarte</p>
            </a>
        </div>
    </div>
</div>

<style>
.admin-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.breadcrumb {
    color: #666;
    margin-top: 0.5rem;
}

.breadcrumb .active {
    color: #333;
    font-weight: 600;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
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
}

.stat-card:hover {
    transform: translateY(-2px);
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

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.section-header h2 {
    margin: 0;
    color: #2c3e50;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.location-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.location-header {
    background: #f8f9fa;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.location-header h4 {
    margin: 0;
    color: #2c3e50;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.activ {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactiv {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.reparatii {
    background: #fff3cd;
    color: #856404;
}

.location-stats {
    padding: 1rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.stat-item .label {
    color: #666;
}

.stat-item .value {
    font-weight: 600;
    color: #2c3e50;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: white;
    border: 2px solid #f0f0f0;
    padding: 2rem;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    text-align: center;
}

.action-card:hover {
    border-color: #3498db;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(52,152,219,0.15);
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

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .locations-grid,
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>
