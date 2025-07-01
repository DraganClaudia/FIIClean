class Header {
    constructor() {
        this.currentUser = JSON.parse(localStorage.getItem('user_data') || 'null');
        this.currentPage = this.getCurrentPage();
        this.init();
    }

    async init() {
        await this.loadHeader();
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
        return 'index';
    }

    updateHeader() {
        this.updatePageInfo();
        this.updateNavigation();
        this.updateUserSection();
    }

    updatePageInfo() {
        const pageInfos = {
            'admin': { icon: '👑', title: 'Dashboard Administrator', subtitle: 'Control complet asupra sistemului' },
            'manager': { icon: '👨‍💼', title: 'Dashboard Manager', subtitle: `Locația: ${this.currentUser?.location_name || 'Nedefinită'}` },
            'client': { icon: '👤', title: 'Contul Meu', subtitle: 'Gestionați comenzile dumneavoastră' },
            'transport': { icon: '🚛', title: 'Transport Dashboard', subtitle: 'Sarcinile de transport' },
            'cleaner': { icon: '🧽', title: 'Cleaner Dashboard', subtitle: 'Sarcinile de curățare' },
            'orders': { icon: '📋', title: 'Gestionare Comenzi', subtitle: 'Administrare comenzi' },
            'resources': { icon: '📦', title: 'Gestionare Resurse', subtitle: 'Administrare resurse' },
            'locations': { icon: '📍', title: 'Gestionare Locații', subtitle: 'Administrare locații' }
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

    updateNavigation() {
        const navEl = document.getElementById('quick-nav');
        if (!navEl) return;

        let navItems = [`<a href="index.html" class="nav-btn ${this.currentPage === 'index' ? 'active' : ''}">🏠 Acasă</a>`];

        if (this.currentUser) {
            navItems.push(`<a href="orders.html" class="nav-btn ${this.currentPage === 'orders' ? 'active' : ''}">📋 Comenzi</a>`);

            if (this.currentUser.role === 'admin') {
                navItems.push(`<a href="locations.html" class="nav-btn ${this.currentPage === 'locations' ? 'active' : ''}">📍 Locații</a>`);
            }

            if (['admin', 'manager'].includes(this.currentUser.role)) {
                navItems.push(`<a href="resources.html" class="nav-btn ${this.currentPage === 'resources' ? 'active' : ''}">📦 Resurse</a>`);
            }

            const dashboards = {
                'admin': 'admin.html',
                'manager': 'manager.html', 
                'client': 'client.html',
                'worker_transport': 'transport.html',
                'worker_cleaner': 'cleaner.html'
            };
            const dashboardUrl = dashboards[this.currentUser.role] || 'dashboard.html';
            const isOnDashboard = ['admin', 'manager', 'client', 'transport', 'cleaner'].includes(this.currentPage);
            navItems.push(`<a href="${dashboardUrl}" class="nav-btn ${isOnDashboard ? 'active' : ''}">📊 Dashboard</a>`);
        } else {
            navItems.push(`<a href="locations.html" class="nav-btn ${this.currentPage === 'locations' ? 'active' : ''}">📍 Locații</a>`);
        }

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
                    <span class="user-role">${roleLabels[this.currentUser.role] || this.currentUser.role}</span>
                </div>
                <button id="logout-btn" class="logout-btn">🚪 Logout</button>
            </div>
        `;
        
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    logout() {
        if (confirm('Sigur vrei să te deconectezi?')) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            window.location.href = 'index.html';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Header();
});

if (typeof window !== 'undefined') {
    window.Header = Header;
}
