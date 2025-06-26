/**
 * Main JavaScript file implementing complete AJAX functionality
 */

// Global variables
let currentModal = null;
let updateInterval = null;
let csrfToken = null;

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    // Get CSRF token
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Load real-time dashboard statistics via AJAX
    loadDashboardStats();
    
    // Initialize contact form AJAX
    initializeContactForm();
    
    // Initialize order management AJAX
    initializeOrderManagement();
    
    // Set up periodic updates for real-time monitoring (conform cerintelor)
    startRealTimeUpdates();
    
    // Initialize location details AJAX
    initializeLocationDetails();
    
    // Initialize resource monitoring
    initializeResourceMonitoring();
}

/**
 * Real-time monitoring updates via AJAX
 */
function startRealTimeUpdates() {
    // Update dashboard every 30 seconds
    updateInterval = setInterval(function() {
        updateDashboardStats();
        updateLocationStatus();
        updateResourceAlerts();
    }, 30000);
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

function toggleMobileMenu() {
    const nav = document.querySelector('.main-nav');
    if (nav) {
        nav.classList.toggle('active');
    }
}

/**
 * Load dashboard statistics via AJAX API call
 */
function loadDashboardStats() {
    makeAjaxRequest('GET', '?controller=api&action=getStatistici&tip=general', null, {
        success: function(response) {
            if (response.success && response.data) {
                updateDashboardDisplay(response.data);
            }
        },
        error: function() {
            console.error('Eroare la incarcarea statisticilor');
        }
    });
}

/**
 * Update dashboard statistics display
 */
function updateDashboardStats() {
    loadDashboardStats();
}

function updateDashboardDisplay(stats) {
    // Update sedii active
    const activeSediiElement = document.getElementById('active-sedii');
    if (activeSediiElement) {
        activeSediiElement.textContent = stats.sedii_active || 0;
    }
    
    // Update comenzi astazi
    const todayOrdersElement = document.getElementById('today-orders');
    if (todayOrdersElement) {
        todayOrdersElement.textContent = stats.comenzi_astazi || 0;
    }
    
    // Update eficienta medie
    const avgEfficiencyElement = document.getElementById('avg-efficiency');
    if (avgEfficiencyElement) {
        const efficiency = stats.comenzi_finalizate && stats.total_comenzi ? 
            ((stats.comenzi_finalizate / stats.total_comenzi) * 100).toFixed(1) : 0;
        avgEfficiencyElement.textContent = efficiency + '%';
    }
}

/**
 * Location details via AJAX
 */
function initializeLocationDetails() {
    // Add click handlers for location cards
    const locationCards = document.querySelectorAll('.location-card');
    locationCards.forEach(card => {
        const button = card.querySelector('button[onclick*="viewSediuDetails"]');
        if (button) {
            const sediuId = button.getAttribute('onclick').match(/\d+/)[0];
            button.onclick = () => viewSediuDetails(sediuId);
        }
    });
}

function viewSediuDetails(sediuId) {
    const modal = document.getElementById('sediuModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!modal || !modalBody || !modalTitle) {
        console.error('Modal elements not found');
        return;
    }
    
    // Show modal
    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading">Se încarcă detaliile sediului...</div>';
    modalTitle.textContent = 'Detalii Sediu';
    currentModal = modal;
    
    // AJAX call to API
    makeAjaxRequest('GET', `?controller=api&action=getSediu&id=${sediuId}`, null, {
        success: function(response) {
            if (response.success && response.data) {
                displaySediuDetails(response.data);
            } else {
                modalBody.innerHTML = `<div class="alert alert-error">${response.error || 'Eroare la încărcarea datelor'}</div>`;
            }
        },
        error: function() {
            modalBody.innerHTML = '<div class="alert alert-error">Eroare la încărcarea datelor.</div>';
        }
    });
}

function displaySediuDetails(data) {
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    
    const sediu = data.sediu;
    const stats = data.statistici || {};
    
    modalTitle.textContent = `Detalii: ${sediu.Nume}`;
    
    const statusClass = sediu.Stare === 'activ' ? 'status-active' : 'status-inactive';
    const statusText = sediu.Stare === 'activ' ? 'Operațional' : 
                      sediu.Stare === 'reparatii' ? 'În reparații' : 'Inactiv';
    
    modalBody.innerHTML = `
        <div class="sediu-details">
            <div class="detail-section">
                <h4>Informații Generale</h4>
                <p><strong>Nume:</strong> ${escapeHtml(sediu.Nume)}</p>
                <p><strong>Adresa:</strong> ${escapeHtml(sediu.Adresa || 'N/A')}</p>
                <p><strong>Status:</strong> <span class="location-status ${statusClass}">${statusText}</span></p>
                ${sediu.Latitudine && sediu.Longitudine ? 
                    `<p><strong>Coordonate:</strong> ${parseFloat(sediu.Latitudine).toFixed(6)}, ${parseFloat(sediu.Longitudine).toFixed(6)}</p>` : ''}
            </div>
            
            <div class="detail-section">
                <h4>Statistici Timp Real</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Comenzi astăzi:</span>
                        <span class="stat-value">${stats.comenzi_astazi || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comenzi luna aceasta:</span>
                        <span class="stat-value">${stats.comenzi_luna || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comenzi anul acesta:</span>
                        <span class="stat-value">${stats.comenzi_an || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Rata de finalizare:</span>
                        <span class="stat-value">${(stats.rata_finalizare || 0).toFixed(1)}%</span>
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
            
            ${data.comenzi_recente && data.comenzi_recente.length > 0 ? `
            <div class="detail-section">
                <h4>Comenzi Recente</h4>
                <div class="orders-list">
                    ${data.comenzi_recente.map(order => `
                        <div class="order-item">
                            <strong>${escapeHtml(order.nume_client || 'Client')}</strong> - ${getServiceTypeName(order.TipServiciu)}
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
 * Close modal
 */
function closeSediuModal() {
    if (currentModal) {
        currentModal.style.display = 'none';
        currentModal = null;
    }
}

/**
 * Contact form AJAX submission
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

function submitContactForm() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    
    // Add CSRF token
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const btnText = submitButton.querySelector('.btn-text');
    const btnLoading = submitButton.querySelector('.btn-loading');
    
    if (btnText && btnLoading) {
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
    }
    submitButton.disabled = true;
    
    makeAjaxRequest('POST', form.action, formData, {
        success: function(response) {
            if (response.success) {
                showAlert('Mesajul a fost trimis cu succes!', 'success');
                form.reset();
            } else {
                showAlert(response.error || 'Eroare la trimiterea mesajului.', 'error');
            }
        },
        error: function() {
            showAlert('Eroare la trimiterea mesajului.', 'error');
        },
        complete: function() {
            if (btnText && btnLoading) {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }
            submitButton.disabled = false;
        }
    });
}

/**
 * Order management AJAX
 */
function initializeOrderManagement() {
    // Initialize order creation form if exists
    const orderForm = document.getElementById('comandaForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitOrder();
        });
    }
    
    // Initialize order status updates
    initializeOrderStatusUpdates();
}

function submitOrder() {
    const form = document.getElementById('comandaForm');
    const formData = new FormData(form);
    
    // Convert form data to JSON for API
    const orderData = {
        client_id: formData.get('client_id'),
        sediu_id: formData.get('sediu_id'),
        tip_serviciu: formData.get('tip_serviciu'),
        data_programare: formData.get('data_programare'),
        transport: formData.has('transport'),
        recurenta: formData.has('recurenta')
    };
    
    makeAjaxRequest('POST', '?controller=api&action=createComanda', JSON.stringify(orderData), {
        success: function(response) {
            if (response.success) {
                showAlert('Comanda a fost creată cu succes!', 'success');
                form.reset();
                // Redirect to orders page
                setTimeout(() => {
                    window.location.href = '?controller=client&action=comenzile';
                }, 2000);
            } else {
                showAlert(response.error || 'Eroare la crearea comenzii.', 'error');
            }
        },
        error: function() {
            showAlert('Eroare la crearea comenzii.', 'error');
        }
    });
}

function initializeOrderStatusUpdates() {
    // Add event listeners for status update buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-action="update-status"]')) {
            const orderId = e.target.getAttribute('data-order-id');
            const newStatus = e.target.getAttribute('data-new-status');
            updateOrderStatus(orderId, newStatus);
        }
        
        if (e.target.matches('[data-action="cancel-order"]')) {
            const orderId = e.target.getAttribute('data-order-id');
            cancelOrder(orderId);
        }
    });
}

function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Sigur doriți să schimbați statusul comenzii la "${newStatus}"?`)) {
        return;
    }
    
    const updateData = { status: newStatus };
    
    makeAjaxRequest('PUT', `?controller=api&action=updateComanda&id=${orderId}`, JSON.stringify(updateData), {
        success: function(response) {
            if (response.success) {
                showAlert('Status comandă actualizat cu succes!', 'success');
                // Refresh the page or update the display
                location.reload();
            } else {
                showAlert(response.error || 'Eroare la actualizarea statusului.', 'error');
            }
        },
        error: function() {
            showAlert('Eroare la actualizarea statusului.', 'error');
        }
    });
}

function cancelOrder(orderId) {
    if (!confirm('Sigur doriți să anulați această comandă?')) {
        return;
    }
    
    updateOrderStatus(orderId, 'anulata');
}

/**
 * Resource monitoring AJAX
 */
function initializeResourceMonitoring() {
    // Check for low stock resources
    checkResourceAlerts();
    
    // Initialize resource update forms
    const resourceForms = document.querySelectorAll('.resource-update-form');
    resourceForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateResourceQuantity(this);
        });
    });
}

