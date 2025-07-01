document.addEventListener('DOMContentLoaded', function() {
    loadResources();
    loadLocationsForSelect();
    setupResourceButtons();
});

async function loadResources() {
    try {
        const resources = await ResourcesAPI.getAll();
        displayResources(resources);
    } catch (error) {
        document.getElementById('resources-list').innerHTML = 'Eroare la încărcarea resurselor.';
    }
}

function displayResources(resources) {
    const container = document.getElementById('resources-list');
    
    if (resources.length === 0) {
        container.innerHTML = '<p>Nu există resurse înregistrate.</p>';
        return;
    }
    
    const html = resources.map(resource => `
        <div class="resource-item" data-location="${resource.location_id}">
            <h3>${resource.name}</h3>
            <p><strong>Tip:</strong> ${resource.resource_type}</p>
            <p><strong>Locație ID:</strong> ${resource.location_id || 'nedefinit'}</p>
            <p><strong>Cantitate:</strong> ${resource.quantity} ${resource.unit}</p>
            <p><strong>Prag minim:</strong> ${resource.min_threshold} ${resource.unit}</p>
            <p><strong>Cost unitar:</strong> ${resource.cost_per_unit ? parseFloat(resource.cost_per_unit).toFixed(2) + ' RON' : 'n/a'}</p>
            <p><strong>Furnizor:</strong> ${resource.supplier || 'n/a'}</p>
            <p><strong>Ultima reîncărcare:</strong> ${resource.last_restocked ? new Date(resource.last_restocked).toLocaleDateString('ro-RO') : 'nedefinit'}</p>
            <button class="delete-resource-btn" data-name="${resource.name}" data-location-id="${resource.location_id}">
                Șterge
            </button>
        </div>
    `).join('');
    
    container.innerHTML = html;

    document.querySelectorAll('.delete-resource-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const name = this.dataset.name;
            const locationId = this.dataset.locationId;
            if (confirm(`Ești sigur că vrei să ștergi resursa ${name} din locația ${locationId}?`)) {
                deleteResource(name, locationId);
            }
        });
    });
}

async function loadLocationsForSelect() {
    try {
        const locations = await LocationsAPI.getAll();
        const select = document.getElementById('resource-location-select');
        const filter = document.getElementById('location-filter');
        
        locations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name;
            select.appendChild(option);
            
            const filterOption = document.createElement('option');
            filterOption.value = location.name;  // pentru filtrare după nume
            filterOption.textContent = location.name;
            filter.appendChild(filterOption);
        });
    } catch (error) {
        console.error('Error loading locations for select:', error);
    }
}

function setupResourceButtons() {
    document.getElementById('add-resource-btn').addEventListener('click', function() {
        document.getElementById('add-resource-form').style.display = 'block';
    });
    
    document.getElementById('cancel-resource-btn').addEventListener('click', function() {
        hideResourceForm();
    });
    
    document.getElementById('resource-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addResource();
    });
    
    document.getElementById('add-quantity-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addQuantityToResource();
    });

    document.getElementById('subtract-quantity-form').addEventListener('submit', function(e) {
        e.preventDefault();
        subtractQuantityFromResource();
    });
    
    document.getElementById('location-filter').addEventListener('change', function() {
        filterResourcesByDepartment(this.value);
    });
}

function filterResourcesByDepartment(departmentName) {
    const resources = document.querySelectorAll('.resource-item');
    
    resources.forEach(resource => {
        // filtrarea după ID-ul locației rămâne, dar opțional poți extinde să iei numele
        if (departmentName === 'all') {
            resource.style.display = 'block';
        } else {
            const locId = resource.dataset.location;
            const locName = getLocationNameById(locId);
            if (locName === departmentName) {
                resource.style.display = 'block';
            } else {
                resource.style.display = 'none';
            }
        }
    });
}

async function addResource() {
    const data = {
        location_id: document.getElementById('resource-location-select').value,
        resource_type: document.getElementById('resource-type').value,
        name: document.getElementById('resource-name').value,
        quantity: document.getElementById('resource-quantity').value,
        unit: document.getElementById('resource-unit').value,
        min_threshold: document.getElementById('resource-min-threshold').value,
        cost_per_unit: document.getElementById('resource-cost').value,
        supplier: document.getElementById('resource-supplier').value,
        last_restocked: document.getElementById('resource-last-restocked').value || null
    };
    
    try {
        await ResourcesAPI.create(data);
        hideResourceForm();
        loadResources();
        alert('Resursa a fost adăugată cu succes!');
    } catch (error) {
        alert('Eroare la adăugarea resursei.');
    }
}

async function addQuantityToResource() {
    const name = document.getElementById('update-resource-name').value;
    const departmentName = document.getElementById('update-department-name').value;
    const amount = document.getElementById('update-quantity').value;
    
    try {
        await ResourcesAPI.addQuantity(name, departmentName, amount);
        loadResources();
        alert(`Cantitatea pentru resursa ${name} a fost mărită cu succes.`);
        document.getElementById('add-quantity-form').reset();
    } catch (error) {
        alert('Eroare la adăugarea cantității.');
    }
}

async function subtractQuantityFromResource() {
    const name = document.getElementById('subtract-resource-name').value;
    const departmentName = document.getElementById('subtract-department-name').value;
    const amount = document.getElementById('subtract-quantity').value;
    
    try {
        await ResourcesAPI.subtractQuantity(name, departmentName, amount);
        loadResources();
        alert(`Cantitatea pentru resursa ${name} a fost scăzută cu succes.`);
        document.getElementById('subtract-quantity-form').reset();
    } catch (error) {
        alert('Eroare la scăderea cantității.');
    }
}

async function deleteResource(name, locationId) {
    try {
        await ResourcesAPI.delete(name, locationId);
        loadResources();
        alert(`Resursa ${name} a fost ștearsă din locația ${locationId}.`);
    } catch (error) {
        alert('Eroare la ștergerea resursei.');
    }
}

function hideResourceForm() {
    document.getElementById('add-resource-form').style.display = 'none';
    document.getElementById('resource-form').reset();
}

/**
 * utilitar - dacă vrei să corelezi numele locației cu id-ul
 * poți construi un map la inițializare
 */
function getLocationNameById(id) {
    // exemplu static, poți încărca dintr-un array
    const locs = window.locationsCache || {};
    return locs[id] || null;
}
