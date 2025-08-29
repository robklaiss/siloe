#!/bin/bash

# Server details
SERVER="siloecom@192.185.143.154"
REMOTE_FILE="/home/siloecom/public_html/app/Controllers/AuthController.php"

# Create a temporary file with the correct content
cat > /tmp/AuthController.php << 'EOL'
<?php
namespace App\Controllers;

class AuthController extends Controller {
    public function showLoginForm() {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        // Display login form
        return $this->view('auth/login', [
            'title' => 'Login - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function showEmployeeLoginForm($company_id) {
        // If already logged in, redirect to the appropriate company-scoped dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'] ?? null;
            $sessionCompanyId = $_SESSION['company_id'] ?? null;

            if ($sessionCompanyId == $company_id) {
                if ($role === 'company_admin') {
                    header('Location: /admin/companies/' . $company_id . '/hr');
                } else { // hr or employee
                    header('Location: /hr/' . $company_id . '/dashboard');
                }
                exit;
            }
        }
        
        // Get company info
        $companyModel = new \App\Models\Company();
        $company = $companyModel->getCompanyById($company_id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /login');
            exit;
        }
        
        // Display employee login form
        return $this->view('auth/employee-login', [
            'title' => 'Acceso Empleados - ' . $company['name'],
            'company' => $company,
            'company_id' => $company_id,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}
EOL

# Upload the file to the server
echo "Uploading fixed AuthController.php to server..."
scp /tmp/AuthController.php "$SERVER:$REMOTE_FILE"

# Set correct permissions
echo "Setting correct permissions..."
ssh "$SERVER" "chmod 644 $REMOTE_FILE"

# Clean up
rm /tmp/AuthController.php

echo "Done! AuthController.php has been replaced and fixed on the server."
