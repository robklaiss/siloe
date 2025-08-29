            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Editar Ítem del Menú</h2>
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
            
            <!-- Edit Menu Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="/menus/<?= htmlspecialchars($menu['id']) ?>" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-medium text-blue-800 mb-3">Detalles del Menú</h3>
                        
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium mb-2">Nombre del Menú</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($menu['name']) ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-medium mb-2">Descripción</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($menu['description']) ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-gray-700 font-medium mb-2">Precio Base</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2">₲</span>
                                    <input type="number" id="price" name="price" step="1" min="0" value="<?= htmlspecialchars($menu['price']) ?>" required
                                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Ingrese el precio total incluyendo 10% I.V.A.</p>
                            </div>
                            
                            <div>
                                <label for="available" class="block text-gray-700 font-medium mb-2">Estado</label>
                                <select id="available" name="available"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1" <?= $menu['available'] ? 'selected' : '' ?>>Disponible</option>
                                    <option value="0" <?= !$menu['available'] ? 'selected' : '' ?>>No Disponible</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Item Categories -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Categorías del Menú</h3>
                        
                        <!-- Main Dishes -->
                        <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <h4 class="text-md font-medium text-green-800 mb-3">Platos Principales</h4>
                            <p class="text-sm text-green-600 mb-3">Selecciona platos principales para este menú:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="mainDishes">
                                <!-- Platos principales se cargarán dinámicamente vía JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Merienda -->
                        <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <h4 class="text-md font-medium text-yellow-800 mb-3">Merienda</h4>
                            <p class="text-sm text-yellow-700 mb-3">Selecciona meriendas para este menú:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="merienda">
                                <!-- Meriendas se cargarán dinámicamente vía JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Beverages -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="text-md font-medium text-blue-800 mb-3">Bebidas</h4>
                            <p class="text-sm text-blue-600 mb-3">Agrega bebidas para complementar tu menú:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="beverages">
                                <!-- Beverages will be loaded here via JavaScript -->
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="desserts">
                                <!-- Postres se cargarán dinámicamente vía JavaScript -->
                            </div>
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
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Actualizar Ítem del Menú
                        </button>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            let isSubmitting = false;
            let customItemCounter = 0;
            let beverageCounter = 0;
            let dessertCounter = 0;
            
            // Pre-selecciones existentes para este menú
            const preselectedWeeklyItems = new Set(<?= json_encode($currentWeeklyItems ?? []) ?>);

            // Utilidad para renderizar un item con clases y soporte para bebidas
            function renderWeeklyItem(item, options = {}) {
                const {
                    borderClass = 'border-gray-300',
                    hoverClass = 'hover:bg-gray-100',
                    textClass = 'text-gray-600',
                    priceTextClass = 'text-gray-700',
                    includeBeverageHidden = false
                } = options;
                const checkedAttr = preselectedWeeklyItems.has(Number(item.id)) ? 'checked' : '';
                const beverageHidden = includeBeverageHidden ? `
                    <input type="hidden" name="beverage_names[weekly_${item.id}]" value="${item.name}">
                    <input type="hidden" name="beverage_descriptions[weekly_${item.id}]" value="${item.description || ''}">
                    <input type="hidden" name="beverage_prices[weekly_${item.id}]" value="${item.price}">` : '';
                return `
                    <label class="flex items-center p-3 border ${borderClass} rounded-lg ${hoverClass} cursor-pointer">
                        <input type="checkbox" name="weekly_items[]" value="${item.id}" class="mr-3 h-4 w-4" ${checkedAttr}>
                        ${beverageHidden}
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">${item.name}</div>
                            <div class="text-sm ${textClass}">${item.description || ''}</div>
                            <div class="text-sm font-medium ${priceTextClass}">$${parseFloat(item.price).toFixed(2)}</div>
                        </div>
                    </label>
                `;
            }

            // Cargar una categoría al contenedor
            function loadCategory(category, containerId, style) {
                const container = document.getElementById(containerId);
                if (!container) return;
                const loadingText = {
                    almuerzo: 'Cargando platos principales...',
                    merienda: 'Cargando meriendas...',
                    bebidas: 'Cargando bebidas...',
                    postres: 'Cargando postres...'
                }[category] || 'Cargando...';
                container.innerHTML = `<div class="text-center text-gray-500 py-4">${loadingText}</div>`;

                fetch(`/api/weekly-items?category=${encodeURIComponent(category)}`)
                    .then(r => r.json())
                    .then(data => {
                        container.innerHTML = '';
                        if (data && data.success && Array.isArray(data.items) && data.items.length) {
                            data.items.forEach(item => {
                                const html = renderWeeklyItem(item, {
                                    borderClass: style.borderClass,
                                    hoverClass: style.hoverClass,
                                    textClass: style.textClass,
                                    priceTextClass: style.priceTextClass,
                                    includeBeverageHidden: category === 'bebidas'
                                });
                                container.insertAdjacentHTML('beforeend', html);
                            });
                        } else {
                            const emptyText = {
                                almuerzo: 'No hay platos principales disponibles.',
                                merienda: 'No hay meriendas disponibles.',
                                bebidas: 'No hay bebidas disponibles. Agrega algunas usando el botón "Agregar Bebida" abajo.',
                                postres: 'No hay postres disponibles.'
                            }[category] || 'No hay ítems disponibles.';
                            container.innerHTML = `<div class="text-center text-gray-500 py-4 italic">${emptyText}</div>`;
                        }
                    })
                    .catch(err => {
                        console.error(`Error al cargar categoría ${category}:`, err);
                        container.innerHTML = `<div class="text-center text-red-500 py-4">Error al cargar. Intenta nuevamente.</div>`;
                    });
            }

            // Cargar categorías
            loadCategory('almuerzo', 'mainDishes', {
                borderClass: 'border-green-300',
                hoverClass: 'hover:bg-green-100',
                textClass: 'text-green-700',
                priceTextClass: 'text-green-600'
            });
            loadCategory('merienda', 'merienda', {
                borderClass: 'border-yellow-300',
                hoverClass: 'hover:bg-yellow-100',
                textClass: 'text-yellow-700',
                priceTextClass: 'text-yellow-700'
            });
            loadCategory('bebidas', 'beverages', {
                borderClass: 'border-blue-300',
                hoverClass: 'hover:bg-blue-100',
                textClass: 'text-blue-700',
                priceTextClass: 'text-blue-600'
            });
            loadCategory('postres', 'desserts', {
                borderClass: 'border-purple-300',
                hoverClass: 'hover:bg-purple-100',
                textClass: 'text-purple-700',
                priceTextClass: 'text-purple-600'
            });
            
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
                            <input type="text" name="custom_beverages[${beverageCounter}][name]" placeholder="Nombre de la bebida" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm beverage-name" required>
                            <input type="text" name="custom_beverages[${beverageCounter}][description]" placeholder="Descripción" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm beverage-description">
                            <input type="number" name="custom_beverages[${beverageCounter}][price]" placeholder="Precio" step="0.01" min="0" class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm beverage-price" required>
                            <div class="flex gap-2 mt-3">
                                <button type="button" class="save-beverage flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Guardar Bebida
                                </button>
                                <button type="button" class="save-beverage-add flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Guardar y Agregar
                                </button>
                                <div class="flex items-center">
                                    <input type="checkbox" class="save-to-weekly mr-2" id="save_weekly_${beverageCounter}">
                                    <label for="save_weekly_${beverageCounter}" class="text-xs text-blue-700">Guardar en el menú semanal</label>
                                </div>
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
                            <input type="text" name="custom_desserts[${dessertCounter}][name]" placeholder="Nombre del postre" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm dessert-name" required>
                            <input type="text" name="custom_desserts[${dessertCounter}][description]" placeholder="Descripción" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm dessert-description">
                            <input type="number" name="custom_desserts[${dessertCounter}][price]" placeholder="Precio" step="0.01" min="0" class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm dessert-price" required>
                            <div class="flex gap-2 mt-3">
                                <button type="button" class="save-dessert flex-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Guardar Postre
                                </button>
                                <button type="button" class="save-dessert-add flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Guardar y Agregar
                                </button>
                                <div class="flex items-center">
                                    <input type="checkbox" class="save-to-weekly-dessert mr-2" id="save_weekly_dessert_${dessertCounter}">
                                    <label for="save_weekly_dessert_${dessertCounter}" class="text-xs text-purple-700">Guardar en el menú semanal</label>
                                </div>
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
            
            // Event delegation for remove and save buttons
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
                if (e.target.closest('.save-beverage')) {
                    saveBeverage(e.target.closest('.beverage-item'));
                }
                if (e.target.closest('.save-beverage-add')) {
                    saveBeverage(e.target.closest('.beverage-item'));
                }
                if (e.target.closest('.save-dessert')) {
                    saveDessert(e.target.closest('.dessert-item'));
                }
                if (e.target.closest('.save-dessert-add')) {
                    saveDessert(e.target.closest('.dessert-item'));
                }
            });
            
            // Save beverage functionality
            function saveBeverage(beverageItem) {
                const nameInput = beverageItem.querySelector('.beverage-name');
                const descriptionInput = beverageItem.querySelector('.beverage-description');
                const priceInput = beverageItem.querySelector('.beverage-price');
                const saveToWeekly = beverageItem.querySelector('.save-to-weekly');
                const saveButton = beverageItem.querySelector('.save-beverage');
                const addButton = beverageItem.querySelector('.save-beverage-add');
                
                const name = nameInput.value.trim();
                const description = descriptionInput.value.trim();
                const price = parseFloat(priceInput.value);
                
                if (!name || !price || price <= 0) {
                    showNotification('Por favor completa el nombre de la bebida y un precio válido.', 'error');
                    return;
                }
                
                // Disable the save button and show loading state
                // Disable both buttons and show loading on the main save button
                saveButton.disabled = true;
                if (addButton) addButton.disabled = true;
                saveButton.innerHTML = `
                    <svg class="w-4 h-4 inline mr-1 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2H4zm0 2h12v11H4V4z" clip-rule="evenodd"></path>
                    </svg>
                    Guardando...
                `;
                
                // Create the beverage data
                const beverageData = {
                    name: name,
                    description: description,
                    price: price,
                    save_to_weekly: saveToWeekly.checked,
                    menu_id: <?= $menu['id'] ?> // Add current menu ID to link the beverage
                };
                
                // Make AJAX call to save beverage
                fetch('/api/beverages/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(beverageData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Convert the custom beverage form to a saved beverage
                        const weeklyId = data.weekly_item_id;
                        const savedBeverageHtml = `
                            <label class="flex items-center p-3 border border-blue-300 rounded-lg hover:bg-blue-100 cursor-pointer bg-green-50">
                                <input type="checkbox" name="weekly_items[]" value="${weeklyId}" class="mr-3 h-4 w-4 text-blue-600" checked>
                                <input type="hidden" name="beverage_names[weekly_${weeklyId}]" value="${name}">
                                <input type="hidden" name="beverage_descriptions[weekly_${weeklyId}]" value="${description}">
                                <input type="hidden" name="beverage_prices[weekly_${weeklyId}]" value="${price}">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">${name} <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">Guardado</span></div>
                                    <div class="text-sm text-gray-600">${description}</div>
                                    <div class="text-sm font-medium text-blue-600">$${price.toFixed(2)}</div>
                                </div>
                            </label>
                        `;
                        
                        // Replace the form with the saved beverage
                        beverageItem.outerHTML = savedBeverageHtml;
                        
                        // Show success message
                        showNotification(data.message, 'success');
                        
                        // Note: We don't reload the beverage list here because the beverage
                        // is already converted to a selectable item above. Reloading would
                        // remove the converted item and cause confusion.
                        
                    } else {
                        // Re-enable button on error
                        saveButton.disabled = false;
                        if (addButton) addButton.disabled = false;
                        saveButton.innerHTML = `
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Guardar Bebida
                        `;
                        showNotification(data.message || 'No se pudo guardar la bebida', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving beverage:', error);
                    // Rehabilitar botón en caso de error
                    saveButton.disabled = false;
                    if (addButton) addButton.disabled = false;
                    saveButton.innerHTML = `
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Guardar Bebida
                    `;
                    showNotification('Ocurrió un error de red al guardar la bebida', 'error');
                });
            }
            
            // Notification function
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    type === 'success' ? 'bg-green-500 text-white' : 
                    type === 'error' ? 'bg-red-500 text-white' : 
                    'bg-blue-500 text-white'
                }`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            // Save dessert functionality
            function saveDessert(dessertItem) {
                const nameInput = dessertItem.querySelector('.dessert-name');
                const descriptionInput = dessertItem.querySelector('.dessert-description');
                const priceInput = dessertItem.querySelector('.dessert-price');
                const saveToWeekly = dessertItem.querySelector('.save-to-weekly-dessert');
                const saveButton = dessertItem.querySelector('.save-dessert');
                const addButton = dessertItem.querySelector('.save-dessert-add');
                
                const name = nameInput.value.trim();
                const description = descriptionInput.value.trim();
                const price = parseFloat(priceInput.value);
                
                if (!name || !price || price <= 0) {
                    showNotification('Por favor completa el nombre del postre y un precio válido.', 'error');
                    return;
                }
                
                // Disable the save button and show loading state
                saveButton.disabled = true;
                saveButton.innerHTML = `
                    <svg class="w-4 h-4 inline mr-1 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2H4zm0 2h12v11H4V4z" clip-rule="evenodd"></path>
                    </svg>
                    Guardando...
                `;
                
                const dessertData = {
                    name: name,
                    description: description,
                    price: price,
                    save_to_weekly: !!(saveToWeekly && saveToWeekly.checked),
                    menu_id: <?= $menu['id'] ?>
                };
                
                fetch('/api/desserts/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dessertData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const weeklyId = data.weekly_item_id;
                        const savedDessertHtml = `
                            <label class="flex items-center p-3 border border-purple-300 rounded-lg hover:bg-purple-100 cursor-pointer bg-green-50">
                                <input type="checkbox" name="weekly_items[]" value="${weeklyId}" class="mr-3 h-4 w-4 text-purple-600" checked>
                                <input type="hidden" name="dessert_names[weekly_${weeklyId}]" value="${name}">
                                <input type="hidden" name="dessert_descriptions[weekly_${weeklyId}]" value="${description}">
                                <input type="hidden" name="dessert_prices[weekly_${weeklyId}]" value="${price}">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">${name} <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">Guardado</span></div>
                                    <div class="text-sm text-gray-600">${description}</div>
                                    <div class="text-sm font-medium text-purple-600">$${price.toFixed(2)}</div>
                                </div>
                            </label>
                        `;
                        dessertItem.outerHTML = savedDessertHtml;
                        showNotification(data.message, 'success');
                    } else {
                        saveButton.disabled = false;
                        if (addButton) addButton.disabled = false;
                        saveButton.innerHTML = `
                            <svg class=\"w-4 h-4 inline mr-1\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                <path fill-rule=\"evenodd\" d=\"M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\" clip-rule=\"evenodd\"></path>
                            </svg>
                            Guardar Postre
                        `;
                        showNotification(data.message || 'No se pudo guardar el postre', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving dessert:', error);
                    saveButton.disabled = false;
                    if (addButton) addButton.disabled = false;
                    saveButton.innerHTML = `
                        <svg class=\"w-4 h-4 inline mr-1\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                            <path fill-rule=\"evenodd\" d=\"M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\" clip-rule=\"evenodd\"></path>
                        </svg>
                        Guardar Postre
                    `;
                    showNotification('Ocurrió un error de red al guardar el postre', 'error');
                });
            }
            
            // Form submission handling
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Prevent double submission
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Mark as submitting
                    isSubmitting = true;
                    
                    // Disable the submit button
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        submitBtn.textContent = 'Actualizando Menú...';
                    }
                    
                    // Add a hidden field to indicate this is a controlled submission
                    const controlField = document.createElement('input');
                    controlField.type = 'hidden';
                    controlField.name = 'controlled_submit';
                    controlField.value = 'true';
                    form.appendChild(controlField);
                });
            }
        });
    </script>
