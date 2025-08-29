<?php include VIEWS_PATH . '/partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-history me-2"></i>
                    Menu Selections History
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="/hr/menu-selections/today" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-day me-2"></i>
                            Today's Selections
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

            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/hr/menu-selections/history" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="<?= htmlspecialchars($startDate ?? date('Y-m-d', strtotime('-30 days'))) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="<?= htmlspecialchars($endDate ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    Filter Results
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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
                                    <h6 class="card-title">Date Range</h6>
                                    <h6 class="mb-0">
                                        <?= date('M d', strtotime($startDate ?? date('Y-m-d', strtotime('-30 days')))) ?> - 
                                        <?= date('M d, Y', strtotime($endDate ?? date('Y-m-d'))) ?>
                                    </h6>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
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
                                    <h6 class="card-title">Unique Employees</h6>
                                    <h3 class="mb-0">
                                        <?php 
                                        $uniqueEmployees = [];
                                        foreach ($selections ?? [] as $selection) {
                                            if (!empty($selection['employee_id'])) {
                                                $uniqueEmployees[$selection['employee_id']] = true;
                                            }
                                        }
                                        echo count($uniqueEmployees);
                                        ?>
                                    </h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Selections History Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Menu Selections History
                        <?php if (!empty($startDate) && !empty($endDate)): ?>
                            (<?= date('M j, Y', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?>)
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($selections)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="selectionsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
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
                                    <?php 
                                    // Group selections by date for better organization
                                    $selectionsByDate = [];
                                    foreach ($selections as $selection) {
                                        $date = date('Y-m-d', strtotime($selection['selection_date'] ?? $selection['created_at'] ?? date('Y-m-d')));
                                        $selectionsByDate[$date][] = $selection;
                                    }
                                    
                                    // Sort dates in descending order (newest first)
                                    krsort($selectionsByDate);
                                    
                                    foreach ($selectionsByDate as $date => $dateSelections):
                                    ?>
                                        <?php foreach ($dateSelections as $selection): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= date('M j, Y', strtotime($date)) ?></strong>
                                                    <br><small class="text-muted"><?= date('l', strtotime($date)) ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <?= strtoupper(substr($selection['employee_name'] ?? 'U', 0, 1)) ?>
                                                        </div>
                                                        <?= htmlspecialchars($selection['employee_name'] ?? 'Unknown') ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($selection['employee_email'] ?? 'N/A') ?></td>
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
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Menu Selections Found</h5>
                            <p class="text-muted">No menu selections were found for the selected date range.</p>
                            <a href="/hr/menu-selections/history" class="btn btn-outline-primary">
                                <i class="fas fa-refresh me-2"></i>
                                Reset Filters
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Export Options -->
            <?php if (!empty($selections)): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Export & Analysis Options</h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-success" onclick="exportToCSV()">
                                <i class="fas fa-file-csv me-2"></i>
                                Export to CSV
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="printReport()">
                                <i class="fas fa-print me-2"></i>
                                Print Report
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="generateSummary()">
                                <i class="fas fa-chart-bar me-2"></i>
                                Generate Summary
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
    const table = document.getElementById('selectionsTable');
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
    downloadLink.download = 'menu_selections_history_<?= $startDate ?? date("Y-m-d", strtotime("-30 days")) ?>_to_<?= $endDate ?? date("Y-m-d") ?>.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function printReport() {
    window.print();
}

function generateSummary() {
    // Simple summary generation
    const totalSelections = <?= count($selections ?? []) ?>;
    const uniqueEmployees = <?= count($uniqueEmployees ?? []) ?>;
    const dateRange = '<?= date("M j, Y", strtotime($startDate ?? date("Y-m-d", strtotime("-30 days")))) ?> - <?= date("M j, Y", strtotime($endDate ?? date("Y-m-d"))) ?>';
    
    const summary = `
Menu Selections Summary Report
=============================
Date Range: ${dateRange}
Total Selections: ${totalSelections}
Unique Employees: ${uniqueEmployees}
Average Selections per Employee: ${uniqueEmployees > 0 ? (totalSelections / uniqueEmployees).toFixed(2) : 0}

Generated on: ${new Date().toLocaleString()}
    `;
    
    const summaryWindow = window.open('', '_blank');
    summaryWindow.document.write('<pre>' + summary + '</pre>');
    summaryWindow.document.title = 'Menu Selections Summary';
}

// Set max date to today
document.getElementById('end_date').max = new Date().toISOString().split('T')[0];
</script>

<?php include VIEWS_PATH . '/partials/footer.php'; ?>
