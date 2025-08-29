<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Administrar Empresas</h1>
        <a href="/admin/companies/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i>Agregar Nueva Empresa
        </a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo de Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono de Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($companies)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron empresas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($companies as $company): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($company['logo']): ?>
                                        <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logo de <?= htmlspecialchars($company['name']); ?>" class="h-12 w-12 rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="text-gray-500">Sin logo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $company['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($company['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($company['contact_email'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($company['contact_phone'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($company['is_active']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Activa
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactiva
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('Y-m-d', strtotime($company['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="/admin/companies/<?= $company['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/companies/<?= $company['id']; ?>/edit" class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/companies/<?= $company['id']; ?>/hr" class="text-purple-600 hover:text-purple-900">
                                            <i class="fas fa-users-cog"></i>
                                        </a>
                                        <a href="/admin/companies/<?= $company['id']; ?>/employee" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-user-friends"></i>
                                        </a>
                                        <button type="button" class="text-red-600 hover:text-red-900" onclick="confirmDelete(<?= $company['id']; ?>, '<?= htmlspecialchars($company['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitar Eliminación</h3>
            <div class="mb-4">
                <p class="text-gray-600 mb-2">¿Desea solicitar la eliminación de <span id="deleteCompanyName" class="font-semibold"></span>?</p>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i> La eliminación de empresas requiere aprobación administrativa. Su solicitud será revisada por un administrador para confirmar la acción.
                    </p>
                </div>
            </div>
            <form id="deleteForm" method="POST" class="space-y-4">
                <div>
                    <label for="delete_reason" class="block text-sm font-medium text-gray-700 mb-1">Motivo de la eliminación (opcional):</label>
                    <textarea id="delete_reason" name="delete_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(companyId, companyName) {
    document.getElementById('deleteCompanyName').textContent = companyName;
    document.getElementById('deleteForm').action = `/admin/companies/${companyId}/delete`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
