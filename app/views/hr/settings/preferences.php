<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>HR Preferences</h1>
        <a href="/admin/companies/<?= htmlspecialchars($company['id']) ?>/hr" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to HR Dashboard
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
                    <h5 class="card-title mb-0">Display Preferences</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/hr/<?= htmlspecialchars($company['id']) ?>/settings/preferences">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <label for="items_per_page" class="form-label">Items per page</label>
                            <select class="form-select" id="items_per_page" name="items_per_page">
                                <option value="10">10</option>
                                <option value="15" selected>15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <div class="form-text">Number of items to display per page in listings</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="date_format" name="date_format">
                                <option value="Y-m-d" selected>2024-01-15</option>
                                <option value="m/d/Y">01/15/2024</option>
                                <option value="d/m/Y">15/01/2024</option>
                                <option value="M j, Y">Jan 15, 2024</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="default_view" class="form-label">Default Dashboard View</label>
                            <select class="form-select" id="default_view" name="default_view">
                                <option value="overview" selected>Overview</option>
                                <option value="employees">Employee List</option>
                                <option value="selections">Today's Selections</option>
                                <option value="reports">Reports</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_refresh" name="auto_refresh">
                                <label class="form-check-label" for="auto_refresh">
                                    <strong>Auto-refresh dashboard</strong>
                                    <div class="text-muted small">Automatically refresh dashboard data every 5 minutes</div>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Report Preferences</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/hr/<?= htmlspecialchars($company['id']) ?>/settings/preferences">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="section" value="reports">
                        
                        <div class="mb-3">
                            <label for="report_period" class="form-label">Default Report Period</label>
                            <select class="form-select" id="report_period" name="report_period">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_inactive" name="include_inactive">
                                <label class="form-check-label" for="include_inactive">
                                    Include inactive employees in reports
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Report Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preferences Info</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Customize your HR dashboard experience and default settings.
                    </div>
                    
                    <h6>Current Settings:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-list me-2"></i> 15 items per page</li>
                        <li><i class="fas fa-calendar me-2"></i> YYYY-MM-DD date format</li>
                        <li><i class="fas fa-home me-2"></i> Overview default view</li>
                        <li><i class="fas fa-chart-bar me-2"></i> 30-day report period</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/hr/<?= htmlspecialchars($company['id']) ?>/employees" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-users"></i> View Employees
                    </a>
                    <a href="/hr/<?= htmlspecialchars($company['id']) ?>/menu-selections/today" class="btn btn-outline-info btn-sm w-100 mb-2">
                        <i class="fas fa-utensils"></i> Today's Selections
                    </a>
                    <a href="/hr/<?= htmlspecialchars($company['id']) ?>/menu-selections/history" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-history"></i> Selection History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
