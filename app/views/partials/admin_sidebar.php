<div id="adminSidebar" class="w-64 bg-gray-800 text-white p-4 hidden lg:hidden fixed inset-y-0 left-0 z-50 shadow-lg">
    <?php $sidebarTitle = $sidebarTitle ?? APP_NAME; ?>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold truncate"><?= htmlspecialchars($sidebarTitle) ?></h1>
        <button type="button" class="lg:hidden text-gray-300 hover:text-white" aria-label="Cerrar menú"
                onclick="document.getElementById('adminSidebar').classList.add('hidden')">✕</button>
    </div>

    <?php 
        // Active link key: 'dashboard', 'companies', 'users', 'menus', 'orders'
        $active = $active ?? null; 
        $link = function($isActive) { 
            return $isActive 
                ? 'block py-2 px-4 rounded bg-gray-700 text-white' 
                : 'block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white'; 
        };
    ?>

    <nav>
        <ul class="space-y-2">
            <li>
                <a href="/admin/dashboard" class="<?= $link($active === 'dashboard') ?>">Panel</a>
            </li>
            <li>
                <a href="/admin/companies" class="<?= $link($active === 'companies') ?>">Empresas</a>
            </li>
            <li>
                <a href="/admin/users" class="<?= $link($active === 'users') ?>">Usuarios</a>
            </li>
            <li>
                <a href="/menus" class="<?= $link($active === 'menus') ?>">Menús</a>
            </li>
            <li>
                <a href="/orders" class="<?= $link($active === 'orders') ?>">Pedidos</a>
            </li>
            <li class="mt-8">
                <form action="/logout" method="POST" class="block">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="w-full text-left py-2 px-4 rounded hover:bg-red-700 text-gray-300 hover:text-white">Cerrar sesión</button>
                </form>
            </li>
        </ul>
    </nav>
</div>
