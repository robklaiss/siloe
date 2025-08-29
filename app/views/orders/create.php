<!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Crear Nuevo Pedido</h2>
                <a href="/orders" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Volver a Pedidos
                </a>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php 
                    $showSuggestion = strpos($_SESSION['error'], '¿Le gustaría pedir para') !== false;
                    unset($_SESSION['error']); 
                    ?>
                </div>
                
                <?php if (isset($suggestedMenu) && $showSuggestion): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    <p>¿Le gustaría pedir para <?= htmlspecialchars($suggestedMenu['name']) ?> el <?= date('d M, Y', strtotime($suggestedMenu['date'])) ?> en su lugar?</p>
                    <div class="mt-2">
                        <button type="button" 
                                onclick="selectSuggestedMenu(<?= htmlspecialchars($suggestedMenu['id']) ?>)" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            Sí, pedir para el <?= date('d M', strtotime($suggestedMenu['date'])) ?>
                        </button>
                        <a href="/orders/create" class="ml-2 text-blue-500 hover:text-blue-700 text-sm">No, gracias</a>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Create Order Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="/orders" method="POST" id="orderForm">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-4">
                        <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Usuario</label>
                        <select name="user_id" id="user_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Seleccionar Usuario</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['id']) ?>" <?= (isset($_SESSION['old']['user_id']) && $_SESSION['old']['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="menu_id" class="block text-gray-700 text-sm font-bold mb-2">Elemento del Menú</label>
                        <select name="menu_id" id="menu_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Seleccionar Elemento del Menú</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= htmlspecialchars($menu['id']) ?>" 
                                        data-price="<?= htmlspecialchars($menu['price']) ?>"
                                        <?= (isset($_SESSION['old']['menu_id']) && $_SESSION['old']['menu_id'] == $menu['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($menu['name']) ?> ($<?= htmlspecialchars(number_format($menu['price'], 2)) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Cantidad</label>
                        <input type="number" name="quantity" id="quantity" min="1" value="<?= isset($_SESSION['old']['quantity']) ? htmlspecialchars($_SESSION['old']['quantity']) : '1' ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="total" class="block text-gray-700 text-sm font-bold mb-2">Total ($)</label>
                        <input type="text" name="total" id="total" readonly 
                               value="<?= isset($_SESSION['old']['total']) ? htmlspecialchars($_SESSION['old']['total']) : '0.00' ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-500 leading-tight focus:outline-none focus:shadow-outline bg-gray-100">
                    </div>
                    
                    <div class="mb-6">
                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                        <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="pending" <?= (isset($_SESSION['old']['status']) && $_SESSION['old']['status'] === 'pending') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="completed" <?= (isset($_SESSION['old']['status']) && $_SESSION['old']['status'] === 'completed') ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelled" <?= (isset($_SESSION['old']['status']) && $_SESSION['old']['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-end">
                        <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Crear Pedido
                        </button>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
        </div>
        </div>

<script>
function selectSuggestedMenu(menuId) {
    // Set the selected menu
    document.getElementById('menu_id').value = menuId;
    // Trigger change event to update the total
    const event = new Event('change');
    document.getElementById('menu_id').dispatchEvent(event);
    // Submit the form
    document.getElementById('orderForm').submit();
}

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const menuSelect = document.getElementById('menu_id');
    const quantityInput = document.getElementById('quantity');
    const totalInput = document.getElementById('total');
    const orderForm = document.getElementById('orderForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Store original button text
    const originalBtnText = submitBtn.textContent;
    
    // Completely prevent duplicate submissions
    orderForm.addEventListener('submit', function(e) {
        // Immediately prevent default to take control
        e.preventDefault();
        
        // Disable the button immediately
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        submitBtn.textContent = 'Procesando...';
        
        // Use a small timeout to ensure UI updates before submission
        setTimeout(function() {
            // Create a hidden field with a unique submission ID
            const uniqueId = new Date().getTime() + Math.random().toString(36).substring(2, 15);
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'submission_id';
            hiddenField.value = uniqueId;
            orderForm.appendChild(hiddenField);
            
            // Store submission ID in localStorage to prevent duplicates
            localStorage.setItem('last_order_submission', uniqueId);
            
            // Submit the form programmatically
            orderForm.submit();
        }, 100);
    });
    
    // Reset form state if user navigates back
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache (user clicked back)
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.textContent = originalBtnText;
        }
    });
    
    function calculateTotal() {
        const selectedOption = menuSelect.options[menuSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price);
            const quantity = parseInt(quantityInput.value) || 0;
            const total = price * quantity;
            totalInput.value = total.toFixed(2);
        } else {
            totalInput.value = '0.00';
        }
    }
    
    menuSelect.addEventListener('change', calculateTotal);
    quantityInput.addEventListener('input', calculateTotal);
    
    // Calculate initial total
    calculateTotal();
});
</script>
