<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($company['name']); ?></h1>
        <div>
            <a href="/admin/companies" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Companies
            </a>
            <a href="/admin/companies/<?= $company['id']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Company Details</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 30%">ID:</th>
                            <td><?= $company['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?= htmlspecialchars($company['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Logo:</th>
                            <td>
                                <?php if ($company['logo']): ?>
                                    <img src="<?= htmlspecialchars($company['logo']); ?>" alt="<?= htmlspecialchars($company['name']); ?> Logo" style="max-height: 150px;">
                                <?php else: ?>
                                    <span class="text-muted">No logo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?= nl2br(htmlspecialchars($company['address'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Contact Email:</th>
                            <td><?= htmlspecialchars($company['contact_email'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Contact Phone:</th>
                            <td><?= htmlspecialchars($company['contact_phone'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($company['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($company['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($company['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Company Dashboards</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="/admin/companies/<?= $company['id']; ?>/hr" class="btn btn-primary">
                            <i class="fas fa-users-cog"></i> HR Dashboard
                        </a>
                        <a href="/admin/companies/<?= $company['id']; ?>/employee" class="btn btn-secondary">
                            <i class="fas fa-user-friends"></i> Employee Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Company Users</h5>
            <a href="/admin/users/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add User
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found for this company</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id']; ?></td>
                                    <td><?= htmlspecialchars($user['name']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($user['role'] === 'company_admin'): ?>
                                            <span class="badge bg-warning">Company Admin</span>
                                        <?php elseif ($user['role'] === 'hr'): ?>
                                            <span class="badge bg-info">HR</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Employee</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/users/<?= $user['id']; ?>/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
