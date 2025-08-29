<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Notification Settings</h1>
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
                    <h5 class="card-title mb-0">Email Notifications</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/hr/<?= htmlspecialchars($company['id']) ?>/settings/notifications">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="daily_summary" name="daily_summary" checked>
                                <label class="form-check-label" for="daily_summary">
                                    <strong>Daily Summary</strong>
                                    <div class="text-muted small">Receive daily summary of menu selections</div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="new_employee" name="new_employee" checked>
                                <label class="form-check-label" for="new_employee">
                                    <strong>New Employee Registration</strong>
                                    <div class="text-muted small">Notify when new employees register</div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="menu_changes" name="menu_changes" checked>
                                <label class="form-check-label" for="menu_changes">
                                    <strong>Menu Changes</strong>
                                    <div class="text-muted small">Notify when menu items are updated</div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="low_selections" name="low_selections">
                                <label class="form-check-label" for="low_selections">
                                    <strong>Low Selection Alerts</strong>
                                    <div class="text-muted small">Alert when daily selections are below threshold</div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="notification_time" class="form-label">Daily Summary Time</label>
                            <select class="form-select" id="notification_time" name="notification_time">
                                <option value="08:00">8:00 AM</option>
                                <option value="09:00" selected>9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="18:00">6:00 PM</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notification Info</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure when and how you receive notifications about HR activities.
                    </div>
                    
                    <h6>Current Settings:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i> Daily summaries enabled</li>
                        <li><i class="fas fa-check text-success me-2"></i> New employee alerts enabled</li>
                        <li><i class="fas fa-check text-success me-2"></i> Menu change notifications enabled</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
