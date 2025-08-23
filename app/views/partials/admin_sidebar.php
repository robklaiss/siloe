<div class="w-64 bg-gray-800 text-white p-4 flex-shrink-0">
    <div class="mb-8">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars(APP_NAME) ?></h1>
        <p class="text-gray-400 text-sm">Panel de Administración</p>
    </div>

    <nav>
        <ul class="space-y-2">
            <li>
                <a href="/admin/dashboard" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Panel</a>
            </li>
            <li>
                <a href="/admin/companies" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Empresas</a>
            </li>
            <li>
                <a href="/admin/users" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Usuarios</a>
            </li>
            <li>
                <a href="/menus" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Menús</a>
            </li>
            <li>
                <a href="/orders" class="block py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white">Pedidos</a>
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
