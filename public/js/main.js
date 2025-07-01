document.addEventListener('DOMContentLoaded', function() {
    loadLocations();
    
    // Add location button
    document.getElementById('add-location-btn').addEventListener('click', function() {
        document.getElementById('add-location-form').style.display = 'block';
    });
        
    // Cancel button
    document.getElementById('cancel-btn').addEventListener('click', function() {
        document.getElementById('add-location-form').style.display = 'none';
    });
    
    // Form submission
    document.getElementById('location-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addLocation();
    });
});

async function loadLocations() {
    try {
        const locations = await LocationsAPI.getAll();
        displayLocations(locations);
    } catch (error) {
        document.getElementById('locations-list').innerHTML = 'Eroare la încărcarea locațiilor.';
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
            <h3>${location.name}</h3>
            <p><strong>Adresă:</strong> ${location.address}</p>
            <p><strong>Servicii:</strong> ${location.services || 'Nu sunt specificate'}</p>
            <p><strong>Status:</strong> ${location.status}</p>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

async function addLocation() {
    const formData = {
        name: document.getElementById('name').value,
        address: document.getElementById('address').value,
        latitude: document.getElementById('latitude').value || null,
        longitude: document.getElementById('longitude').value || null,
        services: document.getElementById('services').value
    };
    
    try {
        await LocationsAPI.create(formData);
        document.getElementById('location-form').reset();
        document.getElementById('add-location-form').style.display = 'none';
        loadLocations(); // Reload the list
        alert('Locația a fost adăugată cu succes!');
    } catch (error) {
        alert('Eroare la adăugarea locației.');
    }
}

// Verifică dacă utilizatorul este logat
document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
    loadLocations();
});

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
