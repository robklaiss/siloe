<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <?php if (!empty($company['logo'])) : ?>
                <img src="<?= htmlspecialchars($company['logo']); ?>" alt="<?= htmlspecialchars($company['name']); ?> Logo" class="img-thumbnail me-3" style="width: 75px; height: 75px; object-fit: contain;">
            <?php endif; ?>
            <h1 class="mb-0"><?= htmlspecialchars($company['name']); ?> - HR Dashboard</h1>
        </div>
        <div>
            <a href="/admin/companies/<?= $company['id']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Company
            </a>
            <a href="/admin/companies" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> All Companies
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
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">HR Navigation</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#employees" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Employee Management
                    </a>
                    <a href="#menu-selections" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2"></i> Menu Selections
                    </a>
                    <a href="#reports" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <a href="#settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Employee Management Section -->
            <div class="card mb-4" id="employees">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Employee Management</h5>
                    <a href="/hr/employees/create" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Employee
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Manage your company's employees here. You can add new employees, deactivate existing ones, or reactivate former employees.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/employees" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users me-2"></i> View All Employees
                            </div>
                            <span class="badge bg-primary rounded-pill">View</span>
                        </a>
                        <a href="/hr/employees/create" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-plus me-2"></i> Add New Employee
                            </div>
                            <span class="badge bg-success rounded-pill">Create</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Menu Selections Section -->
            <div class="card mb-4" id="menu-selections">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employee Menu Selections</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> View and manage employee menu selections. You can see what each employee has selected for their meals.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Today's Selections</h5>
                                    <p class="card-text">View all employee selections for today.</p>
                                    <a href="/hr/menu-selections/today" class="btn btn-primary">View Today's Selections</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Selection History</h5>
                                    <p class="card-text">View historical menu selections by employee.</p>
                                    <a href="/hr/menu-selections/history" class="btn btn-secondary">View History</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="card mb-4" id="reports">
                <div class="card-header">
                    <h5 class="card-title mb-0">Reports</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Generate reports about employee menu selections, preferences, and more.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/reports/selections" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-pie me-2"></i> Menu Selection Reports
                            </div>
                            <span class="badge bg-info rounded-pill">View</span>
                        </a>
                        <a href="/hr/reports/employees" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-chart me-2"></i> Employee Activity Reports
                            </div>
                            <span class="badge bg-info rounded-pill">View</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="card mb-4" id="settings">
                <div class="card-header">
                    <h5 class="card-title mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Configure HR-specific settings for your company.
                    </div>
                    
                    <div class="list-group">
                        <a href="/hr/settings/notifications" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell me-2"></i> Notification Settings
                            </div>
                            <span class="badge bg-secondary rounded-pill">Configure</span>
                        </a>
                        <a href="/hr/settings/preferences" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> HR Preferences
                            </div>
                            <span class="badge bg-secondary rounded-pill">Configure</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
