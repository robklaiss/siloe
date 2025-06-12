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
                        <a href="/menus" class="block py-2 px-4 rounded bg-gray-700 text-white">Menus</a>
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
                <h2 class="text-2xl font-bold">Create New Menu Item</h2>
                <a href="/menus" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Menus
                </a>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Create Menu Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="/menus" method="POST" id="menuForm">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" id="name" value="<?= isset($_SESSION['old']['name']) ? htmlspecialchars($_SESSION['old']['name']) : '' ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea name="description" id="description" rows="3" 
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?= isset($_SESSION['old']['description']) ? htmlspecialchars($_SESSION['old']['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price (PYG) <span class="text-xs text-gray-500">Incl. 10% I.V.A.</span></label>
                        <div class="relative">
                            <input type="number" name="price" id="price" step="1" min="0" value="<?= isset($_SESSION['old']['price']) ? htmlspecialchars($_SESSION['old']['price']) : '' ?>" 
                                   class="shadow appearance-none border rounded w-full py-2 pl-8 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-gray-500">₲</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Enter the total price including 10% I.V.A.</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="available" class="block text-gray-700 text-sm font-bold mb-2">Availability</label>
                        <select name="available" id="available" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="1" <?= (isset($_SESSION['old']['available']) && $_SESSION['old']['available'] == 1) ? 'selected' : '' ?>>Available</option>
                            <option value="0" <?= (isset($_SESSION['old']['available']) && $_SESSION['old']['available'] == 0) ? 'selected' : '' ?>>Not Available</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-end">
                        <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Create Menu Item
                        </button>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('menuForm');
            const submitBtn = document.getElementById('submitBtn');
            let isSubmitting = false;
            
            form.addEventListener('submit', function(e) {
                // Prevent double submission
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                // Mark as submitting
                isSubmitting = true;
                
                // Disable the submit button
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.textContent = 'Creating...';
                
                // Add a hidden field to indicate this is a controlled submission
                const controlField = document.createElement('input');
                controlField.type = 'hidden';
                controlField.name = 'controlled_submit';
                controlField.value = 'true';
                form.appendChild(controlField);
            });
        });
    </script>
</body>
</html>
