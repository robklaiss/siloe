<?php 
    // Use unified admin layout: hide top navbar and container
    $hideNavbar = false; 
    $wrapContainer = false; 
    $title = 'Siloe empresas';
    $sidebarTitle = 'Siloe empresas';
    require_once __DIR__ . '/../../partials/header.php'; 
?>

<div class="min-h-screen flex">
    <?php $active = 'companies'; require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <div class="flex-1 p-8">
        <!-- Mobile menu button (Tailwind, matches dashboard) -->
        <div class="lg:hidden mb-4">
            <button type="button" class="px-3 py-2 border rounded text-gray-700" onclick="document.getElementById('adminSidebar').classList.remove('hidden')">☰ Menú</button>
        </div>

        <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($company['name']); ?></h1>
        <div>
            <a href="/admin/companies" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Empresas
            </a>
            <a href="/admin/companies/<?= $company['id']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
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

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalles de la empresa</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">ID:</th>
                            <td><?= $company['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td><?= htmlspecialchars($company['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Logotipo:</th>
                            <td>
                                <?php if ($company['logo']): ?>
                                    <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logotipo de <?= htmlspecialchars($company['name']); ?>" style="max-height: 150px;">
                                <?php else: ?>
                                    <span class="text-muted">Sin logotipo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td><?= nl2br(htmlspecialchars($company['address'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Correo de contacto:</th>
                            <td><?= htmlspecialchars($company['contact_email'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono de contacto:</th>
                            <td><?= htmlspecialchars($company['contact_phone'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <?php if ($company['is_active']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Creada:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($company['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Última actualización:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($company['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Paneles de la empresa</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="/admin/companies/<?= $company['id']; ?>/hr" class="btn btn-primary">
                            <i class="fas fa-users-cog"></i> Panel de RR. HH.
                        </a>
                        <a href="/admin/companies/<?= $company['id']; ?>/employee" class="btn btn-secondary">
                            <i class="fas fa-user-friends"></i> Panel de empleados
                        </a>
                        <a href="/hr/<?= $company['id']; ?>/login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Inicio de sesión de empleados (Empresa)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Usuarios de la empresa</h5>
            <a href="/admin/companies/<?= $company['id']; ?>/hr/employees/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Agregar usuario
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo electrónico</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No se encontraron usuarios para esta empresa</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id']; ?></td>
                                    <td><?= htmlspecialchars($user['name']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">Administrador</span>
                                        <?php elseif ($user['role'] === 'company_admin'): ?>
                                            <span class="badge bg-warning">Administrador de empresa</span>
                                        <?php elseif ($user['role'] === 'hr'): ?>
                                            <span class="badge bg-info">RR. HH.</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Empleado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/users/<?= $user['id']; ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
