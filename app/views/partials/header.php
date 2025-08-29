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

<?php /* Navigation bar removed as requested */ ?>

<?php if (!isset($wrapContainer) || $wrapContainer): ?>
<div class="container">
<?php endif; ?>
