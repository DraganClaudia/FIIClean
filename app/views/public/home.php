<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bun venit la CaS</h1>
            <p class="hero-subtitle">Sistem Web pentru managementul activităților de spălătorie</p>
            <p class="hero-description">
                Monitorizați în timp real activitățile de spălare pentru covoare, autoturisme, îmbrăcăminte și multe altele. 
                Gestionați resursele, planificați serviciile și urmăriți eficiența fiecărui sediu.
            </p>
        </div>
    </section>
    
    <!-- Services Overview -->
    <section class="services-overview">
        <h2>Serviciile Noastre</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">🏠</div>
                <h3>Spălarea Covoarelor</h3>
                <p>Servicii profesionale de curățare a covoarelor cu echipamente specializate</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">🚗</div>
                <h3>Spălarea Autoturismelor</h3>
                <p>Curățenie completă pentru vehicule, interior și exterior</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">👕</div>
                <h3>Curățenie Îmbrăcăminte</h3>
                <p>Spălare și curățare profesională pentru toate tipurile de textile</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">🚚</div>
                <h3>Transport Domiciliu</h3>
                <p>Servicii de ridicare și livrare la domiciliul clientului</p>
            </div>
        </div>
    </section>
    
    <!-- Real-time Monitoring Section -->
    <section class="monitoring-section">
        <h2>Monitorizare în Timp Real</h2>
        <div class="monitoring-dashboard">
            <div class="dashboard-card">
                <h3>Sedii Active</h3>
                <div class="stat-number" id="active-locations">
                    <?php echo isset($locations) ? count($locations) : '0'; ?>
                </div>
                <p>Locații operaționale</p>
            </div>
            
            <div class="dashboard-card">
                <h3>Comenzi Astăzi</h3>
                <div class="stat-number" id="today-orders">--</div>
                <p>Total comenzi procesate</p>
            </div>
            
            <div class="dashboard-card">
                <h3>Eficiență Medie</h3>
                <div class="stat-number" id="avg-efficiency">--</div>
                <p>Rata de finalizare</p>
            </div>
        </div>
    </section>
    
    <!-- Locations Display -->
    <?php if (isset($locations) && !empty($locations)): ?>
    <section class="locations-section">
        <h2>Sediile Noastre</h2>
        <div class="locations-grid" id="locations-grid">
            <?php foreach ($locations as $location): ?>
            <div class="location-card" data-location-id="<?php echo $location['id']; ?>">
                <div class="location-header">
                    <h3><?php echo htmlspecialchars($location['Nume']); ?></h3>
                    <span class="location-status <?php echo $location['Stare'] === 'activ' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ucfirst($location['Stare']); ?>
                    </span>
                </div>
                
                <div class="location-info">
                    <p><strong>Adresa:</strong> <?php echo htmlspecialchars($location['Adresa'] ?? 'N/A'); ?></p>
                    <p><strong>Comenzi Total:</strong> <?php echo $location['total_orders'] ?? 0; ?></p>
                    <p><strong>Eficiență:</strong> <?php echo number_format(($location['efficiency_rate'] ?? 0) * 100, 1); ?>%</p>
                    <?php if (!empty($location['Latitudine']) && !empty($location['Longitudine'])): ?>
                    <p><strong>Coordonate:</strong> <?php echo number_format($location['Latitudine'], 6); ?>, <?php echo number_format($location['Longitudine'], 6); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="location-actions">
                    <button class="btn btn-primary" onclick="viewLocationDetails(<?php echo $location['id']; ?>)">
                        Vezi Detalii
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- RSS Feed Section -->
    <section class="rss-section">
        <h2>Fluxuri RSS</h2>
        <p>Urmăriți în timp real starea sediilor prin fluxurile RSS disponibile:</p>
        <div class="rss-links">
            <a href="<?php echo BASE_URL; ?>rss/locations" class="rss-link">
                📡 RSS Stare Locații
            </a>
            <a href="<?php echo BASE_URL; ?>rss/statistics" class="rss-link">
                📊 RSS Statistici
            </a>
        </div>
    </section>
</div>

<!-- Location Details Modal -->
<div id="locationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Detalii Locație</h3>
            <button class="modal-close" onclick="closeLocationModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">Se încarcă...</div>
        </div>
    </div>
</div>

<!-- CSRF Token for AJAX calls -->
<script>
    window.csrfToken = '<?php echo isset($csrf_token) ? $csrf_token : ''; ?>';
</script>