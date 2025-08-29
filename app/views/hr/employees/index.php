<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Gestión de empleados</h1>
            <?php if (isset($company)): ?>
                <p class="text-muted mb-0">Empresa: <?= htmlspecialchars($company['name']) ?></p>
            <?php endif; ?>
        </div>
        <div class="btn-group">
            <a href="/hr/<?= $company_id ?? '' ?>/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="/hr/<?= $company_id ?? '' ?>/employees/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar empleado
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Empleados</h5>
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" class="form-control" placeholder="Buscar empleados...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($employees)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron empleados.</p>
                    <a href="/hr/<?= $company_id ?? '' ?>/employees/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agrega tu primer empleado
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo electrónico</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($employee['name']) ?></td>
                                    <td><?= htmlspecialchars($employee['email']) ?></td>
                                    <td>
                                        <?php if ($employee['is_active']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= isset($employee['last_login']) && $employee['last_login'] 
                                            ? date('d/m/Y H:i', strtotime($employee['last_login'])) 
                                            : 'Nunca' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php 
                                                $employeeSlug = urlencode(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $employee['name'])));
                                                $baseUrl = "/hr/" . htmlspecialchars($employee['company_id']) . "/employees";
                                            ?>
                                            <a href="<?= $baseUrl ?>/<?= $employee['id'] ?>/<?= $employeeSlug ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/<?= $employee['id'] ?>/<?= $employeeSlug ?>/edit" 
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($employee['is_active']): ?>
                                                <a href="<?= $baseUrl ?>/<?= $employee['id'] ?>/<?= $employeeSlug ?>/deactivate" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   title="Desactivar"
                                                   onclick="return confirm('¿Estás seguro de que deseas desactivar a este empleado?');">
                                                    <i class="fas fa-user-times"></i>
                                                </a>
                                            <?php else: ?>
                                                <form action="/hr/<?= $company_id ?? '' ?>/employees/<?= $employee['id'] ?>/reactivate" 
                                                       method="POST" 
                                                       class="d-inline"
                                                       onsubmit="return confirm('¿Estás seguro de que deseas reactivar a este empleado?');">
                                                    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Reactivar">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total'] > $pagination['per_page']): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Page Link -->
                        <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>" <?= $pagination['current_page'] <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                &laquo; Anterior
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link"><?= $i ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Next Page Link -->
                        <li class="page-item <?= $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>" <?= $pagination['current_page'] >= $pagination['last_page'] ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                Siguiente &raquo;
                            </a>
                        </li>
                    </ul>
                    <div class="text-center text-muted small mt-2">
                        Mostrando <?= $pagination['from'] ?> a <?= $pagination['to'] ?> de <?= $pagination['total'] ?> registros
                    </div>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
