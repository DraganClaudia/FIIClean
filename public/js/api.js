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

const ResourcesAPI = {
    getAll: () => apiRequest('resource=resources&action=list'),
    
    create: (data) => apiRequest('resource=resources&action=create', {
        method: 'POST',
        body: JSON.stringify(data)
    }),

    addQuantity: (name, amount) => apiRequest('resource=resources&action=addQuantity', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `name=${encodeURIComponent(name)}&amount=${encodeURIComponent(amount)}`
    }),

    addQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=addQuantity`, {
    method: 'PUT',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
}),

    subtractQuantity: (name, departmentName, amount) => apiRequest(`resource=resources&action=subtractQuantity`, {
    method: 'PUT',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `name=${encodeURIComponent(name)}&department_name=${encodeURIComponent(departmentName)}&amount=${encodeURIComponent(amount)}`
})

};
