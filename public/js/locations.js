document.addEventListener('DOMContentLoaded', function() {
    loadLocations();
});

async function loadLocations() {
    try {
        const locations = await LocationsAPI.getAll();
        displayLocations(locations);
    } catch (error) {
        console.error('Eroare detaliată:', error);
        document.getElementById('locations-list').innerHTML = 'Eroare la încărcarea locațiilor: ' + error.message;
    }
}

function displayLocations(locations) {
    const container = document.getElementById('locations-list');
    
    if (locations.length === 0) {
        container.innerHTML = '<p>Nu există locații înregistrate.</p>';
        return;
    }
    
    const html = locations.map(location => `
        <div class="location-item">
            <h3>📍 ${location.name}</h3>
            <p><strong>Adresă:</strong> ${location.address}</p>
            <p><strong>Servicii:</strong> ${location.services || 'Nu sunt specificate'}</p>
            <p><strong>Status:</strong> ${getStatusDisplay(location.status)}</p>
            ${location.latitude && location.longitude ? `
                <p><strong>Coordonate:</strong> ${location.latitude}, ${location.longitude}</p>
            ` : ''}
            <p><strong>Creat la:</strong> ${new Date(location.created_at).toLocaleDateString('ro-RO')}</p>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function getStatusDisplay(status) {
    const statusMap = {
        'active': '<span style="color: #27ae60; font-weight: bold;">🟢 Activ</span>',
        'inactive': '<span style="color: #e74c3c; font-weight: bold;">🔴 Inactiv</span>',
        'maintenance': '<span style="color: #f39c12; font-weight: bold;">🔧 Mentenanță</span>'
    };
    return statusMap[status] || status;
}

// Funcție pentru refresh manual
function refreshLocations() {
    loadLocations();
}
