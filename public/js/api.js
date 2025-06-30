const API_BASE = '../api/index.php';

async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}?${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },  
            ...options
        });
        
        if (!response.ok) {
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
        body: JSON.stringify(locationData)
    })
};

const OrdersAPI = {
    getAll: () => apiRequest('resource=orders&action=list'),

    getByLocation: (locationId) => apiRequest(`resource=orders&action=by-location&id=${locationId}`),

    create: (orderData) => apiRequest('resource=orders&action=create', {
        method: 'POST',
        body: JSON.stringify(orderData)
    }),

    updateStatus: (orderId, status) => apiRequest(`resource=orders&action=update-status&id=${orderId}`, {
        method: 'PUT',
        body: JSON.stringify({status: status})
    }),

    getStatistics: () => apiRequest('resource=orders&action=statistics')
};
