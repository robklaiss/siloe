<?php 
$title = 'Other Menu Items';
require_once __DIR__ . '/../partials/employee_header.php'; 
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Other Menu Items - <?= htmlspecialchars($selectedDate) ?>
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Browse additional menu items that are not part of today's selection.
            </p>
        </div>
        
        <div class="bg-gray-50 px-4 py-5 sm:grid sm:px-6">
            <a href="/menu/select<?= !empty($selectedDate) ? '?date=' . urlencode($selectedDate) : '' ?>" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Menu Selection
            </a>
        </div>

        <div class="px-4 py-5 sm:p-6">
            <?php if (empty($otherItems)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No additional items</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no additional menu items available at this time.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($otherItems as $item): ?>
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <?php if (!empty($item['image_url'])): ?>
                                <img class="w-full h-48 object-cover" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                <div class="h-48 bg-gray-200 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="p-4">
                                <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="mt-1 text-sm text-gray-500"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-lg font-bold text-gray-900">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?= htmlspecialchars(ucfirst($item['category'] ?? 'other')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
