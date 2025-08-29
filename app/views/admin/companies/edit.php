<?php 
    // Use unified admin layout: hide top navbar and container
    $hideNavbar = false; 
    $wrapContainer = false; 
    $title = 'Siloe empresas';
    $sidebarTitle = 'Siloe empresas';
    require_once __DIR__ . '/../../partials/header.php'; 
?>

<div class="min-h-screen flex">
    <?php $active = 'companies'; require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <div class="flex-1 p-8">
        <!-- Mobile menu button (Tailwind, matches dashboard) -->
        <div class="lg:hidden mb-4">
            <button type="button" class="px-3 py-2 border rounded text-gray-700" onclick="document.getElementById('adminSidebar').classList.remove('hidden')">☰ Menú</button>
        </div>

        <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Editar Empresa</h1>
        <a href="/admin/companies" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Empresas
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="/admin/companies/<?= $company['id']; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la empresa *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= $_SESSION['old']['name'] ?? $company['name']; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= $_SESSION['old']['address'] ?? $company['address']; ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_email" class="form-label">Correo de contacto</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                               value="<?= $_SESSION['old']['contact_email'] ?? $company['contact_email']; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="contact_phone" class="form-label">Teléfono de contacto</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                               value="<?= $_SESSION['old']['contact_phone'] ?? $company['contact_phone']; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="logo" class="form-label">Logotipo de la empresa</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/png, image/jpeg, image/gif">
                    <?php if ($company['logo']): ?>
                        <div class="mt-2">
                            <small>Logotipo actual:</small><br>
                            <img src="<?= htmlspecialchars(logo_url($company['logo'])); ?>" alt="Logotipo de la empresa" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                           <?= (isset($_SESSION['old']) ? ($_SESSION['old']['is_active'] ?? false) : $company['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Activa</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar Empresa</button>
            </form>
            
            <?php unset($_SESSION['old']); ?>
        </div>
    </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
