// header.js
class Header {
    constructor() {
        this.currentUser = null;
        this.userRole = null;
        this.currentPage = this.getCurrentPage();
        this.init();
    }

    async init() {
        await this.loadHeader();
        this.loadUserData();
        this.updateHeader();
    }

    async loadHeader() {
        try {
            const response = await fetch('header.html');
            const headerHTML = await response.text();
            document.body.insertAdjacentHTML('afterbegin', headerHTML);
        } catch (error) {
            console.error('Error loading header:', error);
        }
    }

    loadUserData() {
        const token = localStorage.getItem('auth_token');
        const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
        
        if (token && userData.username) {
            this.currentUser = userData;
            this.userRole = userData.role;
        }
    }

    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('admin.html')) return 'admin';
        if (path.includes('manager.html')) return 'manager';
        if (path.includes('client.html')) return 'client';
        if (path.includes('transport.html')) return 'transport';
        if (path.includes('cleaner.html')) return 'cleaner';
        if (path.includes('orders.html')) return 'orders';
        if (path.includes('resources.html')) return 'resources';
        if (path.includes('locations.html')) return 'locations';
        if (path.includes('dashboard.html')) return 'dashboard';
        return 'index';
    }

    updateHeader() {
        this.updatePageInfo();
        this.updateNavigation();
        this.updateUserSection();
        this.updateActions();
    }

    updatePageInfo() {
        const pageInfos = {
            'admin': { icon: '👑', title: 'Dashboard Administrator', subtitle: 'Control complet asupra sistemului' },
            'manager': { icon: '👨‍💼', title: 'Dashboard Manager', subtitle: `Locația: ${this.currentUser?.location_name || 'Nedefinită'}` },
            'client': { icon: '👤', title: 'Contul Meu', subtitle: 'Gestionați comenzile dumneavoastră' },
            'transport': { icon: '🚛', title: 'Transport Dashboard', subtitle: 'Sarcinile de transport' },
            'cleaner': { icon: '🧽', title: 'Cleaner Dashboard', subtitle: 'Sarcinile de curățare' },
            'orders': { icon: '📋', title: 'Gestionare Comenzi', subtitle: this.getOrdersSubtitle() },
            'resources': { icon: '📦', title: 'Gestionare Resurse', subtitle: this.getResourcesSubtitle() },
            'locations': { icon: '📍', title: 'Gestionare Locații', subtitle: 'Administrare locații sistem' },
            'dashboard': { icon: '📊', title: 'Dashboard Public', subtitle: 'Statistici generale' }
        };

        const info = pageInfos[this.currentPage] || { icon: '🏠', title: 'FII-Clean', subtitle: 'Sistem management spălătorii' };
        
        const pageInfoEl = document.getElementById('page-info');
        if (pageInfoEl) {
            pageInfoEl.innerHTML = `
                <span class="page-icon">${info.icon}</span>
                <div class="page-text">
                    <div class="page-title">${info.title}</div>
                    <div class="page-subtitle">${info.subtitle}</div>
                </div>
            `;
        }
    }

    getOrdersSubtitle() {
        const subtitles = {
            'admin': 'Toate comenzile din sistem',
            'manager': 'Comenzile pentru locația mea',
            'worker_transport': 'Sarcinile mele de transport',
            'worker_cleaner': 'Sarcinile mele de curățare',
            'client': 'Comenzile mele'
        };
        return subtitles[this.userRole] || 'Vizualizare comenzi';
    }

    getResourcesSubtitle() {
        const subtitles = {
            'admin': 'Toate resursele din sistem',
            'manager': 'Resursele pentru locația mea'
        };
        return subtitles[this.userRole] || 'Gestionare resurse';
    }

    updateNavigation() {
        const navEl = document.getElementById('quick-nav');
        if (!navEl) return;

        if (!this.currentUser) {
            navEl.innerHTML = `
                <a href="index.html" class="nav-btn ${this.currentPage === 'index' ? 'active' : ''}">🏠 Acasă</a>
                <a href="locations.html" class="nav-btn ${this.currentPage === 'locations' ? 'active' : ''}">📍 Locații</a>
            `;
            return;
        }

        let navItems = [`<a href="index.html" class="nav-btn ${this.currentPage === 'index' ? 'active' : ''}">🏠 Acasă</a>`];

        if (this.userRole === 'admin') {
            navItems.push(`<a href="locations.html" class="nav-btn ${this.currentPage === 'locations' ? 'active' : ''}">📍 Locații</a>`);
        }

        navItems.push(`<a href="orders.html" class="nav-btn ${this.currentPage === 'orders' ? 'active' : ''}">📋 Comenzi</a>`);

        if (['admin', 'manager'].includes(this.userRole)) {
            navItems.push(`<a href="resources.html" class="nav-btn ${this.currentPage === 'resources' ? 'active' : ''}">📦 Resurse</a>`);
        }

        const dashboards = {
            'admin': 'admin.html',
            'manager': 'manager.html',
            'client': 'client.html',
            'worker_transport': 'transport.html',
            'worker_cleaner': 'cleaner.html'
        };
        const dashboardUrl = dashboards[this.userRole] || 'dashboard.html';
        const isOnDashboard = ['admin', 'manager', 'client', 'transport', 'cleaner'].includes(this.currentPage);
        navItems.push(`<a href="${dashboardUrl}" class="nav-btn ${isOnDashboard ? 'active' : ''}">📊 Dashboard</a>`);

        navEl.innerHTML = navItems.join('');
    }

    updateUserSection() {
        const userEl = document.getElementById('user-section');
        if (!userEl) return;

        if (!this.currentUser) {
            userEl.innerHTML = `<a href="login.html" class="login-btn">🔑 Conectare</a>`;
            return;
        }

        const roleLabels = {
            'admin': '👑 Admin',
            'manager': '👨‍💼 Manager',
            'client': '👤 Client',
            'worker_transport': '🚛 Transport',
            'worker_cleaner': '🧽 Cleaner'
        };

        userEl.innerHTML = `
            <div class="user-info">
                <div class="user-details">
                    <span class="user-name">${this.currentUser.first_name || this.currentUser.username}</span>
                    <span class="user-role">${roleLabels[this.userRole] || this.userRole}</span>
                </div>
                <button id="logout-btn" class="logout-btn">🚪 Logout</button>
            </div>
        `;
        
        // Adaugă event listener pentru logout
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    updateActions() {
        const actionsEl = document.getElementById('header-actions');
        if (!actionsEl) return;

        if (!this.currentUser) {
            actionsEl.style.display = 'none';
            return;
        }

        const actions = {
            'admin': this.getAdminActions(),
            'manager': this.getManagerActions(),
            'client': this.getClientActions(),
            'transport': this.getTransportActions(),
            'cleaner': this.getCleanerActions(),
            'orders': this.getOrdersActions(),
            'resources': this.getResourcesActions(),
            'locations': this.getLocationsActions()
        };

        const actionHTML = actions[this.currentPage] || '';
        
        if (actionHTML) {
            actionsEl.innerHTML = `<div class="actions-group">${actionHTML}</div>`;
            actionsEl.style.display = 'block';
        } else {
            actionsEl.style.display = 'none';
        }
    }

    getAdminActions() {
        return `
            <button onclick="window.location.href='admin.html'" class="action-btn btn-primary">👑 Admin Panel</button>
            <button onclick="window.location.href='orders.html'" class="action-btn btn-success">📋 Toate Comenzile</button>
            <button onclick="window.location.href='locations.html'" class="action-btn btn-warning">📍 Gestionare Locații</button>
            <button onclick="window.location.href='resources.html'" class="action-btn btn-info">📦 Gestionare Resurse</button>
        `;
    }

    getManagerActions() {
        return `
            <button onclick="window.location.href='manager.html'" class="action-btn btn-primary">👨‍💼 Manager Dashboard</button>
            <button onclick="window.location.href='orders.html'" class="action-btn btn-success">📋 Comenzi Locația</button>
            <button onclick="window.location.href='resources.html'" class="action-btn btn-warning">📦 Resurse Locația</button>
        `;
    }

    getClientActions() {
        const actions = `
            <button onclick="window.location.href='client.html'" class="action-btn btn-primary">👤 Contul Meu</button>
            <button id="quick-order-btn" class="action-btn btn-success">➕ Comandă Rapidă</button>
            <button onclick="window.location.href='orders.html'" class="action-btn btn-info">📋 Comenzile Mele</button>
        `;

        // Adaugă event listener pentru comanda rapidă
        setTimeout(() => {
            const quickOrderBtn = document.getElementById('quick-order-btn');
            if (quickOrderBtn) quickOrderBtn.addEventListener('click', () => this.quickOrder());
        }, 100);

        return actions;
    }

    getTransportActions() {
        const actions = `
            <button onclick="window.location.href='transport.html'" class="action-btn btn-primary">🚛 Transport Dashboard</button>
            <button onclick="window.location.href='orders.html'" class="action-btn btn-warning">📋 Sarcinile Mele</button>
            <button id="filter-pending-transport-btn" class="action-btn btn-success">⏳ Sarcini În Așteptare</button>
        `;

        // Adaugă event listener pentru filtrare
        setTimeout(() => {
            const filterBtn = document.getElementById('filter-pending-transport-btn');
            if (filterBtn) filterBtn.addEventListener('click', () => this.filterPendingTasks());
        }, 100);

        return actions;
    }

    getCleanerActions() {
        const actions = `
            <button onclick="window.location.href='cleaner.html'" class="action-btn btn-primary">🧽 Cleaner Dashboard</button>
            <button onclick="window.location.href='orders.html'" class="action-btn btn-warning">📋 Sarcinile Mele</button>
            <button id="filter-pending-cleaner-btn" class="action-btn btn-success">⏳ Sarcini În Așteptare</button>
        `;

        // Adaugă event listener pentru filtrare
        setTimeout(() => {
            const filterBtn = document.getElementById('filter-pending-cleaner-btn');
            if (filterBtn) filterBtn.addEventListener('click', () => this.filterPendingTasks());
        }, 100);

        return actions;
    }

    getOrdersActions() {
        const canCreateOrders = ['admin', 'manager'].includes(this.userRole);
        let actions = [];

        if (canCreateOrders) {
            actions.push(`<button id="add-order-header-btn" class="action-btn btn-success">➕ Adaugă Comandă</button>`);
        }

        if (this.userRole === 'client') {
            actions.push(`<button onclick="window.location.href='client.html'" class="action-btn btn-success">➕ Comandă Nouă</button>`);
        }

        actions.push(`<button id="refresh-orders-btn" class="action-btn btn-info">🔄 Actualizează</button>`);

        if (this.userRole === 'admin') {
            actions.push(`<button id="export-orders-btn" class="action-btn btn-warning">📊 Export</button>`);
        }

        // Adaugă event listeners pentru butoane
        setTimeout(() => {
            const addBtn = document.getElementById('add-order-header-btn');
            if (addBtn) addBtn.addEventListener('click', () => this.showAddOrderForm());
            
            const refreshBtn = document.getElementById('refresh-orders-btn');
            if (refreshBtn) refreshBtn.addEventListener('click', () => this.refreshOrders());
            
            const exportBtn = document.getElementById('export-orders-btn');
            if (exportBtn) exportBtn.addEventListener('click', () => this.exportOrders());
        }, 100);

        return actions.join('');
    }

    getResourcesActions() {
        let actions = [];

        if (this.userRole === 'admin') {
            actions.push(`<button id="add-resource-header-btn" class="action-btn btn-success">➕ Adaugă Resursă</button>`);
        }

        actions.push(`<button id="show-low-stock-btn" class="action-btn btn-warning">⚠️ Stoc Redus</button>`);
        actions.push(`<button id="refresh-resources-btn" class="action-btn btn-info">🔄 Actualizează</button>`);

        if (this.userRole === 'admin') {
            actions.push(`<button id="export-resources-btn" class="action-btn btn-secondary">📊 Export</button>`);
        }

        // Adaugă event listeners pentru butoane
        setTimeout(() => {
            const addBtn = document.getElementById('add-resource-header-btn');
            if (addBtn) addBtn.addEventListener('click', () => this.showAddResourceForm());
            
            const lowStockBtn = document.getElementById('show-low-stock-btn');
            if (lowStockBtn) lowStockBtn.addEventListener('click', () => this.showLowStock());
            
            const refreshBtn = document.getElementById('refresh-resources-btn');
            if (refreshBtn) refreshBtn.addEventListener('click', () => this.refreshResources());
            
            const exportBtn = document.getElementById('export-resources-btn');
            if (exportBtn) exportBtn.addEventListener('click', () => this.exportResources());
        }, 100);

        return actions.join('');
    }

    getLocationsActions() {
        if (this.userRole !== 'admin') return '';

        const actions = `
            <button id="create-location-btn" class="action-btn btn-success">➕ Adaugă Locație</button>
            <button id="filter-maintenance-btn" class="action-btn btn-warning">🔧 Mentenanță</button>
            <button id="filter-inactive-btn" class="action-btn btn-danger">🔴 Inactive</button>
            <button id="refresh-locations-btn" class="action-btn btn-info">🔄 Actualizează</button>
        `;

        // Adaugă event listeners pentru butoane
        setTimeout(() => {
            const createBtn = document.getElementById('create-location-btn');
            if (createBtn) createBtn.addEventListener('click', () => this.showCreateLocationModal());
            
            const maintenanceBtn = document.getElementById('filter-maintenance-btn');
            if (maintenanceBtn) maintenanceBtn.addEventListener('click', () => this.filterLocationsByStatus('maintenance'));
            
            const inactiveBtn = document.getElementById('filter-inactive-btn');
            if (inactiveBtn) inactiveBtn.addEventListener('click', () => this.filterLocationsByStatus('inactive'));
            
            const refreshBtn = document.getElementById('refresh-locations-btn');
            if (refreshBtn) refreshBtn.addEventListener('click', () => this.refreshLocations());
        }, 100);

        return actions;
    }

    // === ACTION METHODS ===
    quickOrder() {
        if (this.userRole === 'client') {
            window.location.href = 'client.html';
        } else {
            alert('Doar clienții pot plasa comenzi rapide.');
        }
    }

    showAddOrderForm() {
        if (typeof showAddOrderForm === 'function') {
            showAddOrderForm();
        } else if (typeof window.showAddOrderForm === 'function') {
            window.showAddOrderForm();
        } else {
            const form = document.getElementById('add-order-form');
            if (form) form.style.display = 'block';
        }
    }

    showAddResourceForm() {
        if (typeof showAddResourceForm === 'function') {
            showAddResourceForm();
        } else if (typeof window.showAddResourceForm === 'function') {
            window.showAddResourceForm();
        } else {
            const form = document.getElementById('add-resource-form');
            if (form) form.style.display = 'block';
        }
    }

    showCreateLocationModal() {
        if (typeof showCreateLocationModal === 'function') {
            showCreateLocationModal();
        } else if (typeof window.showCreateLocationModal === 'function') {
            window.showCreateLocationModal();
        } else {
            const modal = document.getElementById('location-modal');
            if (modal) modal.style.display = 'block';
        }
    }

    refreshOrders() {
        if (typeof loadRoleBasedData === 'function') loadRoleBasedData();
        else if (typeof loadOrders === 'function') loadOrders();
    }

    refreshResources() {
        if (typeof loadRoleBasedData === 'function') loadRoleBasedData();
        else if (typeof loadResources === 'function') loadResources();
    }

    refreshLocations() {
        if (typeof loadLocations === 'function') loadLocations();
    }

    showLowStock() {
        if (typeof showLowStockAlert === 'function') showLowStockAlert();
        else { const filter = document.getElementById('stock-filter'); if (filter) { filter.value = 'low'; if (typeof filterResources === 'function') filterResources(); } }
    }

    filterPendingTasks() {
        const filter = document.getElementById('status-filter');
        if (filter) { filter.value = 'pending'; if (typeof filterOrdersByStatus === 'function') filterOrdersByStatus('pending'); }
    }

    filterLocationsByStatus(status) {
        const filter = document.getElementById('status-filter');
        if (filter) { filter.value = status; if (typeof filterByStatus === 'function') filterByStatus(status); }
    }

    exportOrders() {
        if (typeof ExportAPI !== 'undefined') ExportAPI.exportData('orders', 'csv');
        else alert('Funcția de export va fi implementată în curând.');
    }

    exportResources() {
        if (typeof ExportAPI !== 'undefined') ExportAPI.exportData('resources', 'csv');
        else alert('Funcția de export va fi implementată în curând.');
    }

    logout() {
        if (confirm('Sigur vrei să te deconectezi?')) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            localStorage.removeItem('quick_order_service');
            window.location.href = 'index.html';
        }
    }
}

// Inițializare automată
let header;
document.addEventListener('DOMContentLoaded', async function() {
    header = new Header();
});

// Export global
if (typeof window !== 'undefined') {
    window.Header = Header;
}
