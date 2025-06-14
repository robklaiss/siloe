<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">My Menu Selections</h1>
    
    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash']['error']; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['flash']['success']; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Selection History</h2>
            <a href="/menu/select" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Make New Selection
            </a>
        </div>
        
        <?php if (empty($selections['data'])): ?>
            <p class="text-gray-700">You haven't made any menu selections yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Menu Item
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Notes
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Selected On
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selections['data'] as $selection): ?>
                            <tr>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?= date('M d, Y', strtotime($selection['selection_date'])); ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?= htmlspecialchars($selection['menu_item_name']); ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?= !empty($selection['notes']) ? htmlspecialchars($selection['notes']) : '<span class="text-gray-500">None</span>'; ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                        switch ($selection['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'confirmed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?= ucfirst($selection['status']); ?>
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?= date('M d, Y h:i A', strtotime($selection['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($selections['pagination']['total_pages'] > 1): ?>
                <div class="mt-4 flex justify-center">
                    <nav class="inline-flex rounded-md shadow-sm">
                        <?php if ($selections['pagination']['current_page'] > 1): ?>
                            <a href="?page=<?= $selections['pagination']['current_page'] - 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                Previous
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $selections['pagination']['total_pages']; $i++): ?>
                            <?php if ($i == $selections['pagination']['current_page']): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                    <?= $i ?>
                                </span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($selections['pagination']['current_page'] < $selections['pagination']['total_pages']): ?>
                            <a href="?page=<?= $selections['pagination']['current_page'] + 1 ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Next
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                Next
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Date Filter Form -->
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h3 class="text-lg font-semibold mb-4">Filter by Date</h3>
        <form action="/menu/my-selections" method="GET" class="flex flex-wrap items-end">
            <div class="mr-4 mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                    Start Date:
                </label>
                <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mr-4 mb-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
                    End Date:
                </label>
                <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
