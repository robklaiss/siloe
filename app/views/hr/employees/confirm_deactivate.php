<?php require_once __DIR__ . '/../../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h2 class="h5 mb-0">Confirm Deactivation</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You are about to deactivate an employee account.
                    </div>
                    
                    <p>Are you sure you want to deactivate <strong><?= htmlspecialchars($employee['name']) ?></strong> (<?= htmlspecialchars($employee['email']) ?>)?</p>
                    
                    <p class="mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        The employee will no longer be able to log in to the system until their account is reactivated.
                    </p>
                    
                    <form action="/hr/employees/<?= $employee['id'] ?>/deactivate" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-user-times me-2"></i> Yes, Deactivate Employee
                        </button>
                    </form>
                    
                    <a href="/hr/employees" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../partials/footer.php'; ?>
