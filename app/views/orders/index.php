<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Find all delete buttons and attach event listeners
            const deleteButtons = document.querySelectorAll('[data-delete-button]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const formId = this.getAttribute('data-form-id');
                    if (confirm('Are you sure you want to delete this order?')) {
                        document.getElementById(formId).submit();
                    }
                });
            });
        });
    </script>
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
                        <a href="/admin/dashboard" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Dashboard</a>
                    </li>
                    <li>
                        <a href="/admin/users" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Users</a>
                    </li>
                    <li>
                        <a href="/menus" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Menus</a>
                    </li>
                    <li>
                        <a href="/orders" class="block py-2 px-4 rounded bg-gray-700 text-white">Orders</a>
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
                <h2 class="text-2xl font-bold">Order Management</h2>
                <a href="/orders/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New Order
                </a>
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
            
            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Special Requests</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No orders found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= isset($order['user_name']) ? htmlspecialchars($order['user_name']) : 'N/A' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($order['items_summary'] ?? '') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        ₲<?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php elseif ($order['status'] === 'completed'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        <?php elseif ($order['status'] === 'cancelled'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['special_requests'] ?? 'None') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['order_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/orders/<?= htmlspecialchars($order['id']) ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="/orders/<?= htmlspecialchars($order['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button type="button" class="text-red-600 hover:text-red-900 delete-btn" data-id="<?= htmlspecialchars($order['id']) ?>">Delete</button>
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
            <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
            <p class="mb-6">Are you sure you want to delete this order?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        const deleteForm = document.getElementById('deleteForm');
        const cancelDelete = document.getElementById('cancelDelete');
        
        // Get all delete buttons
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        // Add click event listener to each delete button
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-id');
                deleteForm.action = '/orders/' + orderId;
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
    });
    </script>
</body>
</html>
