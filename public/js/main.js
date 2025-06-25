/**
 * CaS - Cleaning Web Simulator
 * Main JavaScript file implementing AJAX functionality
 */

// Global variables
let currentLocationModal = null;

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Load dashboard statistics
    loadDashboardStats();
    
    // Initialize contact form if exists
    initializeContactForm();
    
    // Set up periodic updates for real-time monitoring
    setInterval(updateDashboardStats, 30000); // Update every 30 seconds
}

/**
 * Mobile menu functionality
 */
function initializeMobileMenu() {
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.main-nav');
    
    if (toggleButton && nav) {
        toggleButton.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
}

/**
 * Toggle mobile menu
 */
function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    if (nav) {
        nav.classList.toggle('active');
    }
}

/**
 * Load dashboard statistics via AJAX
 */
function loadDashboardStats() {
    // Simulate loading dashboard data
    // In a real implementation, this would make AJAX calls to get live data
    
    const todayOrdersElement = document.getElementById('today-orders');
    const avgEfficiencyElement = document.getElementById('avg-efficiency');
    
    if (todayOrdersElement) {
        // Simulate some data
        todayOrdersElement.textContent = Math.floor(Math.random() * 50) + 10;
    }
    
    if (avgEfficiencyElement) {
        avgEfficiencyElement.textContent = (Math.random() * 20 + 80).toFixed(1) + '%';
    }
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats() {
    loadDashboardStats();
}

/**
 * View location details via AJAX
 */
function viewLocationDetails(locationId) {
    const modal = document.getElementById('locationModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!modal || !modalBody || !modalTitle) {
        console.error('Modal elements not found');
        return;
    }
    
    // Show modal
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se încarcă detaliile locației...</div>';
    modalTitle.textContent = 'Detalii Locație';
    
    // Make AJAX request
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
    
    xhr.onerror = function() {
        modalBody.innerHTML = '<div class="alert alert-error">Eroare de conexiune.</div>';
    };
    
    xhr.send();
    currentLocationModal = locationId;
}

/**
 * Display location details in modal
 */
function displayLocationDetails(data) {
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    if (data.error) {
        modalBody.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
        return;
    }
    
    const location = data.location;
    const stats = data.stats || {};
    const operationalStatus = data.operational_status || 'unknown';
    const resources = data.resources || [];
    const orders = data.recent_orders || [];
    
    modalTitle.textContent = `Detalii: ${location.Nume}`;
    
    const statusClass = operationalStatus === 'operational' ? 'status-active' : 'status-inactive';
    const statusText = operationalStatus === 'operational' ? 'Operațional' : 
                      operationalStatus === 'maintenance' ? 'În reparații' :
                      operationalStatus === 'inactive' ? 'Inactiv' : 'Necunoscut';
    
    modalBody.innerHTML = `
        <div class="location-details">
            <div class="detail-section">
                <h4>Informații Generale</h4>
                <p><strong>Nume:</strong> ${escapeHtml(location.Nume)}</p>
                <p><strong>Adresa:</strong> ${escapeHtml(location.Adresa || 'N/A')}</p>
                <p><strong>Status:</strong> <span class="location-status ${statusClass}">${statusText}</span></p>
                ${location.Latitudine && location.Longitudine ? 
                    `<p><strong>Coordonate:</strong> ${parseFloat(location.Latitudine).toFixed(6)}, ${parseFloat(location.Longitudine).toFixed(6)}</p>` : ''}
            </div>
            
            <div class="detail-section">
                <h4>Statistici</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Comenzi astăzi:</span>
                        <span class="stat-value">${stats.orders_today || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comenzi luna aceasta:</span>
                        <span class="stat-value">${stats.orders_month || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comenzi anul acesta:</span>
                        <span class="stat-value">${stats.orders_year || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Rata de finalizare:</span>
                        <span class="stat-value">${(stats.completion_rate || 0).toFixed(1)}%</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Servicii Disponibile</h4>
                <div class="services-list">
                    <span class="service-tag">Spălare covoare</span>
                    <span class="service-tag">Spălare auto</span>
                    <span class="service-tag">Curățenie textile</span>
                </div>
            </div>
            
            ${resources.length > 0 ? `
            <div class="detail-section">
                <h4>Resurse</h4>
                <div class="resources-list">
                    ${resources.map(resource => `
                        <div class="resource-item">
                            <strong>${escapeHtml(resource.Nume)}</strong> (${escapeHtml(resource.Tip)}):
                            <span class="resource-quantity">${resource.CantitateDisponibila || 0} disponibile</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
            
            ${orders.length > 0 ? `
            <div class="detail-section">
                <h4>Comenzi Recente</h4>
                <div class="orders-list">
                    ${orders.map(order => `
                        <div class="order-item">
                            <strong>${escapeHtml(order.client_name)}</strong> - ${escapeHtml(order.TipServiciu)}
                            <br><small>Data: ${order.DataProgramare} | Status: ${escapeHtml(order.Status)}</small>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

/**
 * Close location modal
 */
function closeLocationModal() {
    const modal = document.getElementById('locationModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentLocationModal = null;
}

/**
 * Initialize contact form
 */
function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitContactForm();
        });
    }
}

/**
 * Submit contact form via AJAX
 */
function submitContactForm() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    
    // Add CSRF token
    if (window.csrfToken) {
        formData.append('csrf_token', window.csrfToken);
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Se trimite...';
    submitButton.disabled = true;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showAlert('Mesajul a fost trimis cu succes!', 'success');
                        form.reset();
                    } else {
                        showAlert(response.error || 'Eroare la trimiterea mesajului.', 'error');
                    }
                } catch (e) {
                    showAlert('Eroare la procesarea răspunsului.', 'error');
                }
            } else {
                showAlert('Eroare la trimiterea mesajului.', 'error');
            }
        }
    };
    
    xhr.onerror = function() {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        showAlert('Eroare de conexiune.', 'error');
    };
    
    xhr.send(formData);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Close modal when clicking outside
 */
document.addEventListener('click', function(e) {
    const modal = document.getElementById('locationModal');
    if (modal && e.target === modal) {
        closeLocationModal();
    }
});

/**
 * Close modal with Escape key
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLocationModal();
    }
});

// Export functions for global access
window.viewLocationDetails = viewLocationDetails;
window.closeLocationModal = closeLocationModal;
window.toggleMobileMenu = toggleMobileMenu;
