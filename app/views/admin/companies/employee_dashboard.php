<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <?php if (!empty($company['logo'])) : ?>
                <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logotipo de <?= htmlspecialchars($company['name']); ?>" class="img-thumbnail me-3" style="width: 75px; height: 75px; object-fit: contain;">
            <?php endif; ?>
            <div>
                <h1 class="mb-0"><?= htmlspecialchars($company['name']); ?> - Panel de empleados</h1>
                <?php if (isset($employee) && !empty($employee['name'])): ?>
                    <p class="lead mb-0">¡Bienvenido(a), <?= htmlspecialchars($employee['name']); ?>!</p>
                <?php endif; ?>
            </div>
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
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Navegación de empleados</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#menu-selection" class="list-group-item list-group-item-action">
                        <i class="fas fa-utensils me-2"></i> Selección de menú
                    </a>
                    <a href="#my-selections" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2"></i> Mi historial de selecciones
                    </a>
                    <a href="#profile" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i> Perfil
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action">
                        <i class="fas fa-bell me-2"></i> Notificaciones
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Sección de selección de menú -->
            <div class="card mb-4" id="menu-selection">
                <div class="card-header">
                    <h5 class="card-title mb-0">Selección de menú</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Aquí los empleados pueden seleccionar sus platos del menú diario. Pueden elegir entre las opciones disponibles para el día.
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Selección de menú de hoy</h5>
                            <p class="card-text">Los empleados pueden seleccionar su comida de hoy entre las opciones disponibles.</p>
                            <a href="/menu/select" class="btn btn-primary">Ir a Selección de menú</a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Proceso de selección:</h6>
                        <ol>
                            <li>Los empleados inician sesión en su cuenta</li>
                            <li>Navegan a la página de Selección de menú</li>
                            <li>Ven los platos disponibles del día</li>
                            <li>Seleccionan su comida preferida</li>
                            <li>Envían su selección antes de la hora límite diaria (normalmente 10:00 AM)</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Sección Mi historial de selecciones -->
            <div class="card mb-4" id="my-selections">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mi historial de selecciones</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Aquí los empleados pueden ver sus selecciones de menú anteriores.
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ver historial de selecciones</h5>
                            <p class="card-text">Los empleados pueden ver lo que han seleccionado en el pasado y seguir sus preferencias.</p>
                            <a href="/menu/my-selections" class="btn btn-secondary">Ver mis selecciones</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Perfil -->
            <div class="card mb-4" id="profile">
                <div class="card-header">
                    <h5 class="card-title mb-0">Perfil del empleado</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Aquí los empleados pueden ver y actualizar su información de perfil.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Información personal</h5>
                                    <p class="card-text">Actualizar nombre, datos de contacto y otra información personal.</p>
                                    <a href="/profile" class="btn btn-info">Editar perfil</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Contraseña y seguridad</h5>
                                    <p class="card-text">Cambiar contraseña y administrar ajustes de seguridad.</p>
                                    <a href="/profile/security" class="btn btn-warning">Configuración de seguridad</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Notificaciones -->
            <div class="card mb-4" id="notifications">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notificaciones</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Los empleados pueden ver notificaciones y administrar sus preferencias de notificación.
                    </div>
                    
                    <div class="list-group">
                        <a href="/notifications" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell me-2"></i> Ver todas las notificaciones
                            </div>
                            <span class="badge bg-primary rounded-pill">Ver</span>
                        </a>
                        <a href="/notifications/settings" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> Configuración de notificaciones
                            </div>
                            <span class="badge bg-secondary rounded-pill">Configurar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
