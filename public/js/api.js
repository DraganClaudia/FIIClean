const API_BASE = '../api/index.php';

async function apiRequest(endpoint, options = {}) {
    try {
        const url = `${API_BASE}?${endpoint}`;
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },  
            ...options
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Request failed:', error);
        throw error;
    }
}

const LocationsAPI = {
    getAll: () => apiRequest('resource=locations&action=list'),
    
    create: (locationData) => apiRequest('resource=locations&action=create', {
        method: 'POST',
        body: JSON.stringify(locationData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    
    updateStatus: (locationId, status) => apiRequest(`resource=locations&action=update-status&id=${locationId}`, {
        method: 'PUT',
        body: JSON.stringify({ status }),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })
};

const OrdersAPI = {
    getAll: () => apiRequest('resource=orders&action=list'),
    getByLocation: (locationId) => apiRequest(`resource=orders&action=by-location&id=${locationId}`),
    getMyOrders: () => apiRequest('resource=orders&action=my-orders', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    getAssigned: () => apiRequest('resource=orders&action=assigned', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    
    create: (orderData) => apiRequest('resource=orders&action=create', {
        method: 'POST',
        body: JSON.stringify(orderData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    
    updateStatus: (orderId, statusData) => apiRequest(`resource=orders&action=updateStatus&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify(typeof statusData === 'string' ? { status: statusData } : statusData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    
    assignWorker: (orderId, workerId, workerType) => apiRequest(`resource=orders&action=assign-worker&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify({ worker_id: workerId, worker_type: workerType }),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),
    
    getStatistics: () => apiRequest('resource=orders&action=statistics')
};

const ResourcesAPI = {
    getAll: () => apiRequest('resource=resources&action=list'),
    getByLocation: (locationId) => apiRequest(`resource=resources&action=by-location&id=${locationId}`),
    getLowStock: (threshold) => {
        const params = threshold ? `&threshold=${threshold}` : '';
        return apiRequest(`resource=resources&action=low-stock${params}`);
    },
    
    create: (data) => apiRequest('resource=resources&action=create', {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    addQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=addQuantity`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    }),

    subtractQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=subtractQuantity`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
    }),

    delete: (name, locationId) => apiRequest(`resource=resources&action=delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: `name=${encodeURIComponent(name)}&location_id=${encodeURIComponent(locationId)}`
    })
};

const UsersAPI = {
    getList: (role = null) => {
        const params = role ? `&role=${role}` : '';
        return apiRequest(`../api/users.php?action=list${params}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
        });
    },

    create: (userData) => apiRequest('../api/users.php?action=create', {
        method: 'POST',
        body: JSON.stringify(userData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    update: (userId, userData) => apiRequest(`../api/users.php?action=update&id=${userId}`, {
        method: 'PUT',
        body: JSON.stringify(userData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    delete: (userId) => apiRequest(`../api/users.php?action=delete&id=${userId}`, {
        method: 'PUT',
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    getProfile: () => apiRequest('../api/users.php?action=profile', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    updateProfile: (profileData) => apiRequest('../api/users.php?action=update-profile', {
        method: 'POST',
        body: JSON.stringify(profileData),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    getWorkers: (locationId = null) => {
        const params = locationId ? `&location_id=${locationId}` : '';
        return apiRequest(`../api/users.php?action=workers${params}`, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
        });
    }
};

const AuthAPI = {
    login: (username, password) => apiRequest('../api/auth.php?action=login', {
        method: 'POST',
        body: JSON.stringify({ username, password })
    }),

    register: (userData) => apiRequest('../api/auth.php?action=register', {
        method: 'POST',
        body: JSON.stringify(userData)
    }),

    getMe: () => apiRequest('../api/auth.php?action=me', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    logout: () => apiRequest('../api/auth.php?action=logout', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    }),

    changePassword: (currentPassword, newPassword) => apiRequest('../api/auth.php?action=change-password', {
        method: 'POST',
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        }),
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })
};

const ExportAPI = {
    exportData: (type, format) => {
        const url = `../api/index.php?resource=export&type=${type}&format=${format}`;
        window.open(url, '_blank');
    }
};

const StatusHelpers = {
    getStatusLabel: (status) => {
        const labels = {
            'pending': '⏳ În așteptare',
            'in_progress': '🔄 În progres', 
            'completed': '✅ Completată',
            'cancelled': '❌ Anulată'
        };
        return labels[status] || status;
    },
    
    getStatusColor: (status) => {
        const colors = {
            'pending': '#f39c12',
            'in_progress': '#3498db',
            'completed': '#27ae60',
            'cancelled': '#e74c3c'
        };
        return colors[status] || '#95a5a6';
    },
    
    getServiceName: (serviceType) => {
        const services = {
            'covoare': 'Spălare Covoare',
            'auto': 'Spălare Auto', 
            'haine': 'Curățare Haine',
            'textile': 'Curățare Textile'
        };
        return services[serviceType] || serviceType;
    }
};

if (typeof window !== 'undefined') {
    Object.assign(window, {
        OrdersAPI,
        LocationsAPI,
        ResourcesAPI,
        UsersAPI,
        AuthAPI,
        ExportAPI,
        StatusHelpers
    });
}
