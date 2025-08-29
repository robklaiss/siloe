<!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Crear Nuevo Ítem de Menú</h2>
                <a href="/menus" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Volver a Menús
                </a>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Create Menu Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="/menus" method="POST" id="menuForm">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-medium text-blue-800 mb-3">Detalles del Menú</h3>
                        
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium mb-2">Nombre del Menú</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-medium mb-2">Descripción</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-gray-700 font-medium mb-2">Precio Base</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2">$</span>
                                    <input type="number" id="price" name="price" step="0.01" min="0" required
                                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="available" class="block text-gray-700 font-medium mb-2">Estado</label>
                                <select id="available" name="available"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1">Disponible</option>
                                    <option value="0">No Disponible</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weekly Menu Items Section -->
                    <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                        <h3 class="text-lg font-medium text-green-800 mb-3">Agregar Ítems del Menú Semanal</h3>
                        <p class="text-sm text-gray-600 mb-4">Selecciona ítems del menú semanal para incluir en este menú diario:</p>
                        
                        <?php if (!empty($weeklyItems)): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($weeklyItems as $item): ?>
                                    <div class="flex items-center p-3 bg-white rounded border border-gray-200 hover:border-green-300">
                                        <input type="checkbox" id="weekly_item_<?= $item['id'] ?>" 
                                               name="weekly_items[]" value="<?= $item['id'] ?>"
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="weekly_item_<?= $item['id'] ?>" class="ml-3 block text-sm font-medium text-gray-700">
                                            <?= htmlspecialchars($item['name']) ?>
                                            <span class="block text-xs text-gray-500">
                                                <?= htmlspecialchars($item['description']) ?>
                                                <span class="font-semibold">$<?= number_format($item['price'], 2) ?></span>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 italic">No hay ítems del menú semanal disponibles. Por favor agrega algunos en el panel de administración.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Menu Item Categories -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Categorías del Menú</h3>
                        
                        <!-- Main Dishes -->
                        <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <h4 class="text-md font-medium text-green-800 mb-3">Platos Principales</h4>
                            <p class="text-sm text-green-600 mb-3">Selecciona platos principales para el menú de hoy:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="mainDishes"></div>
                        </div>
                        
                        <!-- Beverages -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="text-md font-medium text-blue-800 mb-3">Bebidas</h4>
                            <p class="text-sm text-blue-600 mb-3">Agrega bebidas para complementar tu menú:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="beverages">
                                <!-- Beverages will be loaded here -->
                            </div>
                            <button type="button" id="addBeverage" class="mt-3 inline-flex items-center px-3 py-1.5 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Agregar Bebida
                            </button>
                        </div>
                        
                        <!-- Desserts -->
                        <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <h4 class="text-md font-medium text-purple-800 mb-3">Postres</h4>
                            <p class="text-sm text-purple-600 mb-3">Agrega postres para completar tu menú:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="desserts"></div>
                            <button type="button" id="addDessert" class="mt-3 inline-flex items-center px-3 py-1.5 border border-purple-300 shadow-sm text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Agregar Postre
                            </button>
                        </div>
                    </div>
                    
                    <!-- Custom Items Section -->
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Ítems Personalizados del Menú</h3>
                        <p class="text-sm text-gray-600 mb-4">Agrega ítems personalizados específicos para este menú:</p>
                        <div id="customItems"></div>
                        <button type="button" id="addCustomItem" class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Agregar Ítem Personalizado
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-end">
                        <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Crear Ítem de Menú
                        </button>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
        </div>
        
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('menuForm');
            const submitBtn = document.getElementById('submitBtn');
            let isSubmitting = false;
            let customItemCounter = 0;
            let beverageCounter = 0;
            let dessertCounter = 0;
            
            // Load all available beverages on page load
            loadAvailableBeverages();
            // Load available desserts on page load
            loadAvailableDesserts();
            
            function loadAvailableBeverages() {
                const beveragesContainer = document.getElementById('beverages');
                
                // Mostrar estado de carga
                beveragesContainer.innerHTML = '<div class="text-center text-gray-500 py-4">Cargando bebidas...</div>';
                
                // Fetch beverages from API
                fetch('/api/beverages')
                    .then(response => response.json())
                    .then(data => {
                        beveragesContainer.innerHTML = ''; // Clear loading state
                        
                        if (data.success && data.beverages.length > 0) {
                            data.beverages.forEach((beverage, index) => {
                                const beverageHtml = `
                                    <label class="flex items-center p-3 border border-blue-300 rounded-lg hover:bg-blue-100 cursor-pointer">
                                        <input type="checkbox" name="weekly_items[]" value="${beverage.id}" class="mr-3 h-4 w-4 text-blue-600">
                                        <input type="hidden" name="beverage_names[weekly_${beverage.id}]" value="${beverage.name}">
                                        <input type="hidden" name="beverage_descriptions[weekly_${beverage.id}]" value="${beverage.description}">
                                        <input type="hidden" name="beverage_prices[weekly_${beverage.id}]" value="${beverage.price}">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">${beverage.name}</div>
                                            <div class="text-sm text-gray-600">${beverage.description}</div>
                                            <div class="text-sm font-medium text-blue-600">$${parseFloat(beverage.price).toFixed(2)}</div>
                                        </div>
                                    </label>
                                `;
                                beveragesContainer.insertAdjacentHTML('beforeend', beverageHtml);
                            });
                        } else {
                            beveragesContainer.innerHTML = '<div class="text-center text-gray-500 py-4 italic">No hay bebidas disponibles. Agrega algunas usando el botón "Agregar Bebida" abajo.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading beverages:', error);
                        beveragesContainer.innerHTML = '<div class="text-center text-red-500 py-4">Error al cargar las bebidas. Por favor actualiza la página.</div>';
                    });
            }
            
            function loadAvailableDesserts() {
                const dessertsContainer = document.getElementById('desserts');
                
                // Mostrar estado de carga
                dessertsContainer.innerHTML = '<div class="text-center text-gray-500 py-4">Cargando postres...</div>';
                
                fetch('/api/weekly-items?category=postres', { headers: { 'Accept': 'application/json' } })
                    .then(response => response.json())
                    .then(data => {
                        dessertsContainer.innerHTML = '';
                        const items = (data && data.items) ? data.items : [];
                        if (items.length > 0) {
                            items.forEach((item) => {
                                const price = parseFloat(item.price || 0);
                                const dessertHtml = `
                                    <label class="flex items-center p-3 border border-purple-300 rounded-lg hover:bg-purple-100 cursor-pointer">
                                        <input type="checkbox" name="weekly_items[]" value="${item.id}" class="mr-3 h-4 w-4 text-purple-600">
                                        <input type="hidden" name="dessert_names[weekly_${item.id}]" value="${item.name || ''}">
                                        <input type="hidden" name="dessert_descriptions[weekly_${item.id}]" value="${item.description || ''}">
                                        <input type="hidden" name="dessert_prices[weekly_${item.id}]" value="${price}">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">${item.name || ''}</div>
                                            <div class="text-sm text-gray-600">${item.description || ''}</div>
                                            <div class="text-sm font-medium text-purple-600">$${price.toFixed(2)}</div>
                                        </div>
                                    </label>
                                `;
                                dessertsContainer.insertAdjacentHTML('beforeend', dessertHtml);
                            });
                        } else {
                            dessertsContainer.innerHTML = '<div class="text-center text-gray-500 py-4 italic">No hay postres disponibles. Agrega algunos usando el botón "Agregar Postre" abajo.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading desserts:', error);
                        dessertsContainer.innerHTML = '<div class="text-center text-red-500 py-4">Error al cargar los postres. Por favor actualiza la página.</div>';
                    });
            }
            
            // Add custom beverage functionality
            document.getElementById('addBeverage').addEventListener('click', function() {
                const beverageHtml = `
                    <div class="beverage-item p-3 border border-blue-300 rounded-lg bg-blue-50" data-id="${beverageCounter}">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-medium text-blue-800">Bebida Personalizada</h5>
                            <button type="button" class="remove-beverage text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <input type="text" name="custom_beverages[${beverageCounter}][name]" placeholder="Nombre de la bebida" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm" required>
                            <input type="text" name="custom_beverages[${beverageCounter}][description]" placeholder="Descripción" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm">
                            <input type="number" name="custom_beverages[${beverageCounter}][price]" placeholder="Precio" step="0.01" min="0" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm" required>
                            <div class="flex items-center space-x-2 mt-2">
                                <button type="button" class="save-beverage-btn bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700" data-counter="${beverageCounter}">Guardar Bebida</button>
                                <button type="button" class="save-beverage-and-add bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700" data-counter="${beverageCounter}">Guardar y Agregar</button>
                                <label class="flex items-center text-sm text-gray-600">
                                    <input type="checkbox" class="save-to-weekly-checkbox mr-1" data-counter="${beverageCounter}"> Guardar en el menú semanal
                                </label>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('beverages').insertAdjacentHTML('beforeend', beverageHtml);
                beverageCounter++;
            });
            
            // Add custom dessert functionality
            document.getElementById('addDessert').addEventListener('click', function() {
                const dessertHtml = `
                    <div class="dessert-item p-3 border border-purple-300 rounded-lg bg-purple-50" data-id="${dessertCounter}">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-medium text-purple-800">Postre Personalizado</h5>
                            <button type="button" class="remove-dessert text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <input type="text" name="custom_desserts[${dessertCounter}][name]" placeholder="Nombre del postre" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm" required>
                            <input type="text" name="custom_desserts[${dessertCounter}][description]" placeholder="Descripción" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm">
                            <input type="number" name="custom_desserts[${dessertCounter}][price]" placeholder="Precio" step="0.01" min="0" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm" required>
                            <div class="flex items-center space-x-2 mt-2">
                                <button type="button" class="save-dessert-btn bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700" data-counter="${dessertCounter}">Guardar Postre</button>
                                <button type="button" class="save-dessert-and-add bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700" data-counter="${dessertCounter}">Guardar y Agregar</button>
                                <label class="flex items-center text-sm text-gray-600">
                                    <input type="checkbox" class="save-to-weekly-dessert mr-1" data-counter="${dessertCounter}"> Guardar en el menú semanal
                                </label>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('desserts').insertAdjacentHTML('beforeend', dessertHtml);
                dessertCounter++;
            });
            
            // Add custom item functionality
            document.getElementById('addCustomItem').addEventListener('click', function() {
                const customItemHtml = `
                    <div class="custom-item p-4 border border-gray-300 rounded-lg bg-gray-50 mb-3" data-id="${customItemCounter}">
                        <div class="flex justify-between items-start mb-3">
                            <h5 class="font-medium text-gray-800">Ítem Personalizado</h5>
                            <button type="button" class="remove-custom-item text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" name="custom_items[${customItemCounter}][name]" placeholder="Nombre del ítem" class="px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                            <input type="text" name="custom_items[${customItemCounter}][description]" placeholder="Descripción" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <input type="number" name="custom_items[${customItemCounter}][price]" placeholder="Precio" step="0.01" min="0" class="px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select name="custom_items[${customItemCounter}][category]" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="main">Plato Principal</option>
                                <option value="beverage">Bebida</option>
                                <option value="dessert">Postre</option>
                                <option value="side">Acompañamiento</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                    </div>
                `;
                document.getElementById('customItems').insertAdjacentHTML('beforeend', customItemHtml);
                customItemCounter++;
            });
            
            // Event delegation for remove buttons and save beverage buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-beverage')) {
                    e.target.closest('.beverage-item').remove();
                }
                if (e.target.closest('.remove-dessert')) {
                    e.target.closest('.dessert-item').remove();
                }
                if (e.target.closest('.remove-custom-item')) {
                    e.target.closest('.custom-item').remove();
                }
                if (e.target.closest('.save-beverage-btn')) {
                    saveBeverage(e.target.closest('.save-beverage-btn'));
                }
                if (e.target.closest('.save-beverage-and-add')) {
                    saveBeverage(e.target.closest('.save-beverage-and-add'));
                }
                if (e.target.closest('.save-dessert-btn')) {
                    saveDessert(e.target.closest('.save-dessert-btn'));
                }
                if (e.target.closest('.save-dessert-and-add')) {
                    saveDessert(e.target.closest('.save-dessert-and-add'));
                }
            });
            
            // Save beverage function
            function saveBeverage(saveButton) {
                const counter = saveButton.dataset.counter;
                const beverageItem = saveButton.closest('.beverage-item');
                
                // Get form data
                const name = beverageItem.querySelector(`input[name="custom_beverages[${counter}][name]"]`).value;
                const description = beverageItem.querySelector(`input[name="custom_beverages[${counter}][description]"]`).value;
                const price = beverageItem.querySelector(`input[name="custom_beverages[${counter}][price]"]`).value;
                const saveToWeekly = beverageItem.querySelector(`.save-to-weekly-checkbox[data-counter="${counter}"]`).checked;
                
                // Validate required fields
                if (!name || !price) {
                    showNotification('Por favor completa el nombre de la bebida y el precio', 'error');
                    return;
                }
                
                // Disable both buttons during save
                const primaryBtn = beverageItem.querySelector('.save-beverage-btn');
                const addBtn = beverageItem.querySelector('.save-beverage-and-add');
                if (primaryBtn) primaryBtn.disabled = true;
                if (addBtn) addBtn.disabled = true;
                saveButton.textContent = 'Guardando...';
                
                // Prepare data
                const formData = new FormData();
                formData.append('name', name);
                formData.append('description', description);
                formData.append('price', price);
                if (saveToWeekly) {
                    formData.append('save_to_weekly', '1');
                }
                
                // Send AJAX request
                fetch('/api/beverages/save', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Convert the custom beverage form into a selectable item
                        const selectableHtml = `
                            <label class="flex items-center p-3 border border-blue-300 rounded-lg hover:bg-blue-100 cursor-pointer">
                                <input type="checkbox" name="weekly_items[]" value="${data.weekly_item_id}" class="mr-3 h-4 w-4 text-blue-600" checked>
                                <input type="hidden" name="beverage_names[weekly_${data.weekly_item_id}]" value="${name}">
                                <input type="hidden" name="beverage_descriptions[weekly_${data.weekly_item_id}]" value="${description}">
                                <input type="hidden" name="beverage_prices[weekly_${data.weekly_item_id}]" value="${price}">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">${name}</div>
                                    <div class="text-sm text-gray-600">${description}</div>
                                    <div class="text-sm font-medium text-blue-600">$${parseFloat(price).toFixed(2)}</div>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full ml-2">Guardado</span>
                            </label>
                        `;
                        
                        // Replace the custom form with the selectable item
                        beverageItem.outerHTML = selectableHtml;
                        
                        // Show success message
                        showNotification(data.message, 'success');
                        
                        // Note: We don't reload the beverage list here because the beverage
                        // is already converted to a selectable item above. Reloading would
                        // remove the converted item and cause confusion.
                        
                    } else {
                        // Rehabilitar botones en caso de error
                        if (primaryBtn) { primaryBtn.disabled = false; primaryBtn.textContent = 'Guardar Bebida'; }
                        if (addBtn) { addBtn.disabled = false; addBtn.textContent = 'Guardar y Agregar'; }
                        showNotification(data.message || 'Error al guardar la bebida', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving beverage:', error);
                    // Rehabilitar botones en caso de error
                    if (primaryBtn) { primaryBtn.disabled = false; primaryBtn.textContent = 'Guardar Bebida'; }
                if (addBtn) { addBtn.disabled = false; addBtn.textContent = 'Guardar y Agregar'; }
                showNotification('Error al guardar la bebida', 'error');
            });
        }
        
        // Notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Save dessert function (global)
        function saveDessert(saveButton) {
            const counter = saveButton.dataset.counter;
            const dessertItem = saveButton.closest('.dessert-item');
            
            // Get form data
            const name = dessertItem.querySelector(`input[name="custom_desserts[${counter}][name]"]`).value;
            const description = dessertItem.querySelector(`input[name="custom_desserts[${counter}][description]"]`).value;
            const price = dessertItem.querySelector(`input[name="custom_desserts[${counter}][price]"]`).value;
            const saveToWeekly = dessertItem.querySelector(`.save-to-weekly-dessert[data-counter="${counter}"]`).checked;
            
            // Validate required fields
            if (!name || !price) {
                showNotification('Por favor completa el nombre del postre y el precio', 'error');
                return;
            }
            
            // Disable both buttons during save
            const primaryBtn = dessertItem.querySelector('.save-dessert-btn');
            const addBtn = dessertItem.querySelector('.save-dessert-and-add');
            if (primaryBtn) primaryBtn.disabled = true;
            if (addBtn) addBtn.disabled = true;
            saveButton.textContent = 'Guardando...';
            
            // Prepare data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', description);
            formData.append('price', price);
            if (saveToWeekly) {
                formData.append('save_to_weekly', '1');
            }
            
            // Send AJAX request
            fetch('/api/desserts/save', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Convert the custom dessert form into a selectable item
                    const selectableHtml = `
                        <label class="flex items-center p-3 border border-purple-300 rounded-lg hover:bg-purple-100 cursor-pointer">
                            <input type="checkbox" name="weekly_items[]" value="${data.weekly_item_id}" class="mr-3 h-4 w-4 text-purple-600" checked>
                            <input type="hidden" name="dessert_names[weekly_${data.weekly_item_id}]" value="${name}">
                            <input type="hidden" name="dessert_descriptions[weekly_${data.weekly_item_id}]" value="${description}">
                            <input type="hidden" name="dessert_prices[weekly_${data.weekly_item_id}]" value="${price}">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${name}</div>
                                <div class="text-sm text-gray-600">${description}</div>
                                <div class="text-sm font-medium text-purple-600">$${parseFloat(price).toFixed(2)}</div>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full ml-2">Guardado</span>
                        </label>
                    `;
                    
                    // Replace the custom form with the selectable item
                    dessertItem.outerHTML = selectableHtml;
                    
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Note: We don't reload the dessert list here because the dessert
                    // is already converted to a selectable item above. Reloading would
                    // remove the converted item and cause confusion.
                    
                } else {
                    // Rehabilitar botones en caso de error
                    if (primaryBtn) { primaryBtn.disabled = false; primaryBtn.textContent = 'Guardar Postre'; }
                    if (addBtn) { addBtn.disabled = false; addBtn.textContent = 'Guardar y Agregar'; }
                    showNotification(data.message || 'Error al guardar el postre', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving dessert:', error);
                // Rehabilitar botones en caso de error
                if (primaryBtn) { primaryBtn.disabled = false; primaryBtn.textContent = 'Guardar Postre'; }
                if (addBtn) { addBtn.disabled = false; addBtn.textContent = 'Guardar y Agregar'; }
                showNotification('Error al guardar el postre', 'error');
            });
        }
        
        // Form submission handling
        form.addEventListener('submit', function(e) {
            // Prevent double submission
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            // Mark as submitting
            isSubmitting = true;
            
            // Disable the submit button
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.textContent = 'Creando Menú...';
            
            // Add a hidden field to indicate this is a controlled submission
            const controlField = document.createElement('input');
            controlField.type = 'hidden';
            controlField.name = 'controlled_submit';
            controlField.value = 'true';
            form.appendChild(controlField);
        });
    });
</script>
</body>
</html>