function checkResourceAlerts() {
    makeAjaxRequest('GET', '?controller=api&action=getResurse&stoc_redus=1', null, {
        success: function(response) {
            if (response.success && response.data.length > 0) {
                showResourceAlerts(response.data);
            }
        },
        error: function() {
            console.error('Eroare la verificarea stocurilor');
        }
    });
}

function updateResourceAlerts() {
    checkResourceAlerts();
}

function showResourceAlerts(lowStockResources) {
    const alertContainer = document.getElementById('resource-alerts');
    if (!alertContainer) return;
    
    const alertHtml = lowStockResources.map(resource => `
        <div class="alert alert-warning">
            <strong>STOC REDUS:</strong> ${escapeHtml(resource.Nume)} 
            (${resource.CantitateDisponibila} disponibile)
        </div>
    `).join('');
    
    alertContainer.innerHTML = alertHtml;
}

function updateResourceQuantity(form) {
    const formData = new FormData(form);
    const resourceId = formData.get('resource_id');
    const quantity = formData.get('quantity');
    
    const updateData = { cantitate: parseInt(quantity) };
    
    makeAjaxRequest('PUT', `?controller=api&action=updateResursa&id=${resourceId}`, JSON.stringify(updateData), {
        success: function(response) {
            if (response.success) {
                showAlert('Cantitatea resursei a fost actualizată!', 'success');
                form.reset();
                // Refresh resource display
                updateResourceAlerts();
            } else {
                showAlert(response.error || 'Eroare la actualizarea resursei.', 'error');
            }
        },
        error: function() {
            showAlert('Eroare la actualizarea resursei.', 'error');
        }
    });
}

