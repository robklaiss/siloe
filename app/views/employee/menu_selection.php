<?php 
// Layout will provide the full HTML structure and title via controller data.
?>
<script>
    // Expose company context to client-side JS for redirects
    window.COMPANY_ID = <?= isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : 'null' ?>;
</script>
<?php 

/**
 * Renders a menu item card
 */
function renderMenuItem($item, $userSelection, $isDeadlinePassed, $date, $csrf_token) {
    $isSelected = $userSelection && $userSelection['menu_item_id'] == $item['id'];
    $orderNotes = $isSelected ? ($userSelection['special_requests'] ?? '') : '';
    
    ob_start();
    ?>
    <div class="group relative flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150 <?= $isSelected ? 'bg-green-50 border-green-200' : '' ?>">
        <!-- Menu Item Details -->
        <div class="flex-1 min-w-0 pr-2">
            <div class="flex justify-between items-start">
                <h4 class="text-sm font-medium text-gray-900 truncate">
                    <?= htmlspecialchars($item['name']) ?>
                    <?php if ($isSelected): ?>
                        <span class="ml-2 text-green-600">(Selected)</span>
                    <?php endif; ?>
                </h4>
                <span class="text-sm font-medium text-blue-600 whitespace-nowrap ml-2">₲<?= number_format($item['price'], 0, ',', '.') ?></span>
            </div>
            <?php if (!empty($item['description'])): ?>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
            <?php endif; ?>
        </div>
        
        <?php if (!$isDeadlinePassed): ?>
            <form action="/menu/select" method="POST" class="flex-shrink-0 menu-item-form" data-date="<?= $date ?>">
                <input type="hidden" name="menu_item_id" value="<?= $item['id'] ?>">
                <input type="hidden" name="selection_date" value="<?= $date ?>">
                <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white <?= $isSelected ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' ?> focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-150" title="<?= $isSelected ? 'Change selection' : 'Select this item' ?>">
                    <?php if ($isSelected): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 00-1 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 10-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 100-2H5.999a5 5 0 10-1 9.9V17a1 1 0 102 0v-2.101a7.002 7.002 0 0111.601-2.566 1 1 0 10-1.885-.666A5.002 5.002 0 007.999 9H5a1 1 0 100 2h3a1 1 0 011 1v3a1 1 0 11-2 0v-1.101a7.002 7.002 0 01-6-6.899V3a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    <?php endif; ?>
                    <span class="sr-only"><?= $isSelected ? 'Change selection' : 'Select this item' ?></span>
                </button>
            </form>
        <?php elseif ($isSelected): ?>
            <div class="flex-shrink-0 ml-2 flex items-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Selected
                </span>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Weekly Menu Selection</h1>
            <p class="mt-1 text-sm text-gray-600">Select your meals for the upcoming week</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="/menu/my-selections" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                </svg>
                View My Selections
            </a>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <?= htmlspecialchars($_SESSION['flash']['success']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>
    
    <!-- Week Navigation -->
    <div class="flex items-center justify-between mb-6">
        <button id="prev-week" class="p-2 rounded-full hover:bg-gray-100">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h2 class="text-lg font-medium text-gray-900">
            <?= date('F j', strtotime('monday this week')) ?> - 
            <?= date('j, Y', strtotime('sunday this week')) ?>
        </h2>
        <button id="next-week" class="p-2 rounded-full hover:bg-gray-100">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Week Days Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        // Generate dates for the current week (Monday to Sunday)
        $weekDates = [];
        $currentDate = new DateTime('monday this week');
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $isToday = $dateStr === date('Y-m-d');
            $isPast = $dateStr < date('Y-m-d');
            $isFuture = $dateStr > date('Y-m-d');
            
            $weekDates[] = [
                'date' => $dateStr,
                'day' => $currentDate->format('l'),
                'formatted_date' => $currentDate->format('M j'),
                'is_today' => $isToday,
                'is_past' => $isPast,
                'is_future' => $isFuture
            ];
            $currentDate->modify('+1 day');
        }
        
        foreach ($weekDates as $day): 
            $menuItems = $menuModel->getAvailableMenuItems($day['date']);
            $userSelection = $selectionModel->getSelectionByUserAndDate($currentUser['id'], $day['date']);
            $isDeadlinePassed = strtotime('now') >= strtotime($day['date'] . ' 10:00 AM');
        ?>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <?= $day['day'] ?>
                            <span class="text-sm font-normal text-gray-500"><?= date('M j', strtotime($day['date'])) ?></span>
                        </h3>
                        <?php if (isset($day['selection_status'])): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <?= ucfirst($day['selection_status']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($day['is_today']): ?>
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Today</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($userSelection) && isset($userSelection['menu_item_name'])): ?>
                    <p class="mt-1 text-sm text-green-600">
                        <span class="font-medium">Selected:</span> 
                        <?= htmlspecialchars($userSelection['menu_item_name']) ?>
                    </p>
                <?php elseif ($isDeadlinePassed): ?>
                    <p class="mt-1 text-sm text-red-500">Selection closed</p>
                <?php else: ?>
                    <p class="mt-1 text-sm text-gray-500">No selection made</p>
                <?php endif; ?>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <?php 
                $hasDailyItems = !empty($menuItems['daily']);
                $hasRegularItems = !empty($menuItems['regular']);
                $hasAnyItems = $hasDailyItems || $hasRegularItems;
                ?>
                
                <?php if ($hasAnyItems): ?>
                    <?php if (!$isDeadlinePassed): ?>
                        <!-- Show main menu items when deadline hasn't passed -->
                        <div class="space-y-3">
                            <?php if ($hasDailyItems): ?>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Today's Lunch Specials</h4>
                                <div class="space-y-2 mb-4">
                                    <?php 
                                    // Show only the first daily item initially
                                    $firstItem = reset($menuItems['daily']);
                                    echo renderMenuItem($firstItem, $userSelection, $isDeadlinePassed, $day['date'], $csrf_token);
                                    
                                    // If there are more items, show a "View Full Menu" button
                                    if (count($menuItems['daily']) > 1): ?>
                                        <button type="button" 
                                                onclick="toggleDailyMenu('<?= $day['date'] ?>')" 
                                                class="w-full mt-2 text-center text-sm text-blue-600 hover:text-blue-800 focus:outline-none">
                                            View Full Menu
                                        </button>
                                        <div id="daily-menu-<?= $day['date'] ?>" class="hidden space-y-2">
                                            <?php 
                                            // Skip the first item as it's already shown
                                            $first = true;
                                            foreach ($menuItems['daily'] as $item): 
                                                if ($first) {
                                                    $first = false;
                                                    continue;
                                                }
                                                echo renderMenuItem($item, $userSelection, $isDeadlinePassed, $day['date'], $csrf_token);
                                            endforeach; 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($hasRegularItems): ?>
                                <h4 class="text-sm font-medium text-gray-900 mt-4 mb-2">Always Available</h4>
                                <div class="space-y-2">
                                    <?php 
                                    // Show only first 2 regular items by default
                                    $regularItems = array_slice($menuItems['regular'], 0, 2);
                                    $hasMoreItems = count($menuItems['regular']) > 2;
                                    // Compute remaining items to show in the modal only
                                    $additionalRegularItems = array_slice($menuItems['regular'], 2);
                                    ?>
                                    <?php foreach ($regularItems as $item): ?>
                                        <?= renderMenuItem($item, $userSelection, $isDeadlinePassed, $day['date'], $csrf_token) ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($hasMoreItems): ?>
                                        <button 
                                            onclick="openAdditionalItemsModal('<?= $day['date'] ?>')" 
                                            class="w-full text-center py-2 text-sm font-medium text-blue-600 hover:text-blue-800 focus:outline-none"
                                        >
                                            + <?= (count($menuItems['regular']) - 2) ?> more items available
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <button 
                                    onclick="openAdditionalItemsModal('<?= $day['date'] ?>')" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add More Items
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hidden data for modal -->
                        <div id="menu-data-<?= str_replace('-', '', $day['date']) ?>" 
                             data-date="<?= $day['date'] ?>" 
                             data-regular-items='<?= json_encode($additionalRegularItems ?? []) ?>'
                             data-csrf-token="<?= $csrf_token ?>"
                             style="display: none;">
                        </div>
                    <?php else: ?>
                        <!-- Show menu items without interaction when deadline has passed -->
                        <div class="space-y-3">
                            <?php if ($hasDailyItems): ?>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Today's Lunch Specials</h4>
                                <div class="space-y-2">
                                    <?php foreach ($menuItems['daily'] as $item): ?>
                                        <?= renderMenuItem($item, $userSelection, true, $day['date'], '') ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($hasRegularItems): ?>
                                <h4 class="text-sm font-medium text-gray-900 mt-4 mb-2">Always Available</h4>
                                <div class="space-y-2">
                                    <?php 
                                    // Show only first 2 regular items by default
                                    $regularItems = array_slice($menuItems['regular'], 0, 2);
                                    $hasMoreItems = count($menuItems['regular']) > 2;
                                    ?>
                                    <?php foreach ($regularItems as $item): ?>
                                        <?= renderMenuItem($item, $userSelection, true, $day['date'], '') ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="/menu/other-items?date=<?= $day['date'] ?>" class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    View Full Menu
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-500 mb-3">No lunch specials available for this day.</p>
                        <a href="/menu/other-items?date=<?= $day['date'] ?>" class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            View Full Menu
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- JavaScript for Menu Selection -->
    <script>
        // Function to toggle menu visibility
        function toggleMenu(date) {
            const menuItems = document.getElementById(`menu-items-${date}`);
            const menuToggle = document.getElementById(`menu-toggle-${date}`);
            
            if (menuItems && menuToggle) {
                if (menuItems.classList.contains('hidden')) {
                    menuItems.classList.remove('hidden');
                    menuToggle.classList.add('hidden');
                } else {
                    menuItems.classList.add('hidden');
                    menuToggle.classList.remove('hidden');
                }
            }
        }
        
        // Close all open menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.menu-item-form') && !event.target.closest('[onclick^="toggleMenu"]')) {
                document.querySelectorAll('[id^="menu-items-"]').forEach(menu => {
                    if (!menu.classList.contains('hidden')) {
                        const date = menu.id.replace('menu-items-', '');
                        const toggle = document.getElementById(`menu-toggle-${date}`);
                        if (toggle) {
                            menu.classList.add('hidden');
                            toggle.classList.remove('hidden');
                        }
                    }
                });
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            // Show flash messages as toast notifications
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-4 rounded-md shadow-lg text-white ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} z-50`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                // Remove toast after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            }
            
            // Handle menu item form submissions
            const forms = document.querySelectorAll('.menu-item-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonContent = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    `;
                    
                    // Close all other open menus
                    document.querySelectorAll('[id^="menu-items-"]').forEach(menu => {
                        if (!menu.classList.contains('hidden')) {
                            const date = menu.id.replace('menu-items-', '');
                            const toggle = document.getElementById(`menu-toggle-${date}`);
                            if (toggle) {
                                menu.classList.add('hidden');
                                toggle.classList.remove('hidden');
                            }
                        }
                    });

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            credentials: 'same-origin'
                        });
                        
                        // First, get the response as text to handle both JSON and HTML responses
                        const responseText = await response.text();
                        let result;
                        
                        try {
                            // Try to parse as JSON
                            result = JSON.parse(responseText);
                        } catch (e) {
                            // If parsing as JSON fails, it's probably an HTML error page
                            console.error('Failed to parse response as JSON:', e);
                            console.error('Response text:', responseText);
                            throw new Error('Server returned an invalid response. Please try again.');
                        }
                        
                        if (!response.ok) {
                            // If the response is not OK, throw an error with the message from the server
                            const errorMessage = result.message || 
                                             result.error || 
                                             `Server returned status ${response.status} ${response.statusText}`;
                            throw new Error(errorMessage);
                        }
                        
                        if (result.success) {
                            showToast(result.message, 'success');
                            
                            // Get the selection date from the form
                            const selectionDate = formData.get('selection_date');
                            const dateId = selectionDate.replace(/-/g, '');
                            
                            // Update the UI to show the selection was successful
                            const menuToggle = document.getElementById(`menu-toggle-${dateId}`);
                            const menuItems = document.getElementById(`menu-items-${dateId}`);
                            
                            if (menuToggle) {
                                // Update the button text
                                menuToggle.querySelector('button').innerHTML = `
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 00-1 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 10-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 100-2H5.999a5 5 0 10-1 9.9V17a1 1 0 102 0v-2.101a7.002 7.002 0 0111.601-2.566 1 1 0 10-1.885-.666A5.002 5.002 0 007.999 9H5a1 1 0 100 2h3a1 1 0 011 1v3a1 1 0 11-2 0v-1.101a7.002 7.002 0 01-6-6.899V3a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Change Selection
                                `;
                                menuToggle.classList.remove('hidden');
                            }
                            
                            if (menuItems) {
                                menuItems.classList.add('hidden');
                            }
                            
                            // Reload the page after a short delay to update the UI
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                        
                    } catch (error) {
                        console.error('Error:', error);
                        showToast(error.message, 'error');
                        
                        // If it's an authentication error, redirect to login
                        if (error.message.includes('logged in')) {
                            setTimeout(() => {
                                const target = (window.COMPANY_ID) ? (`/hr/${window.COMPANY_ID}/login`) : '/login';
                                window.location.href = target;
                            }, 1500);
                        }
                        
                        submitButton.innerHTML = originalButtonContent;
                        submitButton.disabled = false;
                    }
                });
            });
            
            // Week Navigation
            const urlParams = new URLSearchParams(window.location.search);
            let currentWeekStart = new Date('<?= date('Y-m-d', strtotime('monday this week')) ?>');
            
            // Update URL with new week parameter
            function updateWeek(weekOffset) {
                currentWeekStart.setDate(currentWeekStart.getDate() + (weekOffset * 7));
                const monday = new Date(currentWeekStart);
                monday.setDate(monday.getDate() - monday.getDay() + (monday.getDay() === 0 ? -6 : 1));
                
                const params = new URLSearchParams(window.location.search);
                params.set('week', monday.toISOString().split('T')[0]);
                window.location.search = params.toString();
            }
            
            // Event listeners for navigation buttons
            document.getElementById('prev-week').addEventListener('click', () => updateWeek(-1));
            document.getElementById('next-week').addEventListener('click', () => updateWeek(1));
            
            // Add keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    updateWeek(-1);
                } else if (e.key === 'ArrowRight') {
                    updateWeek(1);
                }
            });
        });
    </script>
    
    <!-- Help Text -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">How to use the menu selection</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Browse the weekly menu above to see available options for each day</li>
                        <li>Click "Select" to choose a meal for a specific day</li>
                        <li>You can change your selection until the deadline (10:00 AM on the day)</li>
                        <li>After the deadline, your selection will be locked in</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View History Button -->
    <div class="mt-8 text-center">
        <a href="/menu/my-selections" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
            </svg>
            View My Selection History
        </a>
    </div>
