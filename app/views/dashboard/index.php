<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
    
    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash']['error']; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash']['success']; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Welcome to your Dashboard</h2>
        <p class="mb-4">You are logged in as: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
        <p class="mb-4">Role: <strong><?= htmlspecialchars(ucfirst($user['role'])) ?></strong></p>
        
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-2">Quick Links</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/dashboard" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        Admin Dashboard
                    </a>
                    <a href="/admin/users" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        Manage Users
                    </a>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'company_admin'): ?>
                    <a href="/hr/dashboard" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        HR Dashboard
                    </a>
                    <a href="/hr/employees" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        Manage Employees
                    </a>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'employee'): ?>
                    <a href="/menu/select" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        Select Menu
                    </a>
                    <a href="/menu/my-selections" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        My Menu History
                    </a>
                <?php endif; ?>
                
                <a href="/profile" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    My Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
