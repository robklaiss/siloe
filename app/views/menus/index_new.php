<?php
// Debug output to verify this view is being used
echo "<!-- DEBUG: Using index_new.php view file -->";
echo "<!-- DEBUG: Menu count: " . count($menus) . " -->";
?>
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Menu Management</h2>
                <a href="/menus/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Add New Menu Item
                </a>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Menu Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bundled Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price (PYG)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($menus)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No menu items found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menus as $menu): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($menu['id']) ?></td>
                                    
                                    <!-- Menu Details -->
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($menu['name']) ?></div>
                                        <div class="text-gray-500 text-xs mt-1"><?= htmlspecialchars($menu['description']) ?></div>
                                        <div class="text-gray-400 text-xs mt-1">
                                            Base Price: ₲<?= number_format($menu['price'], 0, ',', '.') ?>
                                        </div>
                                        <?php if (!empty($menu['date'])): ?>
                                            <div class="text-gray-400 text-xs">
                                                Date: <?= htmlspecialchars(date('Y-m-d', strtotime($menu['date']))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Bundled Items -->
                                    <td class="px-6 py-4 text-sm">
                                        <?php if (!empty($menu['bundled_items'])): ?>
                                            <div class="space-y-2">
                                                <?php if (!empty($menu['bundled_items']['main_dishes'])): ?>
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Main Dishes</span>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['main_dishes'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($menu['bundled_items']['beverages'])): ?>
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Beverages</span>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['beverages'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($menu['bundled_items']['desserts'])): ?>
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Desserts</span>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['desserts'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($menu['bundled_items']['other'])): ?>
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Other</span>
                                                        <div class="mt-1 text-xs text-gray-600">
                                                            <?php foreach ($menu['bundled_items']['other'] as $item): ?>
                                                                <div>• <?= htmlspecialchars($item['name']) ?> (₲<?= number_format($item['price'], 0, ',', '.') ?>)</div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">No bundled items</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Total Price -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-semibold text-gray-900">
                                            ₲<?= number_format($menu['total_price'], 0, ',', '.') ?>
                                        </div>
                                        <div class="text-xs text-gray-400">Incl. 10% I.V.A.</div>
                                        <?php if ($menu['total_price'] > $menu['price']): ?>
                                            <div class="text-xs text-green-600 mt-1">
                                                +₲<?= number_format($menu['total_price'] - $menu['price'], 0, ',', '.') ?> from bundles
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Available Status -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($menu['available']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="/menus/<?= htmlspecialchars($menu['id']) ?>/edit" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button type="button" class="text-red-600 hover:text-red-900 delete-btn" data-id="<?= htmlspecialchars($menu['id']) ?>">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
