<div class="container">
    <div class="page-header">
        <h1>Comandă Nouă</h1>
        <div class="breadcrumb">
            <a href="?controller=client&action=dashboard">Dashboard</a> > <span class="active">Comandă Nouă</span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Order Form -->
    <div class="section">
        <div class="form-header">
            <h2>Detalii Comandă</h2>
            <p class="form-subtitle">Completați formularul pentru a plasa o comandă nouă</p>
        </div>
        
        <form id="orderForm" method="POST" action="" class="order-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <!-- Service Selection -->
            <div class="form-section">
                <h3>🔧 Selectați Serviciul</h3>
                <div class="service-grid">
                    <div class="service-option">
                        <input type="radio" id="service_covor" name="service_type" value="covor" required>
                        <label for="service_covor" class="service-card">
                            <div class="service-icon">🏠</div>
                            <h4>Spălare Covoare</h4>
                            <p>Curățare profesională covoare, mochete și preșuri</p>
                            <div class="service-features">
                                <span class="feature">✓ Curățare profundă</span>
                                <span class="feature">✓ Tratament antimucegai</span>
                                <span class="feature">✓ Uscare rapidă</span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="service-option">
                        <input type="radio" id="service_auto" name="service_type" value="auto" required>
                        <label for="service_auto" class="service-card">
                            <div class="service-icon">🚗</div>
                            <h4>Spălare Auto</h4>
                            <p>Curățare completă vehicule, interior și exterior</p>
                            <div class="service-features">
                                <span class="feature">✓ Spălare exterioară</span>
                                <span class="feature">✓ Aspirare interior</span>
                                <span class="feature">✓ Ceară protectoare</span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="service-option">
                        <input type="radio" id="service_textil" name="service_type" value="textil" required>
                        <label for="service_textil" class="service-card">
                            <div class="service-icon">👕</div>
                            <h4>Curățenie Textile</h4>
                            <p>Spălare și curățare profesională îmbrăcăminte</p>
                            <div class="service-features">
                                <span class="feature">✓ Spălare delicată</span>
                                <span class="feature">✓ Călcat professional</span>
                                <span class="feature">✓ Tratament pete</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Location Selection -->
            <div class="form-section">
                <h3>📍 Selectați Locația</h3>
                <div class="location-selection">
                    <?php if (isset($locations) && !empty($locations)): ?>
                        <div class="locations-grid">
                            <?php foreach ($locations as $location): ?>
                                <div class="location-option">
                                    <input type="radio" id="location_<?php echo $location['id']; ?>" 
                                           name="location_id" value="<?php echo $location['id']; ?>" required>
                                    <label for="location_<?php echo $location['id']; ?>" class="location-card">
                                        <div class="location-header">
                                            <h4><?php echo htmlspecialchars($location['Nume']); ?></h4>
                                            <span class="location-status available">Disponibil</span>
                                        </div>
                                        <div class="location-details">
                                            <p><strong>📍 Adresa:</strong> <?php echo htmlspecialchars($location['Adresa'] ?? 'N/A'); ?></p>
                                            <?php if (!empty($location['Latitudine']) && !empty($location['Longitudine'])): ?>
                                            <p><strong>🗺️ Coordonate:</strong> 
                                               <?php echo number_format($location['Latitudine'], 4); ?>, 
                                               <?php echo number_format($location['Longitudine'], 4); ?>
                                            </p>
                                            <?php endif; ?>
                                            <p><strong>🕒 Program:</strong> Luni-Vineri: 08:00-18:00</p>
                                        </div>
                                        <button type="button" class="btn-location-info" 
                                                onclick="showLocationInfo(<?php echo $location['id']; ?>)">
                                            ℹ️ Mai multe detalii
                                        </button>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-locations">
                            <p>Nu există locații disponibile în acest moment.</p>
                            <a href="?controller=public&action=contact" class="btn btn-primary">Contactați-ne</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Schedule and Options -->
            <div class="form-section">
                <h3>📅 Programare și Opțiuni</h3>
                <div class="schedule-grid">
                    <div class="form-group">
                        <label for="scheduled_date">Data Programării *</label>
                        <input type="date" id="scheduled_date" name="scheduled_date" required
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        <small class="help-text">Selectați o dată de cel puțin mâine</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="preferred_time">Ora Preferată</label>
                        <select id="preferred_time" name="preferred_time">
                            <option value="">Oricare dintre orele disponibile</option>
                            <option value="08:00">08:00 - 10:00</option>
                            <option value="10:00">10:00 - 12:00</option>
                            <option value="12:00">12:00 - 14:00</option>
                            <option value="14:00">14:00 - 16:00</option>
                            <option value="16:00">16:00 - 18:00</option>
                        </select>
                    </div>
                </div>
                
                <div class="options-section">
                    <div class="option-card">
                        <div class="option-header">
                            <input type="checkbox" id="transport" name="transport" value="1">
                            <label for="transport" class="option-label">
                                <div class="option-icon">🚚</div>
                                <div class="option-content">
                                    <h4>Transport la Domiciliu</h4>
                                    <p>Ridicare și livrare gratuită la adresa dumneavoastră</p>
                                </div>
                            </label>
                        </div>
                        <div class="option-price">Gratuit</div>
                    </div>
                    
                    <div class="option-card">
                        <div class="option-header">
                            <input type="checkbox" id="recurring" name="recurring" value="1">
                            <label for="recurring" class="option-label">
                                <div class="option-icon">🔄</div>
                                <div class="option-content">
                                    <h4>Comandă Recurentă</h4>
                                    <p>Programează serviciul să se repete lunar</p>
                                </div>
                            </label>
                        </div>
                        <div class="option-price">-10%</div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="form-section">
                <h3>📝 Detalii Suplimentare</h3>
                <div class="additional-details">
                    <div class="form-group">
                        <label for="notes">Observații (opțional)</label>
                        <textarea id="notes" name="notes" rows="4" 
                                  placeholder="Orice detalii speciale despre comandă..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone">Telefon de Contact</label>
                        <input type="tel" id="contact_phone" name="contact_phone" 
                               placeholder="+40 xxx xxx xxx"
                               pattern="(\+40|0)[0-9]{9}">
                        <small class="help-text">Pentru confirmare și coordonare</small>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="form-section">
                <h3>📋 Rezumat Comandă</h3>
                <div class="order-summary" id="orderSummary">
                    <div class="summary-item">
                        <span class="label">Serviciu:</span>
                        <span class="value" id="summary-service">Nu a fost selectat</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Locația:</span>
                        <span class="value" id="summary-location">Nu a fost selectată</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Data:</span>
                        <span class="value" id="summary-date">Nu a fost selectată</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Opțiuni:</span>
                        <span class="value" id="summary-options">Fără opțiuni</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    🛒 Plasează Comanda
                </button>
                <a href="?controller=client&action=dashboard" class="btn btn-secondary">
                    ❌ Anulează
                </a>
            </div>
        </form>
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

