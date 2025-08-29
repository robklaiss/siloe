<?php
/**
 * Simple Index Page for Siloe
 * 
 * This is a simplified index page that will work as a temporary solution
 * until the full application can be fixed properly.
 */

// Start the session at the very beginning before any output
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Define constants
define('APP_NAME', 'Siloe');
define('APP_URL', 'https://www.siloe.com.py');
define('APP_ROOT', dirname(__FILE__));

// Create a simple HTML page
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            padding-top: 2rem;
        }
        .hero {
            background-color: #f8f9fa;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .feature-box {
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            background-color: #f8f9fa;
            height: 100%;
        }
        .footer {
            margin-top: 3rem;
            padding: 2rem 0;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero text-center">
            <img src="/uploads/logos/684ddb17c8715-siloe-logo.jpg" alt="<?php echo APP_NAME; ?> Logo" class="logo">
            <h1>Bienvenido a <?php echo APP_NAME; ?></h1>
            <p class="lead">Sistema de gestión de almuerzos empresariales</p>
            <div class="mt-4">
                <a href="/login" class="btn btn-primary">Iniciar Sesión</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <h3>Gestión de Empresas</h3>
                    <p>Administre múltiples empresas y sus empleados desde una sola plataforma.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <h3>Menú Semanal</h3>
                    <p>Planifique y publique menús semanales para que los empleados puedan elegir sus almuerzos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <h3>Reportes</h3>
                    <p>Genere informes detallados sobre las selecciones de almuerzos y costos por empresa.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <p>¿Ya tiene una cuenta? <a href="/login">Inicie sesión aquí</a></p>
            <p>¿Problemas para acceder? Contacte al administrador del sistema.</p>
        </div>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
