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
    
    /**
     * Show the employee login form for a specific company
     * 
     * @param int $company_id The ID of the company
     */
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
            // If logged into a different company, fall through to show the form so the user can switch
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
    
    // ... rest of the file remains the same
}
