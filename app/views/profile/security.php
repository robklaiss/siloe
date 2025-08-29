<?php include VIEWS_PATH . '/partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-shield-alt me-2"></i>
                    Seguridad de la Cuenta
                </h1>
            </div>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key me-2"></i>
                                Cambiar Contraseña
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/profile/password">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="_method" value="PUT">

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                                    <div class="form-text">La contraseña debe tener al menos 8 caracteres.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-shield me-2"></i>
                                Consejos de Sesión y Seguridad
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Use una contraseña fuerte y única.</li>
                                <li>Cierre sesión en computadoras compartidas.</li>
                                <li>Mantenga su navegador actualizado.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Front-end validation for matching passwords
(function() {
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
})();
</script>

<?php include VIEWS_PATH . '/partials/footer.php'; ?>
