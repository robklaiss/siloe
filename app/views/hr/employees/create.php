<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Add New Employee</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error'] ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($company_id)): ?>
                    <form action="/admin/companies/<?= $company_id ?>/hr/employees" method="POST">
                    <?php else: ?>
                    <form action="/hr/employees" method="POST">
                    <?php endif; ?>
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        <?php if (isset($company_id)): ?>
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company_id) ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($_SESSION['errors']['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" required autocomplete="name"
                                   value="<?= htmlspecialchars($_SESSION['old']['name'] ?? '') ?>">
                            <?php if (isset($_SESSION['errors']['name'])): ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION['errors']['name'] ?>
                                </div>
                                <?php unset($_SESSION['errors']['name']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($_SESSION['errors']['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" required autocomplete="email"
                                   value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>">
                            <?php if (isset($_SESSION['errors']['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= $_SESSION['errors']['email'] ?>
                                </div>
                                <?php unset($_SESSION['errors']['email']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password" required autocomplete="new-password">
                                <?php if (isset($_SESSION['errors']['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $_SESSION['errors']['password'] ?>
                                    </div>
                                    <?php unset($_SESSION['errors']['password']); ?>
                                <?php endif; ?>
                                <div class="form-text">At least 8 characters long</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirm" name="password_confirm" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                                <label class="form-check-label" for="send_welcome_email">
                                    Send welcome email with login instructions
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/hr/employees" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Employees
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear old input and errors after displaying them
unset($_SESSION['old']);
unset($_SESSION['errors']);
?>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
