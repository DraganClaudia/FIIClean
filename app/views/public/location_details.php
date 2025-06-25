<?php
/**
 * Location Details - Standalone page for displaying location information
 * Can be accessed directly or via AJAX
 */

// Start session for authentication and CSRF protection
session_start();

// Include necessary files
require_once '../app/core/Database.php';
require_once '../app/utils/security.php';
require_once '../app/models/LocationModel.php';

// Initialize response array for AJAX
$response = ['success' => false, 'data' => null, 'error' => null];

// Get location ID from URL parameter
$location_id = sanitize_numeric($_GET['id'] ?? null);

if (!$location_id) {
    if (isAjaxRequest()) {
        $response['error'] = 'ID locație necesar';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        header('Location: ../index.php');
        exit;
    }
}

try {
    $locationModel = new LocationModel();
    
    // Get location details
    $location = $locationModel->getLocationById($location_id);
    
    if (!$location) {
        if (isAjaxRequest()) {
            $response['error'] = 'Locația nu a fost găsită';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            header('Location: ../index.php?error=location_not_found');
            exit;
        }
    }
    
    // Get additional data
    $stats = $locationModel->getLocationStats($location_id);
    $operational_status = $locationModel->isLocationOperational($location_id);
    $resources = $locationModel->getLocationResources($location_id);
    $recent_orders = $locationModel->getLocationOrders($location_id, 5);
    
    // Prepare response data
    $location_data = [
        'location' => $location,
        'stats' => $stats,
        'operational_status' => $operational_status['operational_status'] ?? 'unknown',
        'resources' => $resources,
        'recent_orders' => $recent_orders
    ];
    
    if (isAjaxRequest()) {
        $response['success'] = true;
        $response['data'] = $location_data;
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Location details error: " . $e->getMessage());
    
    if (isAjaxRequest()) {
        $response['error'] = 'Eroare la încărcarea detaliilor locației';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        header('Location: ../index.php?error=system_error');
        exit;
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// If we get here, it's a regular page request
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalii Locație: <?php echo htmlspecialchars($location['Nume']); ?> - CaS</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <meta name="description" content="Detalii despre locația <?php echo htmlspecialchars($location['Nume']); ?> - servicii de spălătorie disponibile">
</head>
<body>
    <!-- Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="../index.php">CaS</a></h1>
                    <span class="tagline">Cleaning Web Simulator</span>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.php">Acasă</a></li>
                        <li><a href="../public/contact.php">Contact</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="?controller=client&action=dashboard">Dashboard</a></li>
                            <li><a href="../app/action/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="../app/action/login.php">Login</a></li>
                            <li><a href="../app/action/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Acasă</a> > <span class="active">Detalii Locație</span>
                </div>
                <h1>📍 <?php echo htmlspecialchars($location['Nume']); ?></h1>
            </div>

            <!-- Location Overview -->
            <div class="section">
                <div class="location-overview">
                    <div class="location-main-info">
                        <div class="location-header-full">
                            <div class="location-title">
                                <h2><?php echo htmlspecialchars($location['Nume']); ?></h2>
                                <span class="location-status <?php echo $location['Stare']; ?>">
                                    <?php echo ucfirst($location['Stare']); ?>
                                </span>
                            </div>
                            
                            <div class="location-basic-info">
                                <div class="info-item">
                                    <span class="info-icon">📍</span>
                                    <div class="info-content">
                                        <span class="info-label">Adresa</span>
                                        <span class="info-value"><?php echo htmlspecialchars($location['Adresa'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($location['Latitudine']) && !empty($location['Longitudine'])): ?>
                                <div class="info-item">
                                    <span class="info-icon">🗺️</span>
                                    <div class="info-content">
                                        <span class="info-label">Coordonate</span>
                                        <span class="info-value">
                                            <?php echo number_format($location['Latitudine'], 6); ?>, 
                                            <?php echo number_format($location['Longitudine'], 6); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-icon">📞</span>
                                    <div class="info-content">
                                        <span class="info-label">Contact</span>
                                        <span class="info-value">+40 123 456 789</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="location-actions">
                            <a href="?controller=client&action=newOrder&location=<?php echo $location['id']; ?>" 
                               class="btn btn-primary btn-large">
                                🛒 Comandă la Această Locație
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="section">
                <h2>📊 Statistici Locație</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-content">
                            <h3><?php echo $stats['orders_today'] ?? 0; ?></h3>
                            <p>Comenzi Astăzi</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📆</div>
                        <div class="stat-content">
                            <h3><?php echo $stats['orders_month'] ?? 0; ?></h3>
                            <p>Comenzi Luna Aceasta</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-content">
                            <h3><?php echo $stats['orders_year'] ?? 0; ?></h3>
                            <p>Comenzi Anul Acesta</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">⚡</div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['completion_rate'] ?? 0, 1); ?>%</h3>
                            <p>Rata de Finalizare</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="section">
                <h2>🔧 Servicii Disponibile</h2>
                <div class="services-available">
                    <div class="service-item">
                        <div class="service-icon">🏠</div>
                        <div class="service-content">
                            <h4>Spălare Covoare</h4>
                            <p>Curățare profesională covoare, mochete și preșuri cu echipamente specializate</p>
                            <ul class="service-features">
                                <li>✓ Curățare profundă cu detergenti ecologici</li>
                                <li>✓ Tratament antimucegai și antibacterian</li>
                                <li>✓ Uscare rapidă în 2-4 ore</li>
                                <li>✓ Eliminarea petelor persistente</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">🚗</div>
                        <div class="service-content">
                            <h4>Spălare Auto</h4>
                            <p>Curățare completă vehicule, atât interior cât și exterior</p>
                            <ul class="service-features">
                                <li>✓ Spălare exterioară cu spumă activă</li>
                                <li>✓ Aspirare și curățare interior</li>
                                <li>✓ Aplicare ceară protectoare</li>
                                <li>✓ Curățare jante și anvelope</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">👕</div>
                        <div class="service-content">
                            <h4>Curățenie Textile</h4>
                            <p>Spălare și curățare profesională pentru toate tipurile de îmbrăcăminte</p>
                            <ul class="service-features">
                                <li>✓ Spălare delicată pentru materiale fine</li>
                                <li>✓ Călcat și îndoire profesională</li>
                                <li>✓ Tratament special pentru pete</li>
                                <li>✓ Curățare chimică pentru articole speciale</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resources Section -->
            <?php if (!empty($resources)): ?>
            <div class="section">
                <h2>📦 Resurse Disponibile</h2>
                <div class="resources-grid">
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <span class="resource-icon">
                                    <?php 
                                    echo $resource['Tip'] === 'detergent' ? '🧽' : 
                                        ($resource['Tip'] === 'apa' ? '💧' : '🔧'); 
                                    ?>
                                </span>
                                <h4><?php echo htmlspecialchars($resource['Nume']); ?></h4>
                            </div>
                            <div class="resource-info">
                                <div class="resource-type">
                                    <?php echo ucfirst($resource['Tip']); ?>
                                </div>
                                <div class="resource-quantity">
                                    <span class="quantity-value"><?php echo $resource['CantitateDisponibila']; ?></span>
                                    <span class="quantity-label">disponibile</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Schedule and Contact -->
            <div class="section">
                <h2>🕒 Program și Contact</h2>
                <div class="schedule-contact">
                    <div class="schedule-info">
                        <h3>Program de Lucru</h3>
                        <div class="schedule-table">
                            <div class="schedule-row">
                                <span class="day">Luni - Vineri:</span>
                                <span class="hours">08:00 - 18:00</span>
                            </div>
                            <div class="schedule-row">
                                <span class="day">Sâmbătă:</span>
                                <span class="hours">09:00 - 15:00</span>
                            </div>
                            <div class="schedule-row">
                                <span class="day">Duminică:</span>
                                <span class="hours">Închis</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <h3>Informații de Contact</h3>
                        <div class="contact-details">
                            <div class="contact-item">
                                <span class="contact-icon">📞</span>
                                <div class="contact-content">
                                    <span class="contact-label">Telefon:</span>
                                    <span class="contact-value">+40 123 456 789</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">✉️</span>
                                <div class="contact-content">
                                    <span class="contact-label">Email:</span>
                                    <span class="contact-value">contact@cas-simulator.ro</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">🚨</span>
                                <div class="contact-content">
                                    <span class="contact-label">Urgențe (24/7):</span>
                                    <span class="contact-value">+40 724 456 789</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <?php if (!empty($recent_orders)): ?>
            <div class="section">
                <h2>📋 Comenzi Recente</h2>
                <div class="recent-orders">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-preview">
                            <div class="order-header">
                                <span class="order-id">#<?php echo $order['id']; ?></span>
                                <span class="order-status <?php echo $order['Status']; ?>">
                                    <?php echo ucfirst($order['Status']); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <span class="order-service">
                                    <?php 
                                    $service_icons = ['covor' => '🏠', 'auto' => '🚗', 'textil' => '👕'];
                                    echo $service_icons[$order['TipServiciu']] ?? '🔧';
                                    ?>
                                    <?php echo ucfirst($order['TipServiciu']); ?>
                                </span>
                                <span class="order-date">📅 <?php echo date('d.m.Y', strtotime($order['DataProgramare'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="section">
                <div class="action-section">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="?controller=client&action=newOrder&location=<?php echo $location['id']; ?>" 
                           class="btn btn-primary btn-large">
                            🛒 Comandă Acum
                        </a>
                    <?php else: ?>
                        <a href="../app/action/login.php" class="btn btn-primary btn-large">
                            👤 Conectează-te pentru a Comanda
                        </a>
                    <?php endif; ?>
                    
                    <a href="../public/contact.php" class="btn btn-secondary">
                        📞 Contactează-ne
                    </a>
                    
                    <a href="../index.php" class="btn btn-outline">
                        ← Înapoi la Locații
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CaS - Cleaning Web Simulator</h3>
                    <p>Sistem Web pentru managementul activităților de spălătorie</p>
                </div>
                
                <div class="footer-section">
                    <h4>Servicii</h4>
                    <ul>
                        <li>Spălarea covoarelor</li>
                        <li>Spălarea autoturismelor</li>
                        <li>Curățenia îmbrăcămintei</li>
                        <li>Transport la domiciliu</li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>📞 +40 123 456 789</li>
                        <li>✉️ contact@cas-simulator.ro</li>
                        <li>📍 Strada Unirii Nr. 25, București</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> CaS - Cleaning Web Simulator. Toate drepturile rezervate.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../public/js/util.js"></script>
    <script src="../public/js/main.js"></script>
    
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

        .location-overview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .location-header-full {
            margin-bottom: 2rem;
        }

        .location-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .location-title h2 {
            color: white;
            margin: 0;
            font-size: 2.5rem;
        }

        .location-status {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .location-status.activ {
            background: #27ae60;
            color: white;
        }

        .location-basic-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-icon {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        .info-content {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .location-actions {
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .services-available {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .service-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            border-left: 4px solid #3498db;
        }

        .service-item .service-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .service-features {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
        }

        .service-features li {
            color: #27ae60;
            margin-bottom: 0.5rem;
        }

        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .resource-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }

        .resource-header {
            margin-bottom: 1rem;
        }

        .resource-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .resource-quantity {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .quantity-value {
            font-size: 2rem;
            font-weight: 700;
            color: #3498db;
        }

        .schedule-contact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .schedule-table {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .schedule-row:last-child {
            border-bottom: none;
        }

        .contact-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-icon {
            font-size: 1.2rem;
        }

        .contact-content {
            display: flex;
            flex-direction: column;
        }

        .contact-label {
            font-size: 0.9rem;
            color: #666;
        }

        .contact-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .recent-orders {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .order-preview {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #3498db;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .order-id {
            font-weight: 700;
            color: #2c3e50;
        }

        .order-status {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .order-status.noua {
            background: #fff3cd;
            color: #856404;
        }

        .order-status.finalizata {
            background: #d4edda;
            color: #155724;
        }

        .order-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }

        .action-section {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
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

        @media (max-width: 768px) {
            .location-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .location-basic-info {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .schedule-contact,
            .resources-grid,
            .recent-orders {
                grid-template-columns: 1fr;
            }

            .action-section {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>