.form-header {
    text-align: center;
    margin-bottom: 3rem;
}

.form-header h2 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-subtitle {
    color: #666;
    font-size: 1.1rem;
}

.form-section {
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #f8f9fa;
}

.form-section:last-child {
    border-bottom: none;
}

.form-section h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.service-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.service-option input[type="radio"] {
    display: none;
}

.service-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    text-align: center;
}

.service-card:hover {
    border-color: #3498db;
    box-shadow: 0 4px 15px rgba(52,152,219,0.2);
}

.service-option input[type="radio"]:checked + .service-card {
    border-color: #3498db;
    background: #e3f2fd;
    box-shadow: 0 4px 15px rgba(52,152,219,0.3);
}

.service-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.service-card h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.service-card p {
    color: #666;
    margin-bottom: 1rem;
}

.service-features {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.feature {
    color: #27ae60;
    font-size: 0.9rem;
    font-weight: 500;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.location-option input[type="radio"] {
    display: none;
}

.location-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    position: relative;
}

.location-card:hover {
    border-color: #3498db;
    box-shadow: 0 4px 15px rgba(52,152,219,0.2);
}

.location-option input[type="radio"]:checked + .location-card {
    border-color: #3498db;
    background: #e3f2fd;
    box-shadow: 0 4px 15px rgba(52,152,219,0.3);
}

.location-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.location-header h4 {
    color: #2c3e50;
    margin: 0;
}

.location-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.location-status.available {
    background: #d4edda;
    color: #155724;
}

.location-details p {
    margin-bottom: 0.5rem;
    color: #555;
    font-size: 0.9rem;
}

.btn-location-info {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 0.5rem;
    cursor: pointer;
    font-size: 0.8rem;
    transition: background 0.3s ease;
}

.btn-location-info:hover {
    background: #2980b9;
}

.no-locations {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

.help-text {
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.85rem;
}

.options-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.option-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.option-card:has(input:checked) {
    border-color: #3498db;
    background: #e3f2fd;
    box-shadow: 0 4px 15px rgba(52,152,219,0.3);
}

.option-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.option-header input[type="checkbox"] {
    margin-right: 1rem;
    transform: scale(1.2);
}

.option-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    flex: 1;
}

.option-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.option-content h4 {
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
}

.option-content p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.option-price {
    color: #27ae60;
    font-weight: 700;
    font-size: 1.1rem;
}

.additional-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.order-summary {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.summary-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.summary-item .label {
    font-weight: 600;
    color: #2c3e50;
}

.summary-item .value {
    color: #3498db;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 3rem;
}

.btn {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-large {
    padding: 1rem 3rem;
    font-size: 1.1rem;
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
    .service-grid,
    .locations-grid,
    .options-section,
    .additional-details {
        grid-template-columns: 1fr;
    }
    
    .schedule-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .location-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .btn-location-info {
        position: static;
        margin-top: 1rem;
        width: 100%;
    }
}
</style>

<script>
// Update order summary when form changes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    
    // Service selection
    const serviceRadios = form.querySelectorAll('input[name="service_type"]');
    serviceRadios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
    
    // Location selection
    const locationRadios = form.querySelectorAll('input[name="location_id"]');
    locationRadios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
    
    // Date selection
    const dateInput = form.querySelector('#scheduled_date');
    dateInput.addEventListener('change', updateSummary);
    
    // Options
    const optionCheckboxes = form.querySelectorAll('input[type="checkbox"]');
    optionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSummary);
    });
});