/**
 * Location status updates for real-time monitoring
 */
function updateLocationStatus() {
    makeAjaxRequest('GET', '?controller=api&action=getSedii', null, {
        success: function(response) {
            if (response.success && response.data) {
                updateLocationDisplays(response.data);
            }
        },
        error: function() {
            console.error('Eroare la actualizarea statusului sediilor');
        }
    });
}

function updateLocationDisplays(sedii) {
    sedii.forEach(sediu => {
        const locationCard = document.querySelector(`[data-sediu-id="${sediu.id}"]`);
        if (locationCard) {
            // Update status badge
            const statusBadge = locationCard.querySelector('.location-status');
            if (statusBadge) {
                statusBadge.className = `location-status ${sediu.Stare === 'activ' ? 'status-active' : 'status-inactive'}`;
                statusBadge.textContent = sediu.Stare === 'activ' ? 'Operational' : 
                                        sediu.Stare === 'reparatii' ? 'Reparații' : 'Inactiv';
            }
            
            // Update statistics if available
            if (sediu.stats) {
                const statsElements = locationCard.querySelectorAll('[data-stat]');
                statsElements.forEach(elem => {
                    const statType = elem.getAttribute('data-stat');
                    if (sediu.stats[statType] !== undefined) {
                        elem.textContent = sediu.stats[statType];
                    }
                });
            }
        }
    });
}

/**
 * Generic AJAX request function
 */
function makeAjaxRequest(method, url, data, callbacks) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    
    // Set headers
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    if (data && (method === 'POST' || method === 'PUT')) {
        if (typeof data === 'string') {
            xhr.setRequestHeader('Content-Type', 'application/json');
        }
    }
    
    // Set up event handlers
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            let response = null;
            
            try {
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                response = { success: false, error: 'Răspuns invalid de la server' };
            }
            
            if (xhr.status >= 200 && xhr.status < 300) {
                if (callbacks.success) {
                    callbacks.success(response);
                }
            } else {
                if (callbacks.error) {
                    callbacks.error(response);
                }
            }
            
            if (callbacks.complete) {
                callbacks.complete(response);
            }
        }
    };
    
    xhr.onerror = function() {
        if (callbacks.error) {
            callbacks.error({ success: false, error: 'Eroare de conexiune' });
        }
        if (callbacks.complete) {
            callbacks.complete(null);
        }
    };
    
    // Send request
    xhr.send(data);
}

/**
 * Utility functions
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getServiceTypeName(type) {
    const types = {
        'covor': 'Spălare covoare',
        'auto': 'Spălare auto', 
        'textil': 'Curățenie textile'
    };
    return types[type] || type;
}

/**
 * Export functions for global access
 */
window.viewSediuDetails = viewSediuDetails;
window.closeSediuModal = closeSediuModal;
window.toggleMobileMenu = toggleMobileMenu;
window.updateOrderStatus = updateOrderStatus;
window.cancelOrder = cancelOrder;

/**
 * Close modals when clicking outside or pressing Escape
 */
document.addEventListener('click', function(e) {
    if (currentModal && e.target === currentModal) {
        closeSediuModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentModal) {
        closeSediuModal();
    }
});

/**
 * Clean up intervals on page unload
 */
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
