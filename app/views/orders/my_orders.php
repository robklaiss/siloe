<?php require_once __DIR__ . '/../partials/employee_header.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mis Pedidos</h1>
            <p class="mt-1 text-sm text-gray-600">Ver historial y estado de pedidos</p>
        </div>
        <a href="/menu/select" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nuevo Pedido
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        <?= htmlspecialchars($_SESSION['flash']['success']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No hay pedidos aún</h3>
                <p class="mt-1 text-sm text-gray-500">Comience realizando su primer pedido.</p>
                <div class="mt-6">
                    <a href="/menu/select" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Nuevo Pedido
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($orders as $order): ?>
                    <li class="border-b border-gray-200">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-blue-600 truncate">
                                        Pedido #<?= htmlspecialchars($order['id']) ?>
                                    </p>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= 
                                        $order['status'] === 'completed' ? 'green' : 
                                        ($order['status'] === 'cancelled' ? 'red' : 'yellow') 
                                    ?>-100 text-<?= 
                                        $order['status'] === 'completed' ? 'green' : 
                                        ($order['status'] === 'cancelled' ? 'red' : 'yellow') 
                                    ?>-800">
                                        <?php
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'processing' => 'Procesando',
                                                'completed' => 'Completado',
                                                'cancelled' => 'Cancelado'
                                            ];
                                            echo htmlspecialchars($statusLabels[$order['status']] ?? ucfirst($order['status']));
                                        ?>
                                    </span>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm font-medium text-gray-900">
                                        ₲<?= number_format($order['total_amount'], 0, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        <?= date('M j, Y', strtotime($order['order_date'])) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Artículos:</h4>
                                <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                            <div class="w-0 flex-1 flex items-center">
                                                <span class="ml-2 flex-1 w-0 truncate">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </span>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <span class="text-gray-900 font-medium">
                                                    <?= $item['quantity'] ?> × ₲<?= number_format($item['unit_price'], 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <?php if (!empty($order['special_requests'])): ?>
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-900">Solicitudes Especiales:</h4>
                                    <p class="mt-1 text-sm text-gray-500"><?= nl2br(htmlspecialchars($order['special_requests'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/employee_footer.php'; ?>
