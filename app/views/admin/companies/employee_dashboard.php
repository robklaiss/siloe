<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <?php if (!empty($company['logo'])) : ?>
                <img src="<?= htmlspecialchars($company['logo']); ?>" alt="<?= htmlspecialchars($company['name']); ?> Logo" class="img-thumbnail me-3" style="width: 75px; height: 75px; object-fit: contain;">
            <?php endif; ?>
            <h1 class="mb-0"><?= htmlspecialchars($company['name']); ?> - Employee Dashboard</h1>
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
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Employee Navigation</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#menu-selection" class="list-group-item list-group-item-action">
                        <i class="fas fa-utensils me-2"></i> Menu Selection
                    </a>
                    <a href="#my-selections" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2"></i> My Selection History
                    </a>
                    <a href="#profile" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action">
                        <i class="fas fa-bell me-2"></i> Notifications
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Menu Selection Section -->
            <div class="card mb-4" id="menu-selection">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu Selection</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Employees can select their daily menu items here. They can choose from available options for the day.
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Today's Menu Selection</h5>
                            <p class="card-text">Employees can select their meal for today from available options.</p>
                            <a href="/menu/select" class="btn btn-primary">Go to Menu Selection</a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Selection Process:</h6>
                        <ol>
                            <li>Employees log in to their account</li>
                            <li>Navigate to the Menu Selection page</li>
                            <li>View available menu items for the day</li>
                            <li>Select their preferred meal</li>
                            <li>Submit their selection before the daily deadline (typically 10:00 AM)</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- My Selections Section -->
            <div class="card mb-4" id="my-selections">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Selection History</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Employees can view their past menu selections here.
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">View Selection History</h5>
                            <p class="card-text">Employees can see what they've selected in the past and track their preferences.</p>
                            <a href="/menu/my-selections" class="btn btn-secondary">View My Selections</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div class="card mb-4" id="profile">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employee Profile</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Employees can view and update their profile information here.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Personal Information</h5>
                                    <p class="card-text">Update name, contact details, and other personal information.</p>
                                    <a href="/profile" class="btn btn-info">Edit Profile</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Password & Security</h5>
                                    <p class="card-text">Change password and manage security settings.</p>
                                    <a href="/profile/security" class="btn btn-warning">Security Settings</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="card mb-4" id="notifications">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Employees can view notifications and manage their notification preferences.
                    </div>
                    
                    <div class="list-group">
                        <a href="/notifications" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell me-2"></i> View All Notifications
                            </div>
                            <span class="badge bg-primary rounded-pill">View</span>
                        </a>
                        <a href="/notifications/settings" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog me-2"></i> Notification Settings
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