</div>

<!-- Additional Items Modal -->
<div id="additional-items-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg font-medium text-gray-900">Additional Menu Items</h3>
            <button onclick="closeAdditionalItemsModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="mt-4">
            <div id="additional-items-container" class="space-y-3 max-h-96 overflow-y-auto pr-2">
                <!-- Items will be loaded here dynamically -->
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeAdditionalItemsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDate = '';
    let csrfToken = '';
    
    function openAdditionalItemsModal(date) {
        currentDate = date;
        const menuData = document.getElementById(`menu-data-${date.replace(/-/g, '')}`);
        const items = JSON.parse(menuData.dataset.regularItems || '[]');
        csrfToken = menuData.dataset.csrfToken || '';
        
        const container = document.getElementById('additional-items-container');
        container.innerHTML = '';
        
        if (items.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No additional items available.</p>';
        } else {
            // Helper: map or infer category
            const norm = (s) => (s || '').toString().trim().toLowerCase();
            const inferCategory = (name) => {
                const n = norm(name);
                // Beverages keywords
                if (/(agua|refresco|jugo|café|cafe|té|te|mate|terer|soda|gaseosa|cola|coca|pepsi|sprite|fanta|bebida)/.test(n)) return 'bebidas';
                // Desserts keywords
                if (/(postre|dessert|torta|pastel|helado)/.test(n)) return 'postres';
                // Merienda/snacks keywords
                if (/(merienda|sandwich|sándwich|empanada|chipa|tostada)/.test(n)) return 'merienda';
                return 'almuerzo';
            };

            // Group items
            const groups = { almuerzo: [], merienda: [], postres: [], bebidas: [] };
            items.forEach(item => {
                const cat = norm(item.category) || inferCategory(item.name);
                (groups[cat] || groups['almuerzo']).push(item);
            });

            // Render accordion per group (fixed order)
            const order = ['almuerzo', 'merienda', 'postres', 'bebidas'];

            const createItemNode = (item) => {
                const el = document.createElement('div');
                el.className = 'group relative flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50';
                const priceStr = Number(item.price ?? 0).toLocaleString('es-PY');
                el.innerHTML = `
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-medium text-gray-900">${escapeHtml(item.name)}</h4>
                            <span class="text-sm font-medium text-blue-600 whitespace-nowrap ml-2">₲${priceStr}</span>
                        </div>
                        ${item.description ? `<p class=\"text-xs text-gray-500 mt-1\">${escapeHtml(item.description)}</p>` : ''}
                    </div>
                    <form action="/menu/select" method="POST" class="flex-shrink-0">
                        <input type="hidden" name="menu_item_id" value="${item.id}">
                        <input type="hidden" name="selection_date" value="${date}">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" title="Add to order">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">Add to order</span>
                        </button>
                    </form>
                `;
                return el;
            };

            order.forEach(cat => {
                const catItems = groups[cat];
                if (!catItems || catItems.length === 0) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded-md';
                wrapper.innerHTML = `
                    <button type="button" class="w-full flex justify-between items-center px-4 py-2 bg-gray-50 hover:bg-gray-100" data-accordion-toggle="${cat}">
                        <span class="text-sm font-medium capitalize">${cat}</span>
                        <span class="text-xs text-gray-500">${catItems.length} opciones</span>
                    </button>
                    <div class="p-3 space-y-2 hidden" id="accordion-panel-${cat}"></div>
                `;

                container.appendChild(wrapper);
                const panel = wrapper.querySelector(`#accordion-panel-${cat}`);
                catItems.forEach(item => panel.appendChild(createItemNode(item)));

                const toggle = wrapper.querySelector(`[data-accordion-toggle="${cat}"]`);
                toggle.addEventListener('click', () => {
                    panel.classList.toggle('hidden');
                });
            });
        }
        
        document.getElementById('additional-items-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeAdditionalItemsModal() {
        document.getElementById('additional-items-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('additional-items-modal');
        if (event.target === modal) {
            closeAdditionalItemsModal();
        }
    }
    
    // Close modal with Escape key
    document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.key === 'Escape') {
            closeAdditionalItemsModal();
        }
    };
</script>
