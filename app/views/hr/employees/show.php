<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Employee Details</h1>
        <a href="/hr/<?= $company_id ?? '' ?>/employees" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employees
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employee Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($employee['name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
                            <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($employee['role'])) ?></p>
                            <p><strong>Status:</strong> 
                                <?php if ($employee['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Company:</strong> 
                                <?= $company ? htmlspecialchars($company['name']) : 'No company assigned' ?>
                            </p>
                            <p><strong>Created:</strong> 
                                <?= isset($employee['created_at']) ? date('M j, Y g:i A', strtotime($employee['created_at'])) : 'Unknown' ?>
                            </p>
                            <p><strong>Last Login:</strong> 
                                <?= isset($employee['last_login']) && $employee['last_login'] 
                                    ? date('M j, Y g:i A', strtotime($employee['last_login'])) 
                                    : 'Never' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <?php if ($employee['is_active']): ?>
                        <a href="/hr/<?= htmlspecialchars($employee['company_id']) ?>/employees/<?= $employee['id'] ?>/deactivate" class="btn btn-warning btn-sm mb-2 w-100">
                            <i class="fas fa-user-slash"></i> Deactivate Employee
                        </a>
                    <?php else: ?>
                        <form method="POST" action="/hr/employees/<?= $employee['id'] ?>/reactivate" class="mb-2">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-user-check"></i> Reactivate Employee
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="/hr/<?= htmlspecialchars($employee['company_id']) ?>/employees/<?= $employee['id'] ?>/selections" class="btn btn-info btn-sm w-100">
                        <i class="fas fa-utensils"></i> View Menu Selections
                    </a>
                </div>
            </div>
            
            <?php if ($company): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Company Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Company:</strong> <?= htmlspecialchars($company['name']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($company['address'] ?? 'Not provided') ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($company['phone'] ?? 'Not provided') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($company['email'] ?? 'Not provided') ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
