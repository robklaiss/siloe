<?php 
    // Hide top navbar and container when using the admin sidebar layout
    $hideNavbar = false; 
    $wrapContainer = false; 
    $title = 'Siloe empresas';
    $sidebarTitle = 'Siloe empresas';
    require_once __DIR__ . '/../../partials/header.php'; 
?>

<div class="min-h-screen flex">
    <?php $active = 'users'; require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <div class="flex-1 p-8">
        <!-- Mobile menu button (Tailwind, matches dashboard) -->
        <div class="lg:hidden mb-4">
            <button type="button" class="px-3 py-2 border rounded text-gray-700" onclick="document.getElementById('adminSidebar').classList.remove('hidden')">☰ Menú</button>
        </div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Create New User</h2>
            <a href="/admin/users" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Back to Users
            </a>
        </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Create User Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form action="/admin/users" method="POST">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" id="name" value="<?= isset($_SESSION['old']['name']) ? htmlspecialchars($_SESSION['old']['name']) : '' ?>" 
                               class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" id="email" value="<?= isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : '' ?>" 
                               class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" id="password" 
                               class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirm" id="password_confirm" 
                               class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                        <select name="role" id="role" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="toggleCompanySelect()">
                            <option value="employee" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'employee') ? 'selected' : '' ?>>Employee</option>
                            <option value="company_admin" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'company_admin') ? 'selected' : '' ?>>Company Admin</option>
                            <option value="admin" <?= (isset($_SESSION['old']['role']) && $_SESSION['old']['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="mb-6" id="companySelectContainer" style="display: none;">
                        <label for="company_id" class="block text-gray-700 text-sm font-bold mb-2">Company</label>
                        <select name="company_id" id="company_id" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select a company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>" <?= (isset($_SESSION['old']['company_id']) && $_SESSION['old']['company_id'] == $company['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($company['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <script>
                    function toggleCompanySelect() {
                        const roleSelect = document.getElementById('role');
                        const companySelect = document.getElementById('companySelectContainer');
                        
                        if (roleSelect.value === 'company_admin') {
                            companySelect.style.display = 'block';
                            document.getElementById('company_id').setAttribute('required', 'required');
                        } else {
                            companySelect.style.display = 'none';
                            document.getElementById('company_id').removeAttribute('required');
                        }
                    }
                    
                    // Initialize on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        toggleCompanySelect();
                    });
                    </script>
                    
                    <div class="flex items-center justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Create User
                        </button>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['old'])) unset($_SESSION['old']); ?>
            </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
