<?php include VIEWS_PATH . '/partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-calendar-day me-2"></i>
                    Today's Menu Selections
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="/hr/menu-selections/history" class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i>
                            View History
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['flash_message'], $_SESSION['flash_type']); 
                ?>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Selections</h6>
                                    <h3 class="mb-0"><?= count($selections ?? []) ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-utensils fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Date</h6>
                                    <h5 class="mb-0"><?= date('M d, Y', strtotime($date ?? date('Y-m-d'))) ?></h5>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Company</h6>
                                    <h6 class="mb-0"><?= htmlspecialchars($company['name'] ?? 'N/A') ?></h6>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Status</h6>
                                    <h6 class="mb-0">
                                        <?php if (count($selections ?? []) > 0): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Orders</span>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Selections Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Employee Menu Selections for <?= date('F j, Y', strtotime($date ?? date('Y-m-d'))) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($selections)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Email</th>
                                        <th>Menu Item</th>
                                        <th>Quantity</th>
                                        <th>Special Instructions</th>
                                        <th>Order Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selections as $selection): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <?= strtoupper(substr($selection['user_name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <?= htmlspecialchars($selection['user_name'] ?? 'Unknown') ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($selection['user_email'] ?? 'N/A') ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($selection['menu_item_name'] ?? 'N/A') ?></strong>
                                                <?php if (!empty($selection['menu_item_description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($selection['menu_item_description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= (int)($selection['quantity'] ?? 1) ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($selection['special_instructions'])): ?>
                                                    <span class="text-info" title="<?= htmlspecialchars($selection['special_instructions']) ?>">
                                                        <i class="fas fa-comment-alt"></i>
                                                        <?= strlen($selection['special_instructions']) > 30 ? 
                                                            htmlspecialchars(substr($selection['special_instructions'], 0, 30)) . '...' : 
                                                            htmlspecialchars($selection['special_instructions']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($selection['created_at'])): ?>
                                                    <?= date('g:i A', strtotime($selection['created_at'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status = $selection['status'] ?? 'pending';
                                                $badgeClass = match($status) {
                                                    'confirmed' => 'bg-success',
                                                    'preparing' => 'bg-warning',
                                                    'ready' => 'bg-info',
                                                    'delivered' => 'bg-primary',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Menu Selections Today</h5>
                            <p class="text-muted">No employees have made menu selections for today yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Export Options -->
            <?php if (!empty($selections)): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Export Options</h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success" onclick="exportToCSV()">
                                <i class="fas fa-file-csv me-2"></i>
                                Export to CSV
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="printReport()">
                                <i class="fas fa-print me-2"></i>
                                Print Report
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}
</style>

<script>
function exportToCSV() {
    // Simple CSV export functionality
    const table = document.querySelector('table');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'menu_selections_<?= $date ?? date("Y-m-d") ?>.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function printReport() {
    window.print();
}

// Auto-refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);
</script>

<?php include VIEWS_PATH . '/partials/footer.php'; ?>
