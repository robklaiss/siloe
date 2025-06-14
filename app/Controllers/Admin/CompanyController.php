<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Company;
use App\Core\Request;
use App\Core\Response;

class CompanyController extends Controller {
    private $companyModel;

    public function __construct() {
        // Check if user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        $this->companyModel = new Company();
    }
    
    /**
     * Display a listing of companies
     */
    public function index() {
        // Get all companies
        $companies = $this->companyModel->getAllCompanies();
        
        // Render the companies list view
        $this->view('admin/companies/index', [
            'title' => 'Manage Companies - ' . APP_NAME,
            'companies' => $companies
        ]);
    }
    
    /**
     * Show the form for creating a new company
     */
    public function create() {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Render the company creation form
        $this->view('admin/companies/create', [
            'title' => 'Create Company - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Store a newly created company
     */
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
            header('Location: /admin/companies/create');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Company name is required';
        if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid contact email is required';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'address' => $address, 
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'is_active' => $isActive
            ];
            header('Location: /admin/companies/create');
            exit;
        }

        // Check for duplicate company name
        if ($this->companyModel->getCompanyByName($name)) {
            $_SESSION['error'] = 'A company with this name already exists.';
            $_SESSION['old'] = [
                'name' => $name, 
                'address' => $address, 
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'is_active' => $isActive
            ];
            header('Location: /admin/companies/create');
            exit;
        }

        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
            }

            if ($_FILES['logo']['size'] > $maxSize) {
                $errors[] = 'File size exceeds the maximum limit of 2MB.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $logoName = uniqid() . '-' . basename($_FILES['logo']['name']);
                $logoPath = '/uploads/logos/' . $logoName;
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName)) {
                    $errors[] = 'Failed to upload logo.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            // ... (redirect back with old data)
            header('Location: /admin/companies/create');
            exit;
        }

        // Create new company
        $companyData = [
            'name' => $name,
            'address' => $address,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'is_active' => $isActive,
            'logo' => $logoPath
        ];
        
        $result = $this->companyModel->createCompany($companyData);

        if ($result) {
            $_SESSION['success'] = 'Company created successfully';
            header('Location: /admin/companies');
        } else {
            $_SESSION['error'] = 'Failed to create company';
            header('Location: /admin/companies/create');
        }
        exit;
    }
    
    /**
     * Show the form for editing a company
     */
    public function edit(Request $request, Response $response, $id) {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Company not found';
            header('Location: /admin/companies');
            exit;
        }
        
        // Render the company edit form
        $this->view('admin/companies/edit', [
            'title' => 'Edit Company - ' . APP_NAME,
            'company' => $company,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Update the specified company
     */
    public function update(Request $request, Response $response, $id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
            header('Location: /admin/companies/' . $id . '/edit');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Company not found';
            header('Location: /admin/companies');
            exit;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Company name is required';
        if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid contact email is required';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'address' => $address, 
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
                'is_active' => $isActive
            ];
            header('Location: /admin/companies/' . $id . '/edit');
            exit;
        }

        // Check for duplicate company name (if name has changed)
        if (strtolower($name) !== strtolower($company['name'])) {
            if ($this->companyModel->getCompanyByName($name)) {
                $_SESSION['error'] = 'A company with this name already exists.';
                $_SESSION['old'] = ['name' => $name, 'address' => $address, 'contact_email' => $contactEmail, 'contact_phone' => $contactPhone, 'is_active' => $isActive];
                header('Location: /admin/companies/' . $id . '/edit');
                exit;
            }
        }

        // Handle logo upload on update
        $logoPath = $company['logo']; // Keep old logo by default
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
            }

            if ($_FILES['logo']['size'] > $maxSize) {
                $errors[] = 'File size exceeds the maximum limit of 2MB.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Delete old logo if it exists
                if ($company['logo'] && file_exists(__DIR__ . '/../../../public' . $company['logo'])) {
                    unlink(__DIR__ . '/../../../public' . $company['logo']);
                }

                $logoName = uniqid() . '-' . basename($_FILES['logo']['name']);
                $newLogoPath = '/uploads/logos/' . $logoName;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName)) {
                    $logoPath = $newLogoPath;
                } else {
                    $errors[] = 'Failed to upload new logo.';
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: /admin/companies/' . $id . '/edit');
            exit;
        }

        // Prepare data for update
        $companyData = [
            'name' => $name,
            'address' => $address,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'is_active' => $isActive,
            'logo' => $logoPath
        ];

        // Update the company
        if ($this->companyModel->updateCompany($id, $companyData)) {
            $_SESSION['success'] = 'Company updated successfully';
            header('Location: /admin/companies');
        } else {
            $_SESSION['error'] = 'Failed to update company';
            header('Location: /admin/companies/' . $id . '/edit');
        }
        exit;

    }
    
    /**
     * Delete the specified company
     */
    public function destroy(Request $request, Response $response, $id)
    {
        // CSRF check
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF token mismatch.';
            header('Location: /admin/companies');
            exit;
        }

        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // First, get the company to find its logo
        $company = $this->companyModel->getCompanyById($id);

        if ($company) {
            // If a logo exists, delete the file
            if (!empty($company['logo']) && file_exists(__DIR__ . '/../../../public' . $company['logo'])) {
                unlink(__DIR__ . '/../../../public' . $company['logo']);
            }

            // Now, delete the company from the database
            $result = $this->companyModel->deleteCompany($id);

            if ($result) {
                $_SESSION['success'] = 'Company and its logo deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete company.';
            }
        } else {
            $_SESSION['error'] = 'Company not found.';
        }

        header('Location: /admin/companies');
        exit;
    }
    public function show(Request $request, Response $response, $id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Company not found';
            header('Location: /admin/companies');
            exit;
        }
        
        // Get company users
        $users = $this->companyModel->getCompanyUsers($id);
        
        // Render the company details view
        $this->view('admin/companies/show', [
            'title' => $company['name'] . ' - ' . APP_NAME,
            'company' => $company,
            'users' => $users,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Show the company HR dashboard
     */
    public function hrDashboard(Request $request, Response $response, $id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Company not found';
            header('Location: /admin/companies');
            exit;
        }
        
        // Render the company HR dashboard view
        $this->view('admin/companies/hr_dashboard', [
            'title' => $company['name'] . ' HR Dashboard - ' . APP_NAME,
            'company' => $company
        ]);
    }
    
    /**
     * Show the company employee dashboard
     */
    public function employeeDashboard(Request $request, Response $response, $id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Company not found';
            header('Location: /admin/companies');
            exit;
        }
        
        // Render the company employee dashboard view
        $this->view('admin/companies/employee_dashboard', [
            'title' => $company['name'] . ' Employee Dashboard - ' . APP_NAME,
            'company' => $company
        ]);
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string {
        // Always generate a new token to prevent reuse
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
