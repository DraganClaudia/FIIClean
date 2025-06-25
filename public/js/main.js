/**
 * CaS - Cleaning Web Simulator
 * Main JavaScript file implementing complete AJAX functionality
 * Conform cerintelor: invocarea serviciilor Web in maniera asincrona via AJAX
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
 * Conform cerintelor: monitorizarea in timp-real
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
 * Conform cerintelor: localizare geografica si tip de servicii
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
