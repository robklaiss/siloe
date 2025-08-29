<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($title ?? 'Siloe') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <style>
        /* Ensure proper spacing for sidebar */
        .main-content {
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }
        
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 16rem;
            }
        }
        
        /* Mobile adjustments */
        @media (max-width: 1023px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 16rem;
            background: #1f2937;
            color: white;
            z-index: 50;
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            display: none;
        }
        
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background-color: #374151;
        }
        
        .nav-item.active {
            background-color: #111827;
            color: white;
        }
        
        .nav-item i {
            width: 1.25rem;
            margin-right: 0.75rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 border-b border-gray-700">
            <h1 class="text-xl font-bold"><?= htmlspecialchars('Siloe') ?></h1>
        </div>
        
        <nav class="p-4">
            <?php
            // Navigation items based on user role
            $navItems = [];
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            if (isset($_SESSION['user_role'])) {
                $role = $_SESSION['user_role'];
                
                switch($role) {
                    case 'admin':
                        $navItems = [
                            ['title' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => 'fas fa-tachometer-alt'],
                            ['title' => 'Companies', 'url' => '/admin/companies', 'icon' => 'fas fa-building'],
                            ['title' => 'Users', 'url' => '/admin/users', 'icon' => 'fas fa-users'],
                            ['title' => 'Menus', 'url' => '/menus', 'icon' => 'fas fa-utensils'],
                            ['title' => 'Orders', 'url' => '/orders', 'icon' => 'fas fa-shopping-cart'],
                        ];
                        break;
                        
                    case 'company_admin':
                        $companyId = $_SESSION['company_id'] ?? '';
                        $navItems = [
                            ['title' => 'HR Dashboard', 'url' => "/admin/companies/{$companyId}/hr", 'icon' => 'fas fa-tachometer-alt'],
                            ['title' => 'Employees', 'url' => "/admin/companies/{$companyId}/hr/employees", 'icon' => 'fas fa-users'],
                            ['title' => 'Today\'s Selections', 'url' => "/admin/companies/{$companyId}/hr/menu-selections/today", 'icon' => 'fas fa-clipboard-list'],
                            ['title' => 'History', 'url' => "/admin/companies/{$companyId}/hr/menu-selections/history", 'icon' => 'fas fa-history'],
                        ];
                        break;
                        
                    case 'employee':
                        $navItems = [
                            ['title' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt'],
                            ['title' => 'Order Lunch', 'url' => '/menu/select', 'icon' => 'fas fa-utensils'],
                            ['title' => 'My Orders', 'url' => '/menu/my-selections', 'icon' => 'fas fa-history'],
                        ];
                        break;
                        
                    default:
                        $navItems = [
                            ['title' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'fas fa-tachometer-alt'],
                            ['title' => 'Menus', 'url' => '/menus', 'icon' => 'fas fa-utensils'],
                            ['title' => 'Orders', 'url' => '/orders', 'icon' => 'fas fa-shopping-cart'],
                        ];
                }
            }
            
            foreach ($navItems as $item):
                // Use 'active' parameter from controller if available, otherwise detect from URL
                $itemKey = strtolower(basename($item['url']));
                $isActive = (isset($active) && $active === $itemKey) || 
                           $currentPath === $item['url'] || 
                           (strpos($currentPath, $item['url']) === 0 && $item['url'] !== '/');
            ?>
                <a href="<?= $item['url'] ?>" 
                   class="nav-item <?= $isActive ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <?= $item['title'] ?>
                </a>
            <?php endforeach; ?>
            
            <!-- Profile -->
            <a href="/profile" 
               class="nav-item <?= (isset($active) && $active === 'profile') || ($currentPath === '/profile' || strpos($currentPath, '/profile/') === 0) ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                Profile
            </a>
            
            <!-- Logout -->
            <form action="/logout" method="POST" class="mt-4">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="nav-item w-full text-left">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </form>
        </nav>
    </div>

    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-gray-800 text-white p-4 z-30">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold"><?= htmlspecialchars('Siloe') ?></h1>
            <button id="mobileMenuBtn" class="text-white">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Main Content -->
    <main class="main-content pt-16 lg:pt-0">
        <div class="container mx-auto px-4 py-8">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <?php require_once $viewFile ?? ''; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        function toggleMobileMenu() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
            document.body.classList.toggle('overflow-hidden');
        }
        
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        overlay.addEventListener('click', toggleMobileMenu);
        
        // Close menu when clicking on nav items (mobile)
        const navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleMobileMenu();
                }
            });
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                toggleMobileMenu();
            }
        });
    </script>
</body>
</html>
