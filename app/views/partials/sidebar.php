<div class="w-64 bg-gray-800 text-white p-4 flex-shrink-0 hidden md:block">
    <div class="mb-8">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars(APP_NAME) ?></h1>
        <p class="text-gray-400 text-sm">Gestión de Pedidos</p>
    </div>
    
    <nav>
        <ul class="space-y-2">
            <li>
                <a href="/dashboard" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Panel
                </a>
            </li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li>
                <a href="/users" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Usuarios
                </a>
            </li>
            <li>
                <a href="/admin/companies" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h3V7H5a2 2 0 00-2 2zm8-4v16h3a2 2 0 002-2V7a2 2 0 00-2-2h-3z" />
                    </svg>
                    Empresas
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="/menus" class="flex items-center py-2 px-4 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Menús
                </a>
            </li>
            <li>
                <a href="/orders" class="flex items-center py-2 px-4 rounded bg-gray-700 text-white">
                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Pedidos
                </a>
            </li>
            <li class="mt-8 pt-4 border-t border-gray-700">
                <form action="/logout" method="POST" class="block">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="w-full flex items-center text-left py-2 px-4 rounded text-red-400 hover:bg-red-900 hover:text-red-200 transition-colors">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>
