<?php 
    // Hide top navbar and container when using the admin sidebar layout
    $hideNavbar = true; 
    $wrapContainer = false; 
    require_once __DIR__ . '/../../partials/header.php'; 
?>

<div class="min-vh-100 d-flex">
    <?php require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestionar Empresas</h1>
            <a href="/admin/companies/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Nueva Empresa
            </a>
        </div>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Logotipo</th>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo de contacto</th>
                            <th>Teléfono de contacto</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($companies)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron empresas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td>
                                        <?php if ($company['logo']): ?>
                                            <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logotipo de <?= htmlspecialchars($company['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">Sin logotipo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $company['id']; ?></td>
                                    <td><?= htmlspecialchars($company['name']); ?></td>
                                    <td><?= htmlspecialchars($company['contact_email'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($company['contact_phone'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($company['is_active']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($company['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/companies/<?= $company['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/companies/<?= $company['id']; ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/companies/<?= $company['id']; ?>/hr" class="btn btn-sm btn-primary">
                                                <i class="fas fa-users-cog"></i> RR. HH.
                                            </a>
                                            <a href="/admin/companies/<?= $company['id']; ?>/employee" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-user-friends"></i> Empleados
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?= $company['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?= $company['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Está seguro de que desea eliminar la empresa "<?= htmlspecialchars($company['name']); ?>"?
                                                        Esta acción no se puede deshacer.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form action="/admin/companies/<?= $company['id']; ?>/delete" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
    </div>
    </div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
