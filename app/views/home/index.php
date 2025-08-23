<?php
/**
 * Public Homepage View
 * Expects:
 * - $title (string)
 * - $logo_url (string) optional
 */
$defaultLogoPath = 'uploads/logos/684ddb17c8715-siloe-logo.jpg';
$logo = !empty($logo_url)
    ? $logo_url
    : (function_exists('asset') ? asset($defaultLogoPath) : ('/' . ltrim($defaultLogoPath, '/')));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --dark: #212529;
        }
        html, body { height: 100%; }
        body {
            margin: 0;
            min-height: 100dvh;
            background: linear-gradient(135deg, #f8f9fa 0%, #eaf4ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .hero-card {
            width: 100%;
            max-width: 720px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1rem 2rem rgba(13, 110, 253, 0.08);
            overflow: hidden;
        }
        .hero-header {
            background: linear-gradient(135deg, rgba(13,110,253,0.06), rgba(13,110,253,0.02));
            padding: 2rem 2rem 0.75rem;
            text-align: center;
        }
        .brand-logo {
            max-height: 80px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        .hero-body { padding: 1.5rem 2rem 2rem; }
        .cta-group .btn { min-width: 140px; }
        .small-links a { text-decoration: none; }
    </style>
</head>
<body>
    <main class="container px-3 px-sm-4">
        <section class="hero-card">
            <div class="hero-header">
                <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars(APP_NAME) ?>" class="brand-logo mb-3">
                <h1 class="h3 mb-1"><?= htmlspecialchars(APP_NAME) ?></h1>
                <p class="text-muted mb-0">Gestión de pedidos de almuerzos para empresas</p>
            </div>
            <div class="hero-body text-center">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <p class="lead mb-4">Inicie sesión para acceder al panel o regístrese para crear una cuenta.</p>
                <div class="cta-group d-flex flex-wrap gap-2 justify-content-center mb-3">
                    <a href="/login" class="btn btn-primary">Iniciar sesión</a>
                    <a href="/register" class="btn btn-outline-primary">Registrarse</a>
                </div>
                <div class="small-links text-muted">
                    <?php $companyLogin = isset($_SESSION['company_id']) ? ('/hr/' . (int)$_SESSION['company_id'] . '/login') : '/login'; ?>
                    <small>¿Empleado de empresa? <a href="<?= htmlspecialchars($companyLogin) ?>">Ingresar aquí</a></small>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
