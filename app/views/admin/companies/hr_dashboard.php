<?php 
$additionalScripts = ['/js/hr-dashboard.js'];
require_once __DIR__ . '/../../partials/header.php'; 
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <?php if (!empty($company['logo'])) : ?>
                <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logotipo de <?= htmlspecialchars($company['name']); ?>" class="img-thumbnail me-3" style="width: 75px; height: 75px; object-fit: contain;">
            <?php endif; ?>
            <h1 class="mb-0"><?= htmlspecialchars($company['name']); ?> - Panel de RR. HH.</h1>
        </div>
        <div>
            <a href="/admin/companies/<?= $company['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Empresa
            </a>
            <a href="/admin/companies" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Todas las Empresas
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
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Navegación de RR. HH.</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#employees" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Gestión de empleados
                    </a>
                    <a href="#menu-selections" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2"></i> Selecciones de menú
                    </a>
                    <a href="#reports" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i> Informes
                    </a>
                    <a href="#settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </div>
            </div>
            </section>
        </div>

        <div class="col-md-9">
            <!-- Sección de gestión de empleados -->
            <div class="card mb-4" id="employees">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Gestión de empleados</h5>
                    <a href="/admin/companies/<?= $company['id'] ?>/hr/employees/create" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Agregar empleado
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Administre aquí los empleados de su empresa. Puede agregar nuevos empleados, desactivar existentes o reactivar ex empleados.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/<?= $company['id'] ?>/employees" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i> Ver todos los empleados
                            </div>
                            <span class="badge bg-primary rounded-pill">Ver</span>
                        </a>
                        <a href="/hr/<?= $company['id'] ?>/employees/create" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-plus me-2"></i> Agregar nuevo empleado
                            </div>
                            <span class="badge bg-success rounded-pill">Crear</span>
                        </a>
                    </div>
                </div>
            </div>
            </section>

            <!-- Sección de selecciones de menú -->
            <section id="menu-selections" class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Selecciones de menú de empleados</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Vea y gestione las selecciones de menú de los empleados. Puede ver lo que cada empleado ha seleccionado para sus comidas.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Selecciones de hoy</h5>
                                    <p class="card-text">Ver todas las selecciones de los empleados para hoy.</p>
                                    <a href="/admin/companies/<?= $company['id'] ?>/hr/menu-selections/today" class="btn btn-primary">Ver selecciones de hoy</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Historial de selecciones</h5>
                                    <p class="card-text">Ver selecciones históricas de menú por empleado.</p>
                                    <a href="/admin/companies/<?= $company['id'] ?>/hr/menu-selections/history" class="btn btn-secondary">Ver historial</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>

            <!-- Sección de informes -->
            <section id="reports" class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Genere informes sobre selecciones de menú de empleados, preferencias y más.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/reports/selections" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-pie me-2"></i> Informes de selecciones de menú
                            </div>
                            <span class="badge bg-info rounded-pill">Ver</span>
                        </a>
                        <a href="/hr/reports/employees" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-chart me-2"></i> Informes de actividad de empleados
                            </div>
                            <span class="badge bg-info rounded-pill">Ver</span>
                        </a>
                    </div>
                </div>
            </div>
            </section>

            <!-- Sección de configuración -->
            <section id="settings" class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configuración</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Configure ajustes específicos de RR. HH. para su empresa.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/<?= htmlspecialchars($company['id']) ?>/settings/notifications" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell me-2"></i> Configuración de notificaciones
                            </div>
                            <span class="badge bg-secondary rounded-pill">Configurar</span>
                        </a>
                        <a href="/hr/<?= htmlspecialchars($company['id']) ?>/settings/preferences" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> Preferencias de RR. HH.
                            </div>
                            <span class="badge bg-secondary rounded-pill">Configurar</span>
                        </a>
                    </div>
                </div>
            </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
