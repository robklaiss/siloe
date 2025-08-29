<!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Editar Pedido #<?= htmlspecialchars($order['id']) ?></h1>
                    <p class="text-gray-600">Última actualización: <?= date('M j, Y g:i A', strtotime($order['updated_at'] ?? 'now')) ?></p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <a href="/orders/<?= htmlspecialchars($order['id']) ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-center">
                        Ver Pedido
                    </a>
                    <a href="/orders" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded text-center">
                        Volver a Pedidos
                    </a>
                </div>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">Error</p>
                            <p class="text-sm"><?= htmlspecialchars($_SESSION['error']) ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">Éxito</p>
                            <p class="text-sm"><?= htmlspecialchars($_SESSION['success']) ?></p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <form action="/orders/<?= htmlspecialchars($order['id']) ?>" method="POST" id="orderForm">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="p-6 space-y-6">
                        <!-- Customer Selection (Admin Only) -->
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                <select name="user_id" id="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= htmlspecialchars($user['id']) ?>" 
                                            <?= (isset($_SESSION['old']['user_id']) ? $_SESSION['old']['user_id'] == $user['id'] : $order['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado del Pedido</label>
                            <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" 
                                        <?= (isset($_SESSION['old']['status']) ? $_SESSION['old']['status'] === $value : $order['status'] === $value) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Special Requests -->
                        <div>
                            <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-1">Solicitudes Especiales</label>
                            <textarea name="special_requests" id="special_requests" rows="3" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Cualquier instrucción especial para este pedido..."><?= 
                                htmlspecialchars(isset($_SESSION['old']['special_requests']) ? 
                                    $_SESSION['old']['special_requests'] : 
                                    ($order['special_requests'] ?? '')) 
                            ?></textarea>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen del Pedido</h3>
                            <div class="space-y-2">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($item['menu_name']) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium"><?= (int)$item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></p>
                                            <p class="text-sm font-medium">$<?= number_format($item['quantity'] * $item['price'], 2) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="pt-2 mt-4 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <p class="text-base font-medium text-gray-900">Total</p>
                                        <p class="text-base font-bold">$<?= number_format($order['total_amount'] ?? 0, 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                            <a href="/orders/<?= htmlspecialchars($order['id']) ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Actualizar Pedido
                            </button>
                        </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
                    
        </div>

<!-- Include Select2 and scripts in the layout instead -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 if jQuery is available
    if (typeof $ !== 'undefined') {
        $('select').select2({
            theme: 'classic',
            width: '100%',
            placeholder: 'Seleccionar una opción',
            allowClear: true
        });
    }

    // Handle form submission with feedback
    const form = document.getElementById('orderForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                `;
            }
        });
    }

    // Handle status change visual feedback
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const statusColor = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'processing': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800'
            }[this.value] || 'bg-gray-100 text-gray-800';
            
            // Remove all status color classes
            this.className = this.className.split(' ')
                .filter(cls => !cls.startsWith('bg-') && !cls.startsWith('text-'))
                .join(' ');
            
            // Add the new status color
            this.classList.add(...statusColor.split(' '));
        });
        
        // Trigger change to set initial status color
        statusSelect.dispatchEvent(new Event('change'));
    }

    // Character counter for special requests
    const specialRequests = document.getElementById('special_requests');
    if (specialRequests) {
        const charCount = document.createElement('div');
        charCount.className = 'text-xs text-gray-500 mt-1 text-right';
        specialRequests.parentNode.insertBefore(charCount, specialRequests.nextSibling);
        
        function updateCharCount() {
            const maxLength = specialRequests.maxLength || 500;
            const remaining = maxLength - specialRequests.value.length;
            charCount.textContent = `${remaining} caracteres restantes`;
            charCount.className = `text-xs mt-1 text-right ${remaining < 50 ? 'text-red-500 font-medium' : 'text-gray-500'}`;
        }
        
        specialRequests.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count
    }
});
</script>
