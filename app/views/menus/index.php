<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Menu Management</h1>
        <div class="flex items-center space-x-2">
            <a href="/menus/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add Menu Item
            </a>
            <button id="expandAll" type="button" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                Expand All
            </button>
            <button id="collapseAll" type="button" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                Collapse All
            </button>
            <label class="ml-2 inline-flex items-center space-x-1 text-sm text-gray-700">
                <input id="accordionMode" type="checkbox" class="form-checkbox" checked>
                <span>Accordion Mode</span>
            </label>
        </div>
    </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Debug: Show what data we're receiving -->
            <?php if (isset($_GET['debug'])): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    <strong>Información de Depuración:</strong><br>
                    Cantidad de menús: <?= count($menus ?? []) ?><br>
                    <?php if (!empty($menus)): ?>
                        Claves del primer menú: <?= implode(', ', array_keys($menus[0])) ?><br>
                        Primer menú tiene ítems incluidos: <?= isset($menus[0]['bundled_items']) ? 'SÍ' : 'NO' ?><br>
                        Primer menú tiene precio total: <?= isset($menus[0]['total_price']) ? 'SÍ' : 'NO' ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Menu Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles del Menú</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ítems Incluidos</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Total (PYG)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponible</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($menus)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron menús</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menus as $menu): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($menu['id']) ?></td>
                                    
                                    <!-- Menu Details -->
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($menu['name']) ?></div>
                                        <div class="text-gray-500 text-xs mt-1"><?= htmlspecialchars($menu['description']) ?></div>
                                        <div class="text-gray-400 text-xs mt-1">
                                            Precio Base: ₲<?= htmlspecialchars(number_format($menu['price'], 0, ',', '.')) ?>
                                        </div>
                                        <?php if (!empty($menu['date'])): ?>
                                            <div class="text-gray-400 text-xs">
                                                Fecha: <?= htmlspecialchars(date('Y-m-d', strtotime($menu['date']))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Ítems Incluidos (colapsables por categorías) -->
                                    <td class="px-6 py-4 text-sm">
                                        <?php if (!empty($menu['bundled_items'])): ?>
                                            <div class="space-y-2">
                                                <?php if (!empty($menu['bundled_items']['main_dishes'])): ?>
                                                    <details class="group category-details" data-menu-id="<?= htmlspecialchars($menu['id']) ?>">
                                                        <summary class="cursor-pointer inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-800 hover:bg-green-100">
                                                            Platos Principales (<?= count($menu['bundled_items']['main_dishes']) ?>)
                                                        </summary>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['main_dishes'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>

                                                <?php if (!empty($menu['bundled_items']['beverages'])): ?>
                                                    <details class="group category-details" data-menu-id="<?= htmlspecialchars($menu['id']) ?>">
                                                        <summary class="cursor-pointer inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-800 hover:bg-blue-100">
                                                            Bebidas (<?= count($menu['bundled_items']['beverages']) ?>)
                                                        </summary>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['beverages'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>

                                                <?php if (!empty($menu['bundled_items']['desserts'])): ?>
                                                    <details class="group category-details" data-menu-id="<?= htmlspecialchars($menu['id']) ?>">
                                                        <summary class="cursor-pointer inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-50 text-purple-800 hover:bg-purple-100">
                                                            Postres (<?= count($menu['bundled_items']['desserts']) ?>)
                                                        </summary>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['desserts'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>

                                                <?php if (!empty($menu['bundled_items']['other'])): ?>
                                                    <details class="group category-details" data-menu-id="<?= htmlspecialchars($menu['id']) ?>">
                                                        <summary class="cursor-pointer inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-50 text-gray-800 hover:bg-gray-100">
                                                            Otros (<?= count($menu['bundled_items']['other']) ?>)
                                                        </summary>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['other'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">Sin ítems incluidos</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Total Price -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-semibold text-gray-900">
                                            ₲<?= htmlspecialchars(number_format($menu['total_price'], 0, ',', '.')) ?>
                                        </div>
                                        <div class="text-xs text-gray-400">Incl. 10% I.V.A.</div>
                                        <?php if ($menu['total_price'] > $menu['price']): ?>
                                            <div class="text-xs text-green-600 mt-1">
                                                +₲<?= htmlspecialchars(number_format($menu['total_price'] - $menu['price'], 0, ',', '.')) ?> de ítems incluidos
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Available Status -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($menu['available']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Sí
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                No
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/menus/<?= htmlspecialchars($menu['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <button type="button" class="text-red-600 hover:text-red-900 delete-btn" data-id="<?= htmlspecialchars($menu['id']) ?>">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-lg font-bold mb-4">Confirmar Eliminación</h3>
            <p class="mb-6">¿Está seguro de que desea eliminar este menú?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancelar</button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        const deleteForm = document.getElementById('deleteForm');
        const cancelDelete = document.getElementById('cancelDelete');
        const expandAllBtn = document.getElementById('expandAll');
        const collapseAllBtn = document.getElementById('collapseAll');
        const accordionMode = document.getElementById('accordionMode');
        
        // Get all delete buttons
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        // Add click event listener to each delete button
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const menuId = this.getAttribute('data-id');
                deleteForm.action = '/menus/' + menuId;
                deleteModal.classList.remove('hidden');
            });
        });
        
        // Cancel delete
        cancelDelete.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
        });
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });

        // Accordion behavior for category details within each menu row
        const detailsByMenu = {};
        const allDetails = Array.from(document.querySelectorAll('details.category-details'));
        allDetails.forEach((d) => {
            const menuId = d.getAttribute('data-menu-id') || 'global';
            if (!detailsByMenu[menuId]) detailsByMenu[menuId] = [];
            detailsByMenu[menuId].push(d);

            d.addEventListener('toggle', () => {
                if (!accordionMode || !accordionMode.checked) return;
                if (!d.open) return; // Only act when a section opens
                // Close siblings within the same menu row
                detailsByMenu[menuId].forEach((other) => {
                    if (other !== d) other.open = false;
                });
            });
        });

        // Expand/Collapse all handlers
        if (expandAllBtn) {
            expandAllBtn.addEventListener('click', () => {
                allDetails.forEach((d) => { d.open = true; });
            });
        }
        if (collapseAllBtn) {
            collapseAllBtn.addEventListener('click', () => {
                allDetails.forEach((d) => { d.open = false; });
            });
        }
    
    });
    </script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
