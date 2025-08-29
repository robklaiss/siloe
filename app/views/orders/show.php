<!-- Order View Content -->
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Detalles del Pedido</h2>
                <div>
                    <a href="/orders" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">
                        Volver a Pedidos
                    </a>
                    <a href="/orders/<?= htmlspecialchars($order['id']) ?>/edit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Editar Pedido
                    </a>
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
            
            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow overflow-hidden p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Información del Pedido</h3>
                        <p class="mb-2"><span class="font-medium">ID del Pedido:</span> <?= htmlspecialchars($order['id']) ?></p>
                        <p class="mb-2"><span class="font-medium">Fecha:</span> <?= htmlspecialchars($order['order_date']) ?></p>
                        <p class="mb-2"><span class="font-medium">Estado:</span> 
                            <?php if ($order['status'] === 'pending'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pendiente
                                </span>
                            <?php elseif ($order['status'] === 'processing'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Procesando
                                </span>
                            <?php elseif ($order['status'] === 'completed'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Completado
                                </span>
                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Cancelado
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-2"><span class="font-medium">Solicitudes Especiales:</span> <?= htmlspecialchars($order['special_requests'] ?? 'Ninguna') ?></p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Información del Usuario</h3>
                        <p class="mb-2"><span class="font-medium">Nombre:</span> <?= htmlspecialchars($order['user_name'] ?? 'N/A') ?></p>
                        <p class="mb-2"><span class="font-medium">Correo:</span> <?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Artículos del Pedido</h3>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php if (!empty($order['items'])): ?>
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($item['menu_name']) ?>
                                                </p>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <p class="text-sm font-medium text-gray-900">
                                                    $<?= number_format($item['price'], 2) ?> × <?= (int)$item['quantity'] ?>
                                                </p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    $<?= number_format($item['item_total'], 2) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <!-- Order Total -->
                                <li class="p-4 bg-gray-50 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-medium text-gray-900">Total</span>
                                        <span class="text-lg font-bold text-gray-900">
                                            $<?= number_format($order['total_amount'], 2) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php else: ?>
                                <li class="p-4 text-center text-gray-500">
                                    No se encontraron artículos en este pedido.
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Acciones del Pedido</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($order['status'] !== 'processing' && $order['status'] !== 'completed'): ?>
                        <button type="button" onclick="updateOrderStatus('processing', '¿Está seguro que desea marcar este pedido como en proceso?')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Marcar como Procesando
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] !== 'completed'): ?>
                        <button type="button" onclick="updateOrderStatus('completed', '¿Está seguro que desea marcar este pedido como completado?')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Marcar como Completado
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] !== 'cancelled'): ?>
                        <button type="button" onclick="updateOrderStatus('cancelled', '¿Está seguro que desea cancelar este pedido?')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Cancelar Pedido
                        </button>
                        <?php endif; ?>
                        
                        <a href="/orders/<?= htmlspecialchars($order['id']) ?>/edit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.793.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Editar Pedido
                        </a>
                        
                        <button type="button" id="deleteOrderBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Eliminar Pedido
                        </button>
                    </div>
                </div>
            </div>
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-lg font-bold mb-4">Confirmar Eliminación</h3>
            <p class="mb-6">¿Está seguro que desea eliminar este pedido?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancelar</button>
                <form id="deleteForm" action="/orders/<?= htmlspecialchars($order['id']) ?>" method="POST">
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
        const deleteOrderBtn = document.getElementById('deleteOrderBtn');
        
        // Show modal when delete button is clicked
        if (deleteOrderBtn) {
            deleteOrderBtn.addEventListener('click', function() {
                deleteModal.classList.remove('hidden');
            });
        }
        
        // Cancel delete
        if (cancelDelete) {
            cancelDelete.addEventListener('click', function(e) {
                e.preventDefault();
                deleteModal.classList.add('hidden');
            });
        }
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                deleteModal.classList.add('hidden');
            }
        });
    });
    
    // Update order status via AJAX
    function updateOrderStatus(status, confirmMessage) {
        if (!confirm(confirmMessage)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', '<?= htmlspecialchars($_SESSION['csrf_token']) ?>');
        formData.append('status', status);
        
        fetch('/orders/<?= htmlspecialchars($order['id']) ?>/status', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload page to update status
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el estado del pedido');
        });
    }
    </script>
