<?php include VIEWS_PATH . '/partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Perfil
                </h1>
            </div>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['flash_message'], $_SESSION['flash_type']); 
                ?>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Information -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>
                                Información del Perfil
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/profile">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="_method" value="PUT">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre Completo</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= htmlspecialchars($user['name'] ?? ($_SESSION['old']['name'] ?? '')) ?>" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($user['email'] ?? ($_SESSION['old']['email'] ?? '')) ?>" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars(ucfirst($user['role'] ?? 'Empleado')) ?>" 
                                           readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($user['company_name'] ?? 'N/A') ?>" 
                                           readonly>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Perfil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lock me-2"></i>
                                Cambiar Contraseña
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/profile/password">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="_method" value="PUT">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Contraseña Actual</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="new_password" 
                                           name="new_password" 
                                           minlength="8" 
                                           required>
                                    <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           minlength="8" 
                                           required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>
                                    Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php 
// Clear old form data
unset($_SESSION['old']);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>

<?php include VIEWS_PATH . '/partials/footer.php'; ?>
