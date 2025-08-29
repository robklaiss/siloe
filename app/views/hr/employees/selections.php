<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Menu Selections - <?= htmlspecialchars($employee['name']) ?></h1>
        <a href="/hr/<?= htmlspecialchars($employee['company_id']) ?>/employees/<?= $employee['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employee
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

    <!-- Employee Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title"><?= htmlspecialchars($employee['name']) ?></h5>
                    <p class="card-text">
                        <strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?><br>
                        <strong>Status:</strong> 
                        <?php if ($employee['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <!-- Date Range Filter -->
                    <form method="GET" class="d-flex gap-2">
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm">
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Selections Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Menu Selection History</h5>
            <small class="text-muted">Showing selections from <?= date('M j, Y', strtotime($startDate)) ?> to <?= date('M j, Y', strtotime($endDate)) ?></small>
        </div>
        <div class="card-body">
            <?php if (empty($selections)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No menu selections found</h5>
                    <p class="text-muted">This employee hasn't made any menu selections in the selected date range.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Menu Item</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Selected At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selections as $selection): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $menuDate = $selection['menu_date'] ?? $selection['date'] ?? null;
                                        echo $menuDate ? date('M j, Y', strtotime($menuDate)) : 'Unknown';
                                        ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($selection['menu_name'] ?? $selection['name'] ?? 'Unknown') ?></strong>
                                        <?php if (!empty($selection['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($selection['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($selection['category'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($selection['category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($selection['price'])): ?>
                                            $<?= number_format($selection['price'], 2) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $selection['status'] ?? 'confirmed';
                                        $badgeClass = match($status) {
                                            'confirmed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'pending' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
                                    </td>
                                    <td>
                                        <?= isset($selection['created_at']) ? date('M j, Y g:i A', strtotime($selection['created_at'])) : 'Unknown' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
                    <nav aria-label="Menu selections pagination" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Card -->
    <?php if (!empty($selections)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary"><?= count($selections) ?></h4>
                        <small class="text-muted">Total Selections</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success">
                            <?= count(array_filter($selections, fn($s) => ($s['status'] ?? 'confirmed') === 'confirmed')) ?>
                        </h4>
                        <small class="text-muted">Confirmed</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning">
                            <?= count(array_filter($selections, fn($s) => ($s['status'] ?? 'confirmed') === 'pending')) ?>
                        </h4>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-danger">
                            <?= count(array_filter($selections, fn($s) => ($s['status'] ?? 'confirmed') === 'cancelled')) ?>
                        </h4>
                        <small class="text-muted">Cancelled</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
