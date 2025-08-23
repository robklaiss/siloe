<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - Login</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍽️</text></svg>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <base href="<?= APP_URL ?>/">
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #212529;
        }
        
        .login-container {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .alert {
            position: relative;
            padding: 1rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .mt-3 {
            margin-top: 1rem !important;
        }
        
        .text-muted {
            color: #6c757d !important;
            text-decoration: none;
        }
        
        .text-muted:hover {
            text-decoration: underline;
        }
        
        .form-check {
            display: block;
            min-height: 1.5rem;
            padding-left: 1.5em;
            margin-bottom: 1rem;
        }
        
        .form-check-input {
            float: left;
            margin-left: -1.5em;
            margin-top: 0.3em;
        }
        
        .form-check-label {
            cursor: pointer;
        }
        :root {
            --bs-body-bg: #f8f9fa;
            --bs-primary: #0d6efd;
            --bs-secondary: #6c757d;
            --bs-success: #198754;
            --bs-danger: #dc3545;
            --bs-light: #f8f9fa;
            --bs-dark: #212529;
        }
        
        body {
            background-color: var(--bs-body-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--bs-dark);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .alert {
            border-radius: 0.375rem;
        }
        
        .bi {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="<?= asset('uploads/logos/684ddb17c8715-siloe-logo.jpg') ?>" alt="Siloe" class="mb-3" style="max-height:60px; width:auto;">
                            <h2 class="h4 mb-2"><?= htmlspecialchars(APP_NAME) ?></h2>
                            <p class="text-muted">Please sign in to continue</p>
                        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="/login" method="POST">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?= htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>"
                       autocomplete="username">
                <?php unset($_SESSION['old']['email']); ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                <label class="form-check-label" for="remember_me">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="/forgot-password" class="text-muted">Forgot your password?</a>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" 
            crossorigin="anonymous">
    </script>
    
    <script>
        // Log all failed resource loads with more details
        window.addEventListener('error', function(e) {
            const target = e.target;
            const resourceType = target.tagName.toLowerCase();
            const resourceUrl = target.src || target.href || '';
            const errorDetails = {
                type: 'resource_error',
                resourceType: resourceType,
                resourceUrl: resourceUrl,
                timestamp: new Date().toISOString(),
                pageUrl: window.location.href,
                userAgent: navigator.userAgent
            };
            console.error('Failed to load resource:', errorDetails);
            
            // Send error to server for logging
            fetch('/log-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(errorDetails)
            }).catch(err => console.error('Failed to log error:', err));
            
        }, true);

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            
            if (loginForm) {
                // Set autocomplete attributes
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                if (emailInput) emailInput.setAttribute('autocomplete', 'username');
                if (passwordInput) passwordInput.setAttribute('autocomplete', 'current-password');
                
                // Form submission handler
                loginForm.addEventListener('submit', function(e) {
                    const email = emailInput?.value.trim();
                    const password = passwordInput?.value;
                    
                    if (!email || !password) {
                        e.preventDefault();
                        showAlert('Please fill in all required fields', 'danger');
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';
                    }
                    
                    return true;
                });
            }
            
            // Show alert function
            function showAlert(message, type = 'info') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show mb-3`;
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <span class="me-2">${message}</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                const container = document.querySelector('.card-body');
                if (container) {
                    // Insert after the header
                    const header = container.querySelector('.text-center');
                    if (header && header.nextElementSibling) {
                        container.insertBefore(alertDiv, header.nextElementSibling);
                    } else {
                        container.insertBefore(alertDiv, container.firstChild);
                    }
                    
                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alertDiv);
                        bsAlert.close();
                    }, 5000);
                }
            }
            
            // Show any existing alerts from PHP
            <?php if (isset($_SESSION['error'])): ?>
                showAlert('<?= addslashes($_SESSION['error']) ?>', 'danger');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                showAlert('<?= addslashes($_SESSION['success']) ?>', 'success');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            // Debug mode toggle (Ctrl+Alt+D)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.altKey && e.key === 'd') {
                    document.body.classList.toggle('debug-mode');
                    console.log('Debug mode toggled');
                }
            });
        });
    </script>
</body>
</html>
