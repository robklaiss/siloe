<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Company</h1>
        <a href="/admin/companies" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Companies
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
                    <label for="name" class="form-label">Company Name *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= $_SESSION['old']['name'] ?? $company['name']; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= $_SESSION['old']['address'] ?? $company['address']; ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                               value="<?= $_SESSION['old']['contact_email'] ?? $company['contact_email']; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                               value="<?= $_SESSION['old']['contact_phone'] ?? $company['contact_phone']; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="logo" class="form-label">Company Logo</label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/png, image/jpeg, image/gif">
                    <?php if ($company['logo']): ?>
                        <div class="mt-2">
                            <small>Current logo:</small><br>
                            <img src="<?= $company['logo']; ?>" alt="Company Logo" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                           <?= (isset($_SESSION['old']) ? ($_SESSION['old']['is_active'] ?? false) : $company['is_active']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Company</button>
            </form>
            
            <?php unset($_SESSION['old']); ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
