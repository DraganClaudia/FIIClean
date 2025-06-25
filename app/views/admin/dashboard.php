<div class="container">
    <div class="admin-header">
        <h1>Dashboard Admin</h1>
        <div class="breadcrumb">
            Admin > <span class="active">Dashboard</span>
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
                <h3><?php echo count($sedii_stats ?? []); ?></h3>
                <p>Total Sedii</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_comenzi'] ?? 0; ?></h3>
                <p>Total Comenzi</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3><?php echo ($stats['comenzi_noi'] ?? 0) + ($stats['comenzi_in_curs'] ?? 0); ?></h3>
                <p>Comenzi Active</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <h3><?php echo $stats['comenzi_astazi'] ?? 0; ?></h3>
                <p>Comenzi Astazi</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="section">
        <div class="section-header">
            <h2>Comenzi Recente</h2>
            <a href="?controller=admin&action=manageComenzi" class="btn btn-primary">Vezi Toate</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Serviciu</th>
                        <th>Sediu</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($comenzi_recente) && !empty($comenzi_recente)): ?>
                        <?php foreach ($comenzi_recente as $comanda): ?>
                            <tr>
                                <td>#<?php echo $comanda['id']; ?></td>
                                <td><?php echo htmlspecialchars($comanda['nume_client'] ?? 'N/A'); ?></td>
                                <td><?php echo getServiceTypeName($comanda['TipServiciu']); ?></td>
                                <td><?php echo htmlspecialchars($comanda['nume_sediu'] ?? 'N/A'); ?></td>
                                <td><?php echo getStatusBadge($comanda['Status']); ?></td>
                                <td><?php echo formatDate($comanda['DataProgramare']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">Nu exista comenzi recente</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sedii Statistics -->
    <div class="section">
        <div class="section-header">
            <h2>Statistici Sedii</h2>
            <a href="?controller=admin&action=manageSedii" class="btn btn-primary">Gestioneaza Sedii</a>
        </div>
        
        <div class="sedii-grid">
            <?php if (isset($sedii_stats) && !empty($sedii_stats)): ?>
                <?php foreach ($sedii_stats as $sediu): ?>
                    <div class="sediu-card">
                        <div class="sediu-header">
                            <h4><?php echo htmlspecialchars($sediu['Nume']); ?></h4>
                            <span class="status-badge <?php echo $sediu['Stare']; ?>">
                                <?php echo ucfirst($sediu['Stare']); ?>
                            </span>
                        </div>
                        <div class="sediu-stats">
                            <div class="stat-item">
                                <span class="label">Total comenzi:</span>
                                <span class="value"><?php echo $sediu['total_comenzi'] ?? 0; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="label">Adresa:</span>
                                <span class="value"><?php echo truncateText($sediu['Adresa'] ?? 'N/A', 30); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">Nu exista sedii disponibile</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section">
        <h2>Actiuni Rapide</h2>
        <div class="quick-actions">
            <a href="?controller=admin&action=manageSedii" class="action-card">
                <div class="action-icon">🏢</div>
                <h4>Gestioneaza Sedii</h4>
                <p>Adauga, editeaza sau sterge sedii</p>
            </a>
            
            <a href="?controller=admin&action=manageComenzi" class="action-card">
                <div class="action-icon">📋</div>
                <h4>Gestioneaza Comenzi</h4>
                <p>Administreaza comenzile clientilor</p>
            </a>
            
            <a href="?controller=admin&action=exportData&type=comenzi&csrf_token=<?php echo $csrf_token; ?>" class="action-card">
                <div class="action-icon">📊</div>
                <h4>Export Date</h4>
                <p>Exporta date in format CSV</p>
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

.sedii-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.sediu-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.sediu-header {
    background: #f8f9fa;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sediu-header h4 {
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

.sediu-stats {
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

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-new { background: #fff3cd; color: #856404; }
.badge-progress { background: #cce5ff; color: #004085; }
.badge-completed { background: #d4edda; color: #155724; }
.badge-cancelled { background: #f8d7da; color: #721c24; }

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .sedii-grid,
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>
