<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bun venit la FII CLEAN</h1>
            <p class="hero-subtitle">Clean EVERYTHING up (well not really)</p>
            <p class="hero-description">
                Monitorizati in timp real activitatile de spalare pentru covoare, autoturisme si textile. 
                Gestionati resursele, planificati serviciile si urmariti eficienta fiecarui sediu.
            </p>
        </div>
    </section>
    
    <!-- Services Overview -->
    <section class="services-overview">
        <h2>Serviciile Noastre</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">🏠</div>
                <h3>Spalarea Covoarelor</h3>
                <p>Servicii profesionale de curatare a covoarelor cu echipamente specializate</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">🚗</div>
                <h3>Spalarea Autoturismelor</h3>
                <p>Curatenie completa pentru vehicule, interior si exterior</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">👕</div>
                <h3>Curatenie Textile</h3>
                <p>Spalare si curatare profesionala pentru toate tipurile de textile</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">🚚</div>
                <h3>Transport Domiciliu</h3>
                <p>Servicii de ridicare si livrare la domiciliul clientului</p>
            </div>
        </div>
    </section>
    
    <!-- Real-time Monitoring -->
    <section class="monitoring-section">
        <h2>Monitorizare in Timp Real</h2>
        <div class="monitoring-dashboard">
            <div class="dashboard-card">
                <h3>Sedii Active</h3>
                <div class="stat-number" id="active-sedii">
                    <?php echo isset($sedii) ? count(array_filter($sedii, function($s) { return $s['Stare'] === 'activ'; })) : '0'; ?>
                </div>
                <p>Sedii operationale</p>
            </div>
            
            <div class="dashboard-card">
                <h3>Comenzi Astazi</h3>
                <div class="stat-number" id="today-orders">--</div>
                <p>Total comenzi procesate</p>
            </div>
            
            <div class="dashboard-card">
                <h3>Eficienta Medie</h3>
                <div class="stat-number" id="avg-efficiency">--</div>
                <p>Rata de finalizare</p>
            </div>
        </div>
    </section>
    
    <!-- Sedii Display -->
    <?php if (isset($sedii) && !empty($sedii)): ?>
    <section class="locations-section">
        <h2>Sediile Noastre</h2>
        <div class="locations-grid" id="sedii-grid">
            <?php foreach ($sedii as $sediu): ?>
            <div class="location-card" data-sediu-id="<?php echo $sediu['idSediu']; ?>">
                <div class="location-header">
                    <h3><?php echo htmlspecialchars($sediu['Nume']); ?></h3>
                    <span class="location-status <?php echo $sediu['Stare'] === 'activ' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ucfirst($sediu['Stare']); ?>
                    </span>
                </div>
                
                <div class="location-info">
                    <p><strong>Adresa:</strong> <?php echo htmlspecialchars($sediu['Adresa'] ?? 'N/A'); ?></p>
                    <p><strong>Comenzi Total:</strong> <?php echo $sediu['total_comenzi'] ?? 0; ?></p>
                    <?php if (!empty($sediu['Latitudine']) && !empty($sediu['Longitudine'])): ?>
                    <p><strong>Coordonate:</strong> <?php echo number_format($sediu['Latitudine'], 4); ?>, <?php echo number_format($sediu['Longitudine'], 4); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="location-actions">
                    <button onclick="viewSediuDetails(<?php echo $sediu['idSediu']; ?>)" class="btn btn-primary">
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
        <p>Urmariti in timp real starea sediilor prin fluxurile RSS disponibile:</p>
        <div class="rss-links">
            <a href="<?php echo BASE_URL; ?>?rss=sedii" class="rss-link">
                📡 RSS Stare Sedii
            </a>
            <a href="<?php echo BASE_URL; ?>?rss=statistici" class="rss-link">
                📊 RSS Statistici
            </a>
        </div>
    </section>
</div>

<!-- Sediu Details Modal -->
<div id="sediuModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Detalii Sediu</h3>
            <button class="modal-close" onclick="closeSediuModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading">Se incarca...</div>
        </div>
    </div>
</div>

<script>
// Update statistics dinamice
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
    setInterval(updateStats, 30000); // Update la 30 secunde
});

function updateStats() {
    // Simuleaza date live pentru demo
    const todayElement = document.getElementById('today-orders');
    const efficiencyElement = document.getElementById('avg-efficiency');
    
    if (todayElement) {
        todayElement.textContent = Math.floor(Math.random() * 50) + 10;
    }
    
    if (efficiencyElement) {
        efficiencyElement.textContent = (Math.random() * 20 + 80).toFixed(1) + '%';
    }
}

// View sediu details via AJAX
function viewSediuDetails(sediuId) {
    const modal = document.getElementById('sediuModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se incarca detaliile sediului...</div>';
    modalTitle.textContent = 'Detalii Sediu';
    
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?controller=public&action=getSediuDetails&id=${sediuId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    displaySediuDetails(response);
                } catch (e) {
                    modalBody.innerHTML = '<div class="alert alert-error">Eroare la parsarea raspunsului.</div>';
                }
            } else {
                modalBody.innerHTML = '<div class="alert alert-error">Eroare la incarcarea datelor.</div>';
            }
        }
    };
    
    xhr.send();
}

function displaySediuDetails(data) {
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    if (data.error) {
        modalBody.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
        return;
    }
    
    const sediu = data.sediu;
    const stats = data.stats || {};
    
    modalTitle.textContent = `Detalii: ${sediu.Nume}`;
    
    modalBody.innerHTML = `
        <div class="sediu-details">
            <div class="detail-section">
                <h4>Informatii Generale</h4>
                <p><strong>Nume:</strong> ${sediu.Nume}</p>
                <p><strong>Adresa:</strong> ${sediu.Adresa || 'N/A'}</p>
                <p><strong>Status:</strong> ${sediu.Stare === 'activ' ? 'Operational' : 'Inactiv'}</p>
            </div>
            
            <div class="detail-section">
                <h4>Statistici</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Comenzi astazi:</span>
                        <span class="stat-value">${stats.comenzi_astazi || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comenzi luna:</span>
                        <span class="stat-value">${stats.comenzi_luna || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Rata finalizare:</span>
                        <span class="stat-value">${(stats.rata_finalizare || 0).toFixed(1)}%</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Servicii Disponibile</h4>
                <div class="services-list">
                    <span class="service-tag">Spalare covoare</span>
                    <span class="service-tag">Spalare auto</span>
                    <span class="service-tag">Curatenie textile</span>
                </div>
            </div>
        </div>
    `;
}

function closeSediuModal() {
    document.getElementById('sediuModal').style.display = 'none';
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('sediuModal');
    if (e.target === modal) {
        closeSediuModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSediuModal();
    }
});
</script>