function updateSummary() {
    const form = document.getElementById('orderForm');
    
    // Service
    const selectedService = form.querySelector('input[name="service_type"]:checked');
    const serviceText = selectedService ? getServiceName(selectedService.value) : 'Nu a fost selectat';
    document.getElementById('summary-service').textContent = serviceText;
    
    // Location
    const selectedLocation = form.querySelector('input[name="location_id"]:checked');
    const locationText = selectedLocation ? selectedLocation.parentElement.querySelector('h4').textContent : 'Nu a fost selectată';
    document.getElementById('summary-location').textContent = locationText;
    
    // Date
    const dateValue = form.querySelector('#scheduled_date').value;
    const dateText = dateValue ? formatDate(dateValue) : 'Nu a fost selectată';
    document.getElementById('summary-date').textContent = dateText;
    
    // Options
    const transport = form.querySelector('#transport').checked;
    const recurring = form.querySelector('#recurring').checked;
    
    let optionsText = 'Fără opțiuni';
    if (transport || recurring) {
        const options = [];
        if (transport) options.push('Transport');
        if (recurring) options.push('Recurentă');
        optionsText = options.join(', ');
    }
    document.getElementById('summary-options').textContent = optionsText;
}

function getServiceName(value) {
    const names = {
        'covor': 'Spălare Covoare',
        'auto': 'Spălare Auto',
        'textil': 'Curățenie Textile'
    };
    return names[value] || value;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ro-RO');
}

// Show location details
function showLocationInfo(locationId) {
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
                <p><strong>Nume:</strong> ${location.Nume}</p>
                <p><strong>Adresa:</strong> ${location.Adresa || 'N/A'}</p>
                <p><strong>Status:</strong> ${location.Stare === 'activ' ? 'Disponibil' : 'Indisponibil'}</p>
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
                <h4>Program Lucru</h4>
                <p><strong>Luni - Vineri:</strong> 08:00 - 18:00<br>
                <strong>Sâmbătă:</strong> 09:00 - 15:00<br>
                <strong>Duminică:</strong> Închis</p>
            </div>
        </div>
    `;
}

function closeLocationModal() {
    document.getElementById('locationModal').style.display = 'none';
}

// Form validation before submit
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const service = this.querySelector('input[name="service_type"]:checked');
    const location = this.querySelector('input[name="location_id"]:checked');
    const date = this.querySelector('#scheduled_date').value;
    
    if (!service) {
        e.preventDefault();
        alert('Vă rugăm să selectați un serviciu.');
        return;
    }
    
    if (!location) {
        e.preventDefault();
        alert('Vă rugăm să selectați o locație.');
        return;
    }
    
    if (!date) {
        e.preventDefault();
        alert('Vă rugăm să selectați o dată pentru programare.');
        return;
    }
    
    // Check if date is in the future
    const selectedDate = new Date(date);
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (selectedDate < tomorrow) {
        e.preventDefault();
        alert('Data programării trebuie să fie de cel puțin mâine.');
        return;
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('locationModal');
    if (e.target === modal) {
        closeLocationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLocationModal();
    }
});
</script>