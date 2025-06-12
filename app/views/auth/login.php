<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <!-- Debug Info -->
        <div id="debug-info" class="fixed top-4 right-4 bg-yellow-100 p-4 rounded shadow-md text-sm hidden">
            <h3 class="font-bold mb-2">Debug Info</h3>
            <div id="debug-content"></div>
        </div>
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Welcome to <?= htmlspecialchars(APP_NAME) ?></h1>
                <p class="text-gray-600">Please sign in to your account</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="/login" method="POST" class="space-y-6">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input type="email" id="email" name="email" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>">
                    <?php unset($_SESSION['old']['email']); ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Or
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="/register" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Create a new account
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Debug: Log form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const debugInfo = document.getElementById('debug-info');
            const debugContent = document.getElementById('debug-content');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submission started');
                    debugInfo.classList.remove('hidden');
                    
                    // Log form data
                    const formData = new FormData(form);
                    const formEntries = Object.fromEntries(formData.entries());
                    
                    // Don't log the actual password in production
                    if (formEntries.password) {
                        formEntries.password = '********';
                    }
                    
                    const logMessage = 'Form data: ' + JSON.stringify(formEntries, null, 2);
                    console.log(logMessage);
                    debugContent.textContent = logMessage;
                    
                    // Continue with form submission
                    return true;
                });
            } else {
                console.error('Login form not found');
                debugInfo.classList.remove('hidden');
                debugContent.textContent = 'Error: Login form not found';
            }
            
            // Log any JavaScript errors
            window.onerror = function(message, source, lineno, colno, error) {
                const errorMsg = `JavaScript Error: ${message} (${source}:${lineno}:${colno})`;
                console.error(errorMsg);
                debugInfo.classList.remove('hidden');
                debugContent.textContent = errorMsg;
                return false; // Let the error propagate
            };
        });
    </script>
</body>
</html>
