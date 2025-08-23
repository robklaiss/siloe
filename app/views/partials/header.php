<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Debug: APP_URL = <?= APP_URL ?> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Área de Administración' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/css/tailwind.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard">Panel de Administración</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/companies">Empresas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/profile">Perfil</a>
                    </li>
                    <li class="nav-item">
                        <form action="/logout" method="POST" style="display: inline;">
                            <button type="submit" class="nav-link btn btn-link">Cerrar sesión</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <?php $companyLogin = isset($_SESSION['company_id']) ? ('/hr/' . $_SESSION['company_id'] . '/login') : '/login'; ?>
                        <a class="nav-link" href="<?= $companyLogin ?>">Iniciar sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/register">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
