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
                <h2 class="text-2xl font-bold">Order Details</h2>
                <div>
                    <a href="/orders" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">
                        Back to Orders
                    </a>
                    <a href="/orders/<?= htmlspecialchars($order['id']) ?>/edit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Edit Order
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
                        <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                        <p class="mb-2"><span class="font-medium">Order ID:</span> <?= htmlspecialchars($order['id']) ?></p>
                        <p class="mb-2"><span class="font-medium">Date:</span> <?= htmlspecialchars($order['order_date']) ?></p>
                        <p class="mb-2"><span class="font-medium">Status:</span> 
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
                        </p>
                        <p class="mb-2"><span class="font-medium">Special Requests:</span> <?= htmlspecialchars($order['special_requests'] ?? 'None') ?></p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-4">User Information</h3>
                        <p class="mb-2"><span class="font-medium">Name:</span> <?= htmlspecialchars($order['user_name'] ?? 'N/A') ?></p>
                        <p class="mb-2"><span class="font-medium">Email:</span> <?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    <div class="flex space-x-2">
                        <?php if ($order['status'] !== 'completed'): ?>
                        <form action="/orders/<?= htmlspecialchars($order['id']) ?>/status" method="POST" class="inline">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                Mark as Completed
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] !== 'cancelled'): ?>
                        <form action="/orders/<?= htmlspecialchars($order['id']) ?>/status" method="POST" class="inline">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                Cancel Order
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <button type="button" id="deleteOrderBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Delete Order
                        </button>
                    </div>
                </div>
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
                <form id="deleteForm" action="/orders/<?= htmlspecialchars($order['id']) ?>" method="POST">
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
    </script>
</body>
</html>
