<?php
/**
 * Unified Navigation Component
 * Provides consistent navigation across all pages
 * Desktop: Left sidebar navigation
 * Tablet/Mobile: Hamburger menu
 */

// Determine active page based on current route
$currentRoute = $_SERVER['REQUEST_URI'] ?? '';
$activePage = '';

if (strpos($currentRoute, '/admin/dashboard') !== false) $activePage = 'admin_dashboard';
elseif (strpos($currentRoute, '/admin/companies') !== false) $activePage = 'companies';
elseif (strpos($currentRoute, '/admin/users') !== false) $activePage = 'users';
elseif (strpos($currentRoute, '/menus') !== false) $activePage = 'menus';
elseif (strpos($currentRoute, '/orders') !== false) $activePage = 'orders';
elseif (strpos($currentRoute, '/dashboard') !== false) $activePage = 'dashboard';
elseif (strpos($currentRoute, '/menu/select') !== false) $activePage = 'menu_select';
elseif (strpos($currentRoute, '/menu/my-selections') !== false) $activePage = 'my_selections';
elseif (strpos($currentRoute, '/hr/') !== false) $activePage = 'hr_dashboard';
elseif (strpos($currentRoute, '/profile') !== false) $activePage = 'profile';

// Navigation items based on user role
$navItems = [];

if (isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    
    switch($role) {
        case 'admin':
            $navItems = [
                ['title' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => 'fas fa-tachometer-alt', 'key' => 'admin_dashboard'],
                ['title' => 'Companies', 'url' => '/admin/companies', 'icon' => 'fas fa-building', 'key' => 'companies'],
                ['title' => 'Users', 'url' => '/admin/users', 'icon' => 'fas fa-users', 'key' => 'users'],
                ['title' => 'Menus', 'url' => '/menus', 'icon' => 'fas fa-utensils', 'key' => 'menus'],
                ['title' => 'Orders', 'url' => '/orders', 'icon' => 'fas fa-shopping-cart', 'key' => 'orders'],
            ];
            break;
            
        case 'company_admin':
            $companyId = $_SESSION['company_id'] ?? '';
            $navItems = [
                ['title' => 'HR Dashboard', 'url' => "/admin/companies/{$companyId}/hr", 'icon' => 'fas fa-tachometer-alt', 'key' => 'hr_dashboard'],
                ['title' => 'Employees', 'url' => "/admin/companies/{$companyId}/hr/employees", 'icon' => 'fas fa-users', 'key' => 'employees'],
                ['title' => 'Menu Selections', 'url' => "/admin/companies/{$companyId}/hr/menu-selections/today", 'icon' => 'fas fa-clipboard-list', 'key' => 'menu_selections'],
                ['title' => 'History', 'url' => "/admin/companies/{$companyId}/hr/menu-selections/history", 'icon' => 'fas fa-history', 'key' => 'history'],
            ];
            break;
            
        case 'employee':
            $navItems = [
                ['title' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'],
                ['title' => 'Order Lunch', 'url' => '/menu/select', 'icon' => 'fas fa-utensils', 'key' => 'menu_select'],
                ['title' => 'My Orders', 'url' => '/menu/my-selections', 'icon' => 'fas fa-history', 'key' => 'my_selections'],
            ];
            break;
            
        default:
            $navItems = [
                ['title' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'],
                ['title' => 'Menus', 'url' => '/menus', 'icon' => 'fas fa-utensils', 'key' => 'menus'],
                ['title' => 'Orders', 'url' => '/orders', 'icon' => 'fas fa-shopping-cart', 'key' => 'orders'],
            ];
    }
}

// Always add profile and logout
$navItems[] = ['title' => 'Profile', 'url' => '/profile', 'icon' => 'fas fa-user', 'key' => 'profile'];

?>

<!-- Desktop Sidebar Navigation -->
<div class="navigation-container">
    <!-- Desktop Sidebar -->
    <div id="desktopSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
        <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
            <h1 class="text-xl font-bold"><?= htmlspecialchars(APP_NAME) ?></h1>
            <!-- Close button for mobile -->
            <button id="closeSidebar" class="lg:hidden text-gray-300 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="mt-5 px-2">
            <div class="space-y-1">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= $item['url'] ?>" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?= $activePage === $item['key'] ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                        <i class="<?= $item['icon'] ?> mr-3"></i>
                        <?= $item['title'] ?>
                    </a>
                <?php endforeach; ?>
                
                <!-- Logout -->
                <form action="/logout" method="POST" class="mt-auto">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="group flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white rounded-md transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Mobile Header with Hamburger -->
    <div class="lg:hidden bg-gray-800 text-white shadow-lg">
        <div class="flex items-center justify-between px-4 py-3">
            <h1 class="text-lg font-bold"><?= htmlspecialchars(APP_NAME) ?></h1>
            <button id="mobileMenuToggle" class="text-gray-300 hover:text-white">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>
</div>

<style>
/* Ensure sidebar is visible on desktop */
@media (min-width: 1024px) {
    #desktopSidebar {
        transform: translateX(0) !important;
    }
}

/* Adjust main content for sidebar */
body {
    padding-left: 0;
}

@media (min-width: 1024px) {
    body {
        padding-left: 16rem; /* 64px (w-64) */
    }
    
    .main-content {
        margin-left: 0;
    }
}

/* Mobile menu transitions */
#desktopSidebar {
    transition: transform 0.3s ease-in-out;
}

#desktopSidebar.mobile-open {
    transform: translateX(0);
}

#desktopSidebar.mobile-closed {
    transform: translateX(-100%);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const desktopSidebar = document.getElementById('desktopSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    function openSidebar() {
        desktopSidebar.classList.remove('mobile-closed');
        desktopSidebar.classList.add('mobile-open');
        sidebarOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    
    function closeSidebar() {
        desktopSidebar.classList.remove('mobile-open');
        desktopSidebar.classList.add('mobile-closed');
        sidebarOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    
    // Mobile menu toggle
    mobileMenuToggle?.addEventListener('click', openSidebar);
    closeSidebar?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // Close when clicking on nav links (mobile)
    const navLinks = desktopSidebar.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth >= 1024) {
            closeSidebar();
        }
    }
    
    window.addEventListener('resize', handleResize);
});
</script>
