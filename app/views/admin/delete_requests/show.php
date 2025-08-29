<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detalles de Solicitud de Eliminación</h1>
        <a href="/admin/delete-requests" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i>Volver
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

    <?php if (!$request): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            Solicitud no encontrada
        </div>
    <?php else: ?>
        <!-- Información de la solicitud -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Solicitud #<?= $request['id']; ?>
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Detalles de la solicitud de eliminación
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Tipo de entidad
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php if ($request['entity_type'] === 'company'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Empresa
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= htmlspecialchars(ucfirst($request['entity_type'])); ?>
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Nombre
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?= htmlspecialchars($request['entity_name']); ?>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            ID de la entidad
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?= $request['entity_id']; ?>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Solicitado por
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?= htmlspecialchars($request['requester_name'] ?? 'Usuario desconocido'); ?> 
                            (<?= htmlspecialchars($request['requester_email'] ?? 'sin correo'); ?>)
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Fecha de solicitud
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?= date('d/m/Y H:i', strtotime($request['requested_at'])); ?>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Estado
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <?php if ($request['status'] === 'pending'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pendiente
                                </span>
                            <?php elseif ($request['status'] === 'approved'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Aprobado
                                </span>
                            <?php elseif ($request['status'] === 'rejected'): ?>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rechazado
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <?php if (!empty($request['reason'])): ?>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Motivo de la solicitud
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= nl2br(htmlspecialchars($request['reason'])); ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
        
        <?php if ($request['status'] === 'pending'): ?>
            <!-- Botones de acción -->
            <div class="flex space-x-4">
                <button type="button" onclick="showApproveModal(<?= $request['id']; ?>, '<?= htmlspecialchars($request['entity_name']); ?>')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-check mr-2"></i>Aprobar eliminación
                </button>
                <button type="button" onclick="showRejectModal(<?= $request['id']; ?>, '<?= htmlspecialchars($request['entity_name']); ?>')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-times mr-2"></i>Rechazar eliminación
                </button>
            </div>
        <?php endif; ?>
        
        <?php if ($request['entity_type'] === 'company' && !empty($entityDetails)): ?>
            <!-- Detalles de la empresa -->
            <h2 class="text-xl font-bold mt-8 mb-4">Detalles de la empresa</h2>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Nombre
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($entityDetails['name']); ?>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Email de contacto
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($entityDetails['contact_email'] ?? 'No disponible'); ?>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Teléfono de contacto
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($entityDetails['contact_phone'] ?? 'No disponible'); ?>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Estado
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php if (!empty($entityDetails['is_active']) && $entityDetails['is_active']): ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Activa
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactiva
                                    </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
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
