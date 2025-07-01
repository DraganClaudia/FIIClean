document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
    loadLocationsPreview();
});

async function loadLocationsPreview() {
    try {
        const locations = await LocationsAPI.getAll();
        displayLocationsPreview(locations.slice(0, 3)); // Afișează doar primele 3
    } catch (error) {
        document.getElementById('locations-list').innerHTML = 'Eroare la încărcarea locațiilor.';
    }
}

function displayLocationsPreview(locations) {
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
            <p><strong>Status:</strong> ${location.status === 'active' ? '🟢 Activ' : '🔴 Inactiv'}</p>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function checkAuthStatus() {
    const token = localStorage.getItem('auth_token');
    const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
    
    if (token && userData.username) {
        document.getElementById('user-info').style.display = 'inline-flex';
        document.getElementById('user-name').textContent = userData.first_name || userData.username;
        document.querySelector('.btn-login').style.display = 'none';
    }
}

function logout() {
    if (confirm('Sigur vrei să te deconectezi?')) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.reload();
    }
}
