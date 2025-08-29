<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Users</h1>
        <a href="/admin/users/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i>Add User
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
            
            <!-- Grouped Users Accordions -->
            <div class="space-y-4">
                <!-- System Admins -->
                <details class="bg-white rounded-lg shadow">
                    <summary class="cursor-pointer select-none px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">Admins del sistema</span>
                            <span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5"><?= count($system_admins ?? []) ?></span>
                        </div>
                        <span class="text-gray-400">▼</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <?php if (empty($system_admins)): ?>
                            <p class="text-sm text-gray-500">Sin admins del sistema.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($system_admins as $user): ?>
                                            <tr>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['id']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="/admin/users/<?= htmlspecialchars($user['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? null)): ?>
                                                        <form action="/admin/users/<?= htmlspecialchars($user['id']) ?>" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['csrf_token'] ?? '')) ?>">
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- Company-scoped Admins -->
                <details class="bg-white rounded-lg shadow">
                    <summary class="cursor-pointer select-none px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">Admins asociados a empresa</span>
                            <span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5"><?= count($normal_admins ?? []) ?></span>
                        </div>
                        <span class="text-gray-400">▼</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <?php if (empty($normal_admins)): ?>
                            <p class="text-sm text-gray-500">Sin admins asociados a empresa.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($normal_admins as $user): ?>
                                            <tr>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['id']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['company_name'] ?? '—') ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="/admin/users/<?= htmlspecialchars($user['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? null)): ?>
                                                        <form action="/admin/users/<?= htmlspecialchars($user['id']) ?>" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['csrf_token'] ?? '')) ?>">
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- HR Managers (Company Admins) -->
                <details class="bg-white rounded-lg shadow">
                    <summary class="cursor-pointer select-none px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">Administradores de RR. HH.</span>
                            <span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5"><?= count($hr_managers ?? []) ?></span>
                        </div>
                        <span class="text-gray-400">▼</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <?php if (empty($hr_managers)): ?>
                            <p class="text-sm text-gray-500">Sin administradores de RR. HH.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($hr_managers as $user): ?>
                                            <tr>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['id']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['company_name'] ?? '—') ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Company Admin</span>
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="/admin/users/<?= htmlspecialchars($user['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? null)): ?>
                                                        <form action="/admin/users/<?= htmlspecialchars($user['id']) ?>" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['csrf_token'] ?? '')) ?>">
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- System Users -->
                <details class="bg-white rounded-lg shadow">
                    <summary class="cursor-pointer select-none px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">Usuarios del sistema</span>
                            <span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5"><?= count($system_users ?? []) ?></span>
                        </div>
                        <span class="text-gray-400">▼</span>
                    </summary>
                    <div class="px-6 pb-6">
                        <?php if (empty($system_users)): ?>
                            <p class="text-sm text-gray-500">Sin usuarios del sistema.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($system_users as $user): ?>
                                            <tr>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['id']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">User</span>
                                                </td>
                                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="/admin/users/<?= htmlspecialchars($user['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? null)): ?>
                                                        <form action="/admin/users/<?= htmlspecialchars($user['id']) ?>" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['csrf_token'] ?? '')) ?>">
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- Employees (managed per company) -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">Empleados (gestionar por empresa)</div>
                            <div class="text-sm text-gray-500 mt-1">Total: <span class="font-medium"><?= (int)($employees_total ?? 0) ?></span></div>
                        </div>
                        <a href="/admin/companies" class="text-blue-600 hover:text-blue-800">Ir a empresas</a>
                    </div>
                </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
