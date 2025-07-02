const API_BASE = AppConfig.api.baseUrl + AppConfig.api.endpoints.locations.split('?')[0];

async function apiRequest(endpoint, options = {}) {
    try {
        const url = `${API_BASE}?${endpoint}`;
        const controller = new AbortController();
        
        // Timeout configurabil
        const timeoutId = setTimeout(() => controller.abort(), AppConfig.api.timeout);
        
        const response = await fetch(url, {
            ...options,
            signal: controller.signal,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Request failed:', error);
        throw error;
    }
}

// Actualizează AuthAPI pentru a folosi configurarea
const AuthAPI = {
    login: (username, password) => apiRequest('../' + AppConfig.api.endpoints.auth + '?action=login', {
        method: 'POST',
        body: JSON.stringify({ username, password })
    }),
    
    getMe: () => apiRequest('../' + AppConfig.api.endpoints.auth + '?action=me', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem(AppConfig.security.tokenKey)}` }
    }),
    
    logout: () => {
        localStorage.removeItem(AppConfig.security.tokenKey);
        localStorage.removeItem(AppConfig.security.userDataKey);
    }
};
