<div class="mb-6">
    <h1 class="text-2xl font-bold">¡Hola <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1>
    <p class="text-gray-600 mt-1">Bienvenido al Panel de Administración</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <i class="fas fa-users text-blue-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Usuarios</p>
                <p class="text-2xl font-bold"><?= $stats['users'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <i class="fas fa-utensils text-green-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Menús</p>
                <p class="text-2xl font-bold"><?= $stats['menu_count'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                <i class="fas fa-shopping-cart text-yellow-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Pedidos</p>
                <p class="text-2xl font-bold"><?= $stats['order_count'] ?></p>
            </div>
        </div>
    </div>
    
    <a href="/admin/delete-requests" class="bg-white rounded-lg shadow-md p-6 hover:bg-gray-50">
        <div class="flex items-center">
            <div class="rounded-full bg-red-100 p-3 mr-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Solicitudes de eliminación</p>
                <p class="text-2xl font-bold"><?= $stats['pending_delete_requests'] ?? 0 ?></p>
                <?php if (($stats['pending_delete_requests'] ?? 0) > 0): ?>
                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-red-100 text-red-800">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Pendientes
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Últimos menús creados</h2>
        
        <?php if (empty($stats['latest_menus'])): ?>
            <p class="text-gray-500">No hay menús registrados aún.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stats['latest_menus'] as $menu): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($menu['name']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">$<?= number_format($menu['price'], 2) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($menu['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Últimos pedidos</h2>
        
        <?php if (empty($stats['latest_orders'])): ?>
            <p class="text-gray-500">No hay pedidos registrados aún.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stats['latest_orders'] as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($order['user_name'] ?? 'N/A') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">$<?= $order['total_amount'] ? number_format($order['total_amount'], 2) : '0.00' ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
