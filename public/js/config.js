window.AppConfig = {
    api: {
        baseUrl: '../api/',
        timeout: 10000,
        endpoints: {
            auth: 'auth.php',
            locations: 'index.php?resource=locations',
            orders: 'index.php?resource=orders',
            resources: 'index.php?resource=resources',
            users: 'users.php',
            export: 'index.php?resource=export',
            rss: 'index.php?resource=rss'
        }
    },
    ui: {
        itemsPerPage: 10,
        autoRefreshInterval: 5 * 60 * 1000, // 5 minute
        animationDuration: 300,
        modalDelay: 1000
    },
    roles: {
        admin: { 
            dashboard: 'admin.html', 
            canEdit: true, 
            canDelete: true,
            canExport: true 
        },
        manager: { 
            dashboard: 'manager.html', 
            canEdit: true, 
            canDelete: false,
            canExport: true 
        },
        client: { 
            dashboard: 'client.html', 
            canEdit: false, 
            canDelete: false,
            canExport: false 
        },
        worker_transport: { 
            dashboard: 'transport.html', 
            canEdit: false, 
            canDelete: false,
            canExport: false 
        },
        worker_cleaner: { 
            dashboard: 'cleaner.html', 
            canEdit: false, 
            canDelete: false,
            canExport: false 
        }
    },
    validation: {
        passwordMinLength: 6,
        usernameMinLength: 3,
        emailRegex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    },
    security: {
        tokenKey: 'auth_token',
        userDataKey: 'user_data',
        sessionTimeout: 24 * 60 * 60 * 1000 // 24 ore
    }
};