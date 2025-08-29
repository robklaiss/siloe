<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card mt-5">
                <div class="card-header text-center">
                    <?php if (isset($company)): ?>
                        <?php $logoSrc = !empty($company['logo']) ? logo_url($company['logo']) : ''; ?>
                        <?php if ($logoSrc): ?>
                            <div class="mb-2 d-flex justify-content-center">
                                <img class="mx-auto d-block" src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo de <?= htmlspecialchars($company['name']) ?>" style="max-height: 60px; object-fit: contain;">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <h4 class="mb-0">Inicio de sesión de empleados</h4>
                    <?php if (isset($company)): ?>
                        <p class="text-muted mb-0"><?= htmlspecialchars($company['name']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['company_id']) && ($_SESSION['company_id'] ?? null) !== ($company_id ?? null)): ?>
                        <div class="alert alert-warning">
                            Actualmente has iniciado sesión en una empresa diferente.
                            Iniciar sesión aquí cambiará tu sesión a <strong><?= htmlspecialchars($company['name']) ?></strong>.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="/hr/<?= $company_id ?? '' ?>/login" method="POST">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="/login">Volver al inicio de sesión principal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
