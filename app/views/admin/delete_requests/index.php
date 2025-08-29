<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Solicitudes de Eliminación Pendientes</h1>
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

    <?php if (empty($requests)): ?>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-500">No hay solicitudes de eliminación pendientes</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitado por</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($request['entity_type'] === 'company'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Empresa
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <?= htmlspecialchars(ucfirst($request['entity_type'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $request['entity_id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($request['entity_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($request['requester_name'] ?? ''); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d/m/Y H:i', strtotime($request['requested_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="/admin/delete-requests/<?= $request['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <button type="button" onclick="showApproveModal(<?= $request['id']; ?>, '<?= htmlspecialchars($request['entity_name']); ?>')" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i> Aprobar
                                        </button>
                                        <button type="button" onclick="showRejectModal(<?= $request['id']; ?>, '<?= htmlspecialchars($request['entity_name']); ?>')" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de aprobación -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar Aprobación</h3>
            <p class="text-gray-600 mb-4">¿Está seguro que desea aprobar la eliminación de <span id="approveEntityName" class="font-semibold"></span>?</p>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4">
                <p class="text-sm text-yellow-700">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Esta acción eliminará permanentemente la entidad y no se puede deshacer.
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeApproveModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancelar
                </button>
                <form id="approveForm" method="POST">
                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                        Confirmar Aprobación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de rechazo -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar Rechazo</h3>
            <p class="text-gray-600 mb-4">¿Está seguro que desea rechazar la eliminación de <span id="rejectEntityName" class="font-semibold"></span>?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancelar
                </button>
                <form id="rejectForm" method="POST">
                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Confirmar Rechazo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showApproveModal(id, name) {
    document.getElementById('approveEntityName').textContent = name;
    document.getElementById('approveForm').action = `/admin/delete-requests/${id}/approve`;
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
}

function showRejectModal(id, name) {
    document.getElementById('rejectEntityName').textContent = name;
    document.getElementById('rejectForm').action = `/admin/delete-requests/${id}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
