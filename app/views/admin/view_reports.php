<div class="container">
    <div class="page-header">
        <h1>Rapoarte și Statistici</h1>
        <div class="breadcrumb">
            <a href="?controller=admin&action=dashboard">Admin</a> > <span class="active">Rapoarte</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Report Type Selector -->
    <div class="section">
        <div class="report-tabs">
            <a href="?controller=admin&action=viewReports&type=overview" 
               class="tab-btn <?php echo ($report_type ?? 'overview') === 'overview' ? 'active' : ''; ?>">
                📊 Prezentare Generală
            </a>
            <a href="?controller=admin&action=viewReports&type=orders" 
               class="tab-btn <?php echo ($report_type ?? '') === 'orders' ? 'active' : ''; ?>">
                📋 Raport Comenzi
            </a>
            <a href="?controller=admin&action=viewReports&type=locations" 
               class="tab-btn <?php echo ($report_type ?? '') === 'locations' ? 'active' : ''; ?>">
                🏢 Raport Locații
            </a>
            <a href="?controller=admin&action=viewReports&type=resources" 
               class="tab-btn <?php echo ($report_type ?? '') === 'resources' ? 'active' : ''; ?>">
                📦 Raport Resurse
            </a>
        </div>
    </div>

    <?php if (($report_type ?? 'overview') === 'overview'): ?>
        <!-- Overview Report -->
        <div class="section">
            <div class="section-header">
                <h2>Prezentare Generală</h2>
                <div class="export-buttons">
                    <a href="?controller=admin&action=exportData&type=overview&format=csv&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export CSV</a>
                    <a href="?controller=admin&action=exportData&type=overview&format=json&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export JSON</a>
                </div>
            </div>
            
            <?php if (isset($overview_report)): ?>
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-content">
                            <h3><?php echo $overview_report['total_locations'] ?? 0; ?></h3>
                            <p>Total Locații</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <h3><?php echo $overview_report['total_orders'] ?? 0; ?></h3>
                            <p>Total Comenzi</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <h3><?php echo $overview_report['total_clients'] ?? 0; ?></h3>
                            <p>Total Clienți</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🔄</div>
                        <div class="stat-content">
                            <h3><?php echo $overview_report['active_orders'] ?? 0; ?></h3>
                            <p>Comenzi Active</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-content">
                            <h3><?php echo $overview_report['monthly_orders'] ?? 0; ?></h3>
                            <p>Comenzi Luna Aceasta</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">⚡</div>
                        <div class="stat-content">
                            <h3><?php echo number_format($overview_report['efficiency_rate'] ?? 0, 1); ?>%</h3>
                            <p>Rata Eficienței</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (($report_type ?? '') === 'orders'): ?>
        <!-- Orders Report -->
        <div class="section">
            <div class="section-header">
                <h2>Raport Comenzi</h2>
                <div class="export-buttons">
                    <a href="?controller=admin&action=exportData&type=orders&format=csv&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export CSV</a>
                    <a href="?controller=admin&action=exportData&type=orders&format=json&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export JSON</a>
                </div>
            </div>
            
            <?php if (isset($orders_report)): ?>
                <div class="report-grid">
                    <!-- Orders by Status -->
                    <div class="report-card">
                        <h3>Comenzi după Status</h3>
                        <div class="chart-container">
                            <?php if (!empty($orders_report['orders_by_status'])): ?>
                                <?php foreach ($orders_report['orders_by_status'] as $status): ?>
                                    <div class="chart-item">
                                        <span class="chart-label"><?php echo getStatusBadge($status['Status']); ?></span>
                                        <span class="chart-value"><?php echo $status['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Nu există date disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Orders by Service -->
                    <div class="report-card">
                        <h3>Comenzi după Serviciu</h3>
                        <div class="chart-container">
                            <?php if (!empty($orders_report['orders_by_service'])): ?>
                                <?php foreach ($orders_report['orders_by_service'] as $service): ?>
                                    <div class="chart-item">
                                        <span class="chart-label"><?php echo getServiceTypeName($service['TipServiciu']); ?></span>
                                        <span class="chart-value"><?php echo $service['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Nu există date disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Daily Orders -->
                    <div class="report-card">
                        <h3>Comenzi Zilnice (Ultima Lună)</h3>
                        <div class="chart-container">
                            <?php if (!empty($orders_report['daily_orders'])): ?>
                                <?php foreach (array_slice($orders_report['daily_orders'], -7) as $daily): ?>
                                    <div class="chart-item">
                                        <span class="chart-label"><?php echo formatDate($daily['order_date']); ?></span>
                                        <span class="chart-value"><?php echo $daily['order_count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Nu există date disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (($report_type ?? '') === 'locations'): ?>
        <!-- Locations Report -->
        <div class="section">
            <div class="section-header">
                <h2>Raport Locații</h2>
                <div class="export-buttons">
                    <a href="?controller=admin&action=exportData&type=locations&format=csv&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export CSV</a>
                    <a href="?controller=admin&action=exportData&type=locations&format=json&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export JSON</a>
                </div>
            </div>
            
            <?php if (isset($locations_report)): ?>
                <div class="report-grid">
                    <!-- Locations by Status -->
                    <div class="report-card">
                        <h3>Locații după Status</h3>
                        <div class="chart-container">
                            <?php if (!empty($locations_report['locations_by_status'])): ?>
                                <?php foreach ($locations_report['locations_by_status'] as $status): ?>
                                    <div class="chart-item">
                                        <span class="chart-label">
                                            <span class="status-badge <?php echo $status['Stare']; ?>">
                                                <?php echo ucfirst($status['Stare']); ?>
                                            </span>
                                        </span>
                                        <span class="chart-value"><?php echo $status['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Nu există date disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Location Efficiency -->
                    <div class="report-card">
                        <h3>Eficiența Locațiilor</h3>
                        <div class="table-container">
                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>Locația</th>
                                        <th>Comenzi</th>
                                        <th>Eficiență</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($locations_report['location_efficiency'])): ?>
                                        <?php foreach (array_slice($locations_report['location_efficiency'], 0, 5) as $location): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($location['location_name']); ?></td>
                                                <td><?php echo $location['total_orders']; ?></td>
                                                <td><?php echo number_format($location['efficiency_rate'] ?? 0, 1); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="no-data">Nu există date</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (($report_type ?? '') === 'resources'): ?>
        <!-- Resources Report -->
        <div class="section">
            <div class="section-header">
                <h2>Raport Resurse</h2>
                <div class="export-buttons">
                    <a href="?controller=admin&action=exportData&type=resources&format=csv&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export CSV</a>
                    <a href="?controller=admin&action=exportData&type=resources&format=json&csrf_token=<?php echo $csrf_token; ?>" 
                       class="btn btn-export">📄 Export JSON</a>
                </div>
            </div>
            
            <?php if (isset($resources_report)): ?>
                <div class="report-grid">
                    <!-- Resources by Type -->
                    <div class="report-card">
                        <h3>Resurse după Tip</h3>
                        <div class="chart-container">
                            <?php if (!empty($resources_report['resources_by_type'])): ?>
                                <?php foreach ($resources_report['resources_by_type'] as $type): ?>
                                    <div class="chart-item">
                                        <span class="chart-label"><?php echo getResourceTypeName($type['Tip']); ?></span>
                                        <span class="chart-value"><?php echo $type['count']; ?> (<?php echo $type['total_quantity']; ?> total)</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Nu există date disponibile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Low Stock Alerts -->
                    <div class="report-card alert-card">
                        <h3>🚨 Alerte Stoc Redus</h3>
                        <div class="table-container">
                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>Resursa</th>
                                        <th>Tip</th>
                                        <th>Stoc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($resources_report['low_stock_alerts'])): ?>
                                        <?php foreach ($resources_report['low_stock_alerts'] as $resource): ?>
                                            <tr class="alert-row">
                                                <td><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                                <td><?php echo getResourceTypeName($resource['resource_type']); ?></td>
                                                <td class="stock-low"><?php echo $resource['available_quantity']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="no-data">Nu există alerte de stoc</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Resource Consumption -->
                    <div class="report-card">
                        <h3>Consumul Resurselor</h3>
                        <div class="table-container">
                            <table class="mini-table">
                                <thead>
                                    <tr>
                                        <th>Resursa</th>
                                        <th>Disponibil</th>
                                        <th>Consumat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($resources_report['resource_consumption'])): ?>
                                        <?php foreach (array_slice($resources_report['resource_consumption'], 0, 5) as $resource): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                                <td><?php echo $resource['available_quantity']; ?></td>
                                                <td><?php echo $resource['consumed_quantity']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="no-data">Nu există date</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="section">
        <h2>Acțiuni Rapide</h2>
        <div class="quick-actions">
            <button onclick="refreshReports()" class="action-btn">
                🔄 Actualizează Rapoartele
            </button>
            <a href="?controller=admin&action=dashboard" class="action-btn">
                📊 Înapoi la Dashboard
            </a>
            <button onclick="printReport()" class="action-btn">
                🖨️ Printează Raportul
            </button>
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

.report-tabs {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 1rem 2rem;
    background: #f8f9fa;
    color: #666;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #333;
}

.tab-btn.active {
    background: #3498db;
    color: white;
    border-color: #2980b9;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.export-buttons {
    display: flex;
    gap: 1rem;
}

.btn-export {
    padding: 0.5rem 1rem;
    background: #27ae60;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background 0.3s ease;
}

.btn-export:hover {
    background: #229954;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
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

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.report-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.report-card h3 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
    font-size: 1.2rem;
}

.alert-card {
    border-left: 4px solid #e74c3c;
    background: #fff5f5;
}

.chart-container {
    space-y: 1rem;
}

.chart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
}

.chart-label {
    font-weight: 500;
    color: #2c3e50;
}

.chart-value {
    font-weight: 700;
    color: #3498db;
    background: #e3f2fd;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.9rem;
}

.table-container {
    overflow-x: auto;
}

.mini-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.mini-table th,
.mini-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.mini-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.mini-table tr:hover {
    background: #f8f9fa;
}

.alert-row {
    background: #fff5f5 !important;
}

.alert-row:hover {
    background: #ffeaea !important;
}

.stock-low {
    color: #e74c3c;
    font-weight: 700;
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

.no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
    font-style: italic;
}

.quick-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 1rem 2rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.action-btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .report-tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .export-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .stats-overview,
    .report-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
}

@media print {
    .export-buttons,
    .quick-actions,
    .report-tabs {
        display: none;
    }
    
    .section {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script>
function refreshReports() {
    location.reload();
}

function printReport() {
    window.print();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    const autoRefresh = confirm('Dorești să actualizezi rapoartele automat?');
    if (autoRefresh) {
        location.reload();
    }
}, 300000);
</script>