<?php include VIEWS_PATH . '/partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user me-2"></i>
                    Mi Perfil
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="/profile/edit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Editar Perfil
                        </a>
                        <a href="/profile/security" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-lock me-1"></i> Configuración de Seguridad
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                Información del Perfil
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Nombre:</div>
                                <div class="col-md-8"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Email:</div>
                                <div class="col-md-8"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Rol:</div>
                                <div class="col-md-8"><?= htmlspecialchars(ucfirst($user['role'] ?? 'Empleado')) ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Empresa:</div>
                                <div class="col-md-8"><?= htmlspecialchars($user['company_name'] ?? 'N/A') ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Cuenta Creada:</div>
                                <div class="col-md-8">
                                    <?php if (isset($user['created_at'])): ?>
                                        <?= date('F j, Y', strtotime($user['created_at'])) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="/profile/edit" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                Editar Perfil
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                Seguridad de la Cuenta
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>
                                <i class="fas fa-info-circle me-2 text-info"></i>
                                Administre la configuración de seguridad de su cuenta, incluyendo la contraseña.
                            </p>
                            <a href="/profile/security" class="btn btn-warning w-100">
                                <i class="fas fa-lock me-2"></i>
                                Configuración de Seguridad
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include VIEWS_PATH . '/partials/footer.php'; ?>
