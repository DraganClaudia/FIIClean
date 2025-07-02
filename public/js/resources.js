let currentUser = null;
        let userRole = null;
        let locationsCache = {};

        document.addEventListener('DOMContentLoaded', async function() {
            await loadUserRole();
            setupUI();
            await Promise.all([
                loadResources(),
                loadLocationsForSelect()
            ]);
            setupEventListeners();
        });

        async function loadUserRole() {
            try {
                if (checkAuthStatus()) {
                    const userData = await AuthAPI.getMe();
                    if (userData.success) {
                        currentUser = userData.user;
                        userRole = userData.user.role;
                    }
                }
            } catch (error) {
                console.error('Eroare la încărcarea utilizatorului:', error);
            }
        }

        function setupUI() {
            if (userRole === 'admin') {
                document.getElementById('add-resource-btn').style.display = 'inline-block';
            }
        }

        async function loadResources() {
            try {
                const resources = await ResourcesAPI.getAll();
                displayResources(resources);
            } catch (error) {
                document.getElementById('resources-list').innerHTML = '<p>Eroare la încărcarea resurselor: ' + error.message + '</p>';
            }
        }

        function displayResources(resources) {
            const container = document.getElementById('resources-list');
            
            if (!Array.isArray(resources) || resources.length === 0) {
                container.innerHTML = '<p>Nu există resurse înregistrate.</p>';
                return;
            }
            
            const html = resources.map(resource => `
                <div class="list-item" data-location="${resource.location_id}" style="border-left: 4px solid ${resource.quantity <= resource.min_threshold ? '#e74c3c' : '#27ae60'};">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <h3>${resource.name}</h3>
                        <div style="display: flex; gap: 0.5rem;">
                            ${resource.quantity <= resource.min_threshold ? '<span class="alert" style="background: #f8d7da; color: #721c24; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">⚠️ STOC REDUS</span>' : ''}
                            ${userRole === 'admin' ? `<button onclick="deleteResource('${resource.name}', ${resource.location_id})" class="btn btn-danger btn-small">🗑️</button>` : ''}
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div>
                            <p><strong>Tip:</strong> ${resource.resource_type}</p>
                            <p><strong>Locație:</strong> ${getLocationName(resource.location_id)}</p>
                        </div>
                        <div>
                            <p><strong>Cantitate:</strong> <span style="font-size: 1.2rem; font-weight: bold; color: ${resource.quantity <= resource.min_threshold ? '#e74c3c' : '#27ae60'};">${resource.quantity}</span> ${resource.unit || 'buc'}</p>
                            <p><strong>Prag minim:</strong> ${resource.min_threshold} ${resource.unit || 'buc'}</p>
                        </div>
                        <div>
                            ${resource.cost_per_unit ? `<p><strong>Cost unitar:</strong> ${parseFloat(resource.cost_per_unit).toFixed(2)} RON</p>` : ''}
                            ${resource.supplier ? `<p><strong>Furnizor:</strong> ${resource.supplier}</p>` : ''}
                        </div>
                        <div>
                            ${resource.last_restocked ? `<p><strong>Ultima reîncărcare:</strong> ${new Date(resource.last_restocked).toLocaleDateString('ro-RO')}</p>` : ''}
                            <p><strong>ID:</strong> ${resource.id}</p>
                        </div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }

        async function loadLocationsForSelect() {
            try {
                const locations = await LocationsAPI.getAll();
                
                locations.forEach(location => {
                    locationsCache[location.id] = location.name;
                });
                
                const selects = ['resource-location-select', 'location-filter'];
                
                selects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    
                    while (select.children.length > 1) {
                        select.removeChild(select.lastChild);
                    }
                    
                    locations.forEach(location => {
                        const option = document.createElement('option');
                        option.value = selectId === 'location-filter' ? location.name : location.id;
                        option.textContent = location.name;
                        select.appendChild(option);
                    });
                });
            } catch (error) {
                console.error('Eroare la încărcarea locațiilor:', error);
            }
        }

        function getLocationName(locationId) {
            return locationsCache[locationId] || `ID: ${locationId}`;
        }

        function setupEventListeners() {
            document.getElementById('add-resource-btn')?.addEventListener('click', showResourceForm);
            
            document.getElementById('resource-form').addEventListener('submit', addResource);
            
            document.getElementById('add-quantity-form').addEventListener('submit', addQuantityToResource);
            document.getElementById('subtract-quantity-form').addEventListener('submit', subtractQuantityFromResource);
            
            document.getElementById('location-filter').addEventListener('change', function() {
                filterResourcesByLocation(this.value);
            });
        }

        function showResourceForm() {
            document.getElementById('add-resource-section').style.display = 'block';
        }

        function hideResourceForm() {
            document.getElementById('add-resource-section').style.display = 'none';
            document.getElementById('resource-form').reset();
        }

        async function addResource(e) {
            e.preventDefault();
            
            const data = {
                location_id: document.getElementById('resource-location-select').value,
                resource_type: document.getElementById('resource-type').value,
                name: document.getElementById('resource-name').value,
                quantity: document.getElementById('resource-quantity').value,
                unit: document.getElementById('resource-unit').value || 'bucăți',
                min_threshold: document.getElementById('resource-min-threshold').value,
                cost_per_unit: document.getElementById('resource-cost').value || null,
                supplier: document.getElementById('resource-supplier').value || null,
                last_restocked: document.getElementById('resource-last-restocked').value || null
            };
            
            try {
                const result = await ResourcesAPI.create(data);
                
                if (result.success) {
                    alert('Resursa a fost adăugată cu succes!');
                    hideResourceForm();
                    loadResources();
                } else {
                    alert('Eroare la adăugarea resursei: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la adăugarea resursei: ' + error.message);
            }
        }

        async function addQuantityToResource(e) {
            e.preventDefault();
            
            const name = document.getElementById('update-resource-name').value;
            const departmentName = document.getElementById('update-department-name').value;
            const amount = document.getElementById('update-quantity').value;
            
            try {
                const result = await ResourcesAPI.addQuantity(name, departmentName, amount);
                
                if (result.success) {
                    alert(`Cantitatea pentru resursa "${name}" a fost mărită cu succes.`);
                    document.getElementById('add-quantity-form').reset();
                    loadResources();
                } else {
                    alert('Eroare la adăugarea cantității: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la adăugarea cantității: ' + error.message);
            }
        }

        async function subtractQuantityFromResource(e) {
            e.preventDefault();
            
            const name = document.getElementById('subtract-resource-name').value;
            const departmentName = document.getElementById('subtract-department-name').value;
            const amount = document.getElementById('subtract-quantity').value;
            
            try {
                const result = await ResourcesAPI.subtractQuantity(name, departmentName, amount);
                
                if (result.success) {
                    alert(`Cantitatea pentru resursa "${name}" a fost scăzută cu succes.`);
                    document.getElementById('subtract-quantity-form').reset();
                    loadResources();
                } else {
                    alert('Eroare la scăderea cantității: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la scăderea cantității: ' + error.message);
            }
        }

        async function deleteResource(name, locationId) {
            if (!confirm(`Ești sigur că vrei să ștergi resursa "${name}" din locația ${getLocationName(locationId)}?`)) {
                return;
            }
            
            try {
                const result = await ResourcesAPI.delete(name, locationId);
                
                if (result.success) {
                    alert(`Resursa "${name}" a fost ștearsă cu succes.`);
                    loadResources();
                } else {
                    alert('Eroare la ștergerea resursei: ' + (result.message || 'Eroare necunoscută'));
                }
            } catch (error) {
                alert('Eroare la ștergerea resursei: ' + error.message);
            }
        }

        function filterResourcesByLocation(locationName) {
            const resources = document.querySelectorAll('[data-location]');
            
            resources.forEach(resource => {
                const locationId = resource.dataset.location;
                const resourceLocationName = getLocationName(locationId);
                
                if (locationName === 'all' || resourceLocationName === locationName) {
                    resource.style.display = 'block';
                } else {
                    resource.style.display = 'none';
                }
            });
        }

        async function showLowStock() {
            try {
                const lowStockResources = await ResourcesAPI.getLowStock();
                
                if (lowStockResources.length === 0) {
                    alert('✅ Toate resursele au stoc suficient!');
                    return;
                }
                
                const message = `⚠️ ALERTA STOC REDUS!\n\nRursele cu stoc sub pragul minim:\n\n` +
                    lowStockResources.map(resource => 
                        `• ${resource.name} (${getLocationName(resource.location_id)}): ${resource.quantity} ${resource.unit || 'buc'} (min: ${resource.min_threshold})`
                    ).join('\n');
                
                alert(message);
                
                const allResources = document.querySelectorAll('.list-item');
                allResources.forEach(item => {
                    const hasLowStockAlert = item.querySelector('[style*="STOC REDUS"]');
                    item.style.display = hasLowStockAlert ? 'block' : 'none';
                });
                
            } catch (error) {
                alert('Eroare la verificarea stocului: ' + error.message);
            }
        }