<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Menu Selection</h1>
    
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
    
    <?php if ($selectionDeadlinePassed): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <p><strong>Note:</strong> The selection deadline for today (<?= date('h:i A', $selectionDeadline) ?>) has passed. You can still view your selection but cannot modify it.</p>
        </div>
    <?php endif; ?>
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Today's Menu Selection</h2>
        
        <?php if (empty($menuItems)): ?>
            <p class="text-gray-700">No menu items are available for today.</p>
        <?php else: ?>
            <?php if ($hasSelectedForToday): ?>
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
                    <h3 class="font-semibold text-blue-800">Your Current Selection</h3>
                    <p class="mt-2">
                        <span class="font-medium">Selected Item:</span> 
                        <?= htmlspecialchars($currentSelection['menu_item_name']); ?>
                    </p>
                    <?php if (!empty($currentSelection['notes'])): ?>
                        <p class="mt-1">
                            <span class="font-medium">Notes:</span> 
                            <?= htmlspecialchars($currentSelection['notes']); ?>
                        </p>
                    <?php endif; ?>
                    <p class="mt-1 text-sm text-gray-600">
                        Selected on: <?= date('M d, Y h:i A', strtotime($currentSelection['created_at'])); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!$selectionDeadlinePassed): ?>
                <form action="/menu/select" method="POST" class="mt-4">
                    <input type="hidden" name="_token" value="<?= $csrf_token; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="menu_item_id">
                            Select Menu Item:
                        </label>
                        <select name="menu_item_id" id="menu_item_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">-- Select an item --</option>
                            <?php foreach ($menuItems as $item): ?>
                                <option value="<?= $item['id']; ?>" <?= ($hasSelectedForToday && $currentSelection['menu_item_id'] == $item['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($item['name']); ?> - <?= htmlspecialchars($item['description']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
                            Special Instructions (Optional):
                        </label>
                        <textarea name="notes" id="notes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"><?= $hasSelectedForToday ? htmlspecialchars($currentSelection['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <?= $hasSelectedForToday ? 'Update Selection' : 'Submit Selection'; ?>
                        </button>
                        <p class="text-sm text-gray-600">
                            Deadline: <?= date('h:i A', $selectionDeadline); ?> today
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Upcoming Menus</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Available Menu Items
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingMenus as $date => $menuData): ?>
                        <tr>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <?= date('l, M d', strtotime($date)); ?>
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <?php if (empty($menuData['items'])): ?>
                                    <span class="text-gray-500">No menu items available yet</span>
                                <?php else: ?>
                                    <ul class="list-disc pl-5">
                                        <?php foreach ($menuData['items'] as $item): ?>
                                            <li><?= htmlspecialchars($item['name']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="/menu/my-selections" class="text-blue-500 hover:text-blue-700">
            View My Selection History
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
