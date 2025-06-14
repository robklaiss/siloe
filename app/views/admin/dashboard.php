<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white p-4">
            <div class="mb-8">
                <h1 class="text-2xl font-bold"><?= htmlspecialchars(APP_NAME) ?></h1>
                <p class="text-gray-400 text-sm">Admin Dashboard</p>
            </div>
            
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="/admin/dashboard" class="block py-2 px-4 rounded bg-gray-700 text-white">Dashboard</a>
                    </li>
                    <li>
                        <a href="/admin/companies" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Companies</a>
                    </li>
                    <li>
                        <a href="/admin/users" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Users</a>
                    </li>
                    <li>
                        <a href="/menus" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Menus</a>
                    </li>
                    <li>
                        <a href="/orders" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Orders</a>
                    </li>
                    <li class="mt-8">
                        <form action="/logout" method="POST" class="block">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="w-full text-left py-2 px-4 rounded hover:bg-red-700 text-gray-300 hover:text-white">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Dashboard Overview</h2>
                <div class="text-sm text-gray-600">
                    Welcome, <?= htmlspecialchars($_SESSION['user_email']) ?>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Users</h3>
                            <p class="text-2xl font-semibold"><?= htmlspecialchars($stats['users']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-500 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Last Login</h3>
                            <p class="text-2xl font-semibold"><?= htmlspecialchars($stats['last_login']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-500 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">System Status</h3>
                            <p class="text-2xl font-semibold">Active</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Latest Menus -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Latest Menus</h3>
                            <a href="/menus" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        <?php if (empty($stats['latest_menus'])): ?>
                            <p class="text-gray-500 text-sm">No menus found.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($stats['latest_menus'] as $menu): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($menu['name']) ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    ₲<?= number_format($menu['price'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <?php if ($menu['available']): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Available
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Unavailable
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Latest Orders -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Orders</h3>
                            <a href="/orders" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        <?php if (empty($stats['latest_orders'])): ?>
                            <p class="text-gray-500 text-sm">No orders found.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($stats['latest_orders'] as $order): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    #<?= htmlspecialchars($order['id']) ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    <?= htmlspecialchars($order['user_name'] ?? 'Guest') ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    ₲<?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <?php if ($order['status'] === 'completed'): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Completed
                                                        </span>
                                                    <?php elseif ($order['status'] === 'pending'): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Pending
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                            <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                <div class="border-t border-gray-200">
                    <div class="py-3 flex items-center">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium">Admin logged in</p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($_SESSION['user_email']) ?></p>
                        </div>
                        <div class="ml-auto text-sm text-gray-500">
                            <?= date('Y-m-d H:i:s') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
