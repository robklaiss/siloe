<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Company;

class CompanyController extends Controller {
    private $companyModel;
    private $userModel;
    private $selectionModel;

    public function __construct($router = null, $request = null, $response = null, $session = null) {
        // Create a dummy router if none provided (for direct testing)
        if ($router === null) {
            $router = new \App\Core\Router();
        }
        
        parent::__construct($router, $request, $response, $session);
        
        $this->companyModel = new Company();
        $this->userModel = new \App\Models\User();
        $this->selectionModel = new \App\Models\EmployeeMenuSelection();
    }
    
    /**
     * Display today's menu selections for a specific company
     * 
     * @param string $id Company ID from route parameter
     */
    public function todaysMenuSelections($id = null) {
        $companyId = $id;
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Se requiere el ID de la empresa.');
            $this->response->redirect('/admin/companies');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        
        if (!$company) {
            $this->session->setFlash('error', 'Empresa no encontrada.');
            $this->response->redirect('/admin/companies');
            return;
        }
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Get today's menu selections for the company
        $selections = $this->selectionModel->getSelectionsByCompanyAndDate($companyId, $today);
        
        // Set company ID in session for any subsequent requests
        $this->session->set('company_id', $companyId);
        
        // Load the view
        return $this->view('hr/menu_selections/today', [
            'title' => 'Selecciones de Menú de Hoy - ' . ($company['name'] ?? 'Empresa'),
            'selections' => $selections,
            'date' => $today,
            'company' => $company,
            'isAdmin' => true,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Display menu selections history for a specific company
     * 
     * @param string $id Company ID from route parameter
     */
    public function selectionsHistory($id = null) {
        $companyId = $id;
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Se requiere el ID de la empresa.');
            $this->response->redirect('/admin/companies');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        
        if (!$company) {
            $this->session->setFlash('error', 'Empresa no encontrada.');
            $this->response->redirect('/admin/companies');
            return;
        }
        
        // Set company ID in session for any subsequent requests
        $this->session->set('company_id', $companyId);
        
        // Redirect to the HR selections history page
        $this->response->redirect('/hr/menu-selections/history');
    }
    
    /**
     * Display a listing of companies
     */
    public function index() {
        // Get all companies
        $companies = $this->companyModel->getAllCompanies();
        
        // Load view with companies data
        return $this->view('admin/companies/index', [
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

        // Load view
        return $this->view('admin/companies/create', [
            'title' => 'Crear Empresa - ' . (defined('\APP_NAME') ? \APP_NAME : 'Siloe'),
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Store a newly created company
     */
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, inténtelo de nuevo.';
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
        if (empty($name)) {
            $errors[] = 'El nombre de la empresa es obligatorio';
        }
        
        if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Se requiere un correo electrónico de contacto válido';
        }
        
        // Prepare data array
        $data = [
            'name' => $name,
            'address' => $address,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'is_active' => $isActive
        ];

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
            $_SESSION['error'] = 'Ya existe una empresa con este nombre.';
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
                $errors[] = 'Tipo de archivo no válido. Solo se permiten JPG, PNG y GIF.';
            }

            if ($_FILES['logo']['size'] > $maxSize) {
                $errors[] = 'El tamaño del archivo supera el límite máximo de 2 MB.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../../../public/uploads/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $logoName = uniqid() . '-' . basename($_FILES['logo']['name']);
                $logoPath = '/uploads/logos/' . $logoName;
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName)) {
                    $errors[] = 'Error al subir el logo.';
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
        
        $companyId = $this->companyModel->createCompany($companyData);

        if ($companyId) {
            // Auto-create default HR Manager (company_admin) for this company
            $hrName = 'HR Manager - ' . $name;
            // Prefer company contact email if valid and unused; otherwise generate one
            $proposedEmail = $contactEmail;
            if (empty($proposedEmail) || !filter_var($proposedEmail, FILTER_VALIDATE_EMAIL) || $this->userModel->emailExists($proposedEmail)) {
                // Build a fallback email based on company name and ID
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
                if ($slug === '') {
                    $slug = 'company';
                }
                $proposedEmail = 'hr-' . $slug . '-' . (int)$companyId . '@siloe.local';
                // In rare case this also exists, append a short random suffix
                if ($this->userModel->emailExists($proposedEmail)) {
                    $proposedEmail = 'hr-' . $slug . '-' . (int)$companyId . '-' . substr(bin2hex(random_bytes(2)), 0, 4) . '@siloe.local';
                }
            }

            // Generate a secure random password (16 hex chars)
            $generatedPassword = substr(bin2hex(random_bytes(12)), 0, 16);

            // Attempt to create the HR Manager user
            $hrUserId = null;
            try {
                $hrUserId = $this->userModel->createUser([
                    'name' => $hrName,
                    'email' => $proposedEmail,
                    'password' => $generatedPassword,
                    'role' => 'company_admin',
                    'company_id' => (int)$companyId,
                    'is_active' => 1,
                ]);
            } catch (\Throwable $e) {
                // Log silently; continue flow
                error_log('Error al crear automáticamente el Gerente de RR. HH.: ' . $e->getMessage());
            }

            // Compose success message and flash credentials if user was created
            if ($hrUserId) {
                $_SESSION['success'] = 'Empresa creada correctamente. Usuario de RR. HH. creado: ' 
                    . htmlspecialchars($proposedEmail) . ' / Contraseña: ' . htmlspecialchars($generatedPassword);
            } else {
                $_SESSION['success'] = 'Empresa creada correctamente. Nota: no se pudo crear automáticamente el usuario de RR. HH.';
            }

            header('Location: /admin/companies');
        } else {
            $_SESSION['error'] = 'Error al crear la empresa';
            header('Location: /admin/companies/create');
        }
        exit;
    }
    
    /**
     * Display the specified company
     */
    public function show($id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Get company employees
        $employees = $this->userModel->getEmployeesByCompany($id);
        
        // Load view with company data
        return $this->view('admin/companies/show', [
            'company' => $company,
            // Provide both keys for backward-compatibility; view expects 'users'
            'users' => $employees,
            'employees' => $employees
        ]);
    }
    
    /**
     * Show the form for editing a company
     */
    public function edit($id) {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Render the company edit form
        return $this->view('admin/companies/edit', [
            'title' => 'Editar Empresa - ' . (defined('\APP_NAME') ? \APP_NAME : 'Siloe'),
            'company' => $company,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Update the specified company
     */
    public function update($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, inténtelo de nuevo.';
            header('Location: /admin/companies/' . $id . '/edit');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
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
        if (empty($name)) $errors[] = 'El nombre de la empresa es obligatorio';
        if (!empty($contactEmail) && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Se requiere un correo electrónico de contacto válido';
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
                $_SESSION['error'] = 'Ya existe una empresa con este nombre.';
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
                $errors[] = 'Tipo de archivo no válido. Solo se permiten JPG, PNG y GIF.';
            }

            if ($_FILES['logo']['size'] > $maxSize) {
                $errors[] = 'El tamaño del archivo supera el límite máximo de 2 MB.';
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
                    $errors[] = 'Error al subir el nuevo logo.';
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

        // Validate data (only requires name; validates email/phone if present)
        $errors = $this->validateCompanyData($companyData);

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

        // Update the company
        if ($this->companyModel->updateCompany($id, $companyData)) {
            $_SESSION['success'] = 'Empresa actualizada correctamente.';
            header('Location: /admin/companies');
            exit;
        } else {
            $_SESSION['error'] = 'Error al actualizar la empresa. Por favor, inténtelo de nuevo.';
            header('Location: /admin/companies/' . $id . '/edit');
            exit;
        }
    }
    
    /**
     * Remove the specified company
     */
    public function destroy($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, inténtelo de nuevo.';
            header('Location: /admin/companies');
            exit;
        }

        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }

        // Cargar el modelo de solicitudes de eliminación
        $deleteRequestModel = new \App\Models\DeleteRequest();
        
        // Verificar si ya existe una solicitud pendiente para esta empresa
        if ($deleteRequestModel->hasPendingRequest('company', $id)) {
            $_SESSION['info'] = 'Ya existe una solicitud pendiente para eliminar esta empresa. Un administrador la revisará pronto.';
            header('Location: /admin/companies');
            exit;
        }
        
        // Obtener el ID del usuario actual
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Crear una solicitud de eliminación
        $reason = $_POST['delete_reason'] ?? '';
        $requestId = $deleteRequestModel->createRequest('company', $id, $company['name'], $userId, $reason);
        
        if ($requestId) {
            $_SESSION['success'] = 'Solicitud de eliminación enviada correctamente. Un administrador revisará su solicitud.';
        } else {
            $_SESSION['error'] = 'Error al crear la solicitud de eliminación. Por favor, inténtelo de nuevo.';
        }
        
        header('Location: /admin/companies');
        exit;
    }
    
    /**
     * Validate company data
     */
    private function validateCompanyData($data) {
        $errors = [];

        $name = trim($data['name'] ?? '');
        $address = trim($data['address'] ?? '');
        $email = trim($data['contact_email'] ?? ($data['email'] ?? ''));
        $phone = trim($data['contact_phone'] ?? ($data['phone'] ?? ''));

        // Name is required
        if ($name === '') {
            $errors[] = 'El nombre es obligatorio.';
        } elseif (strlen($name) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        }

        // Address optional

        // Phone optional; validate format if provided
        if ($phone !== '' && !preg_match('/^[0-9+\-\s()]+$/', $phone)) {
            $errors[] = 'El número de teléfono no es válido.';
        }

        // Email optional; validate format if provided
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        return $errors;
    }
    
    /**
     * Show the company HR dashboard
     */
    public function hrDashboard($id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Check if the current user is authorized to view this company's HR dashboard
        if ($_SESSION['user_role'] !== 'admin' && 
            ($_SESSION['user_role'] !== 'company_admin' || $_SESSION['company_id'] != $id)) {
            $_SESSION['error'] = 'No está autorizado para ver el panel de RR. HH. de esta empresa';
            header('Location: /admin/companies');
            exit;
        }
        
        // Get company employees
        $employees = $this->userModel->getEmployeesByCompany($id);
        
        // Load view with company data
        return $this->view('admin/companies/hr_dashboard', [
            'company' => $company,
            'employees' => $employees,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Show the company employee dashboard
     */
    public function employeeDashboard($id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Check if the current user is authorized to view this company's employee dashboard
        if ($_SESSION['user_role'] !== 'admin' && 
            ($_SESSION['user_role'] !== 'company_admin' || $_SESSION['company_id'] != $id)) {
            $_SESSION['error'] = 'No está autorizado para ver el panel de empleados de esta empresa';
            header('Location: /admin/companies');
            exit;
        }
        
        // Get company employees
        $employees = $this->userModel->getEmployeesByCompany($id);
        
        // Get current employee data
        $employee = null;
        if (isset($_SESSION['user_id'])) {
            $employee = $this->userModel->getUserById($_SESSION['user_id']);
        }
        
        // Load view with company and employee data
        return $this->view('admin/companies/employee_dashboard', [
            'company' => $company,
            'employees' => $employees,
            'employee' => $employee
        ]);
    }
    
    /**
     * Show the form to create a new employee for a specific company
     */
    public function createEmployee($id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Check if the current user is authorized to create employees for this company
        if ($_SESSION['user_role'] !== 'admin' && 
            ($_SESSION['user_role'] !== 'company_admin' || $_SESSION['company_id'] != $id)) {
            $_SESSION['error'] = 'No está autorizado para crear empleados para esta empresa';
            header('Location: /admin/companies/' . $id . '/hr');
            exit;
        }
        
        // Load view with company data and CSRF token
        return $this->view('hr/employees/create', [
            'company' => $company,
            'company_id' => $id,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken($token): bool {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        // Regenerate token after use
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
    
    /**
     * Store a newly created employee for a specific company
     */
    public function storeEmployee($id) {
        // Get company by ID
        $company = $this->companyModel->getCompanyById($id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /admin/companies');
            exit;
        }
        
        // Check if the current user is authorized to create employees for this company
        if ($_SESSION['user_role'] !== 'admin' && 
            ($_SESSION['user_role'] !== 'company_admin' || $_SESSION['company_id'] != $id)) {
            $_SESSION['error'] = 'No está autorizado para crear empleados para esta empresa';
            header('Location: /admin/companies/' . $id . '/hr');
            exit;
        }
        
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, actualice la página e inténtelo de nuevo.';
            $_SESSION['old'] = $_POST;
            header('Location: /admin/companies/' . $id . '/hr/employees/create');
            exit;
        }
        
        // Get form data
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'company_id' => $id,
            'role' => 'employee',
            'is_active' => 1
        ];
        
        // Validate data
        $errors = $this->validateEmployeeData($data);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $data;
            
            // Initialize session flash error array if not exists
            if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
                $_SESSION['_flash'] = [];
            }
            if (!isset($_SESSION['_flash']['error']) || !is_array($_SESSION['_flash']['error'])) {
                $_SESSION['_flash']['error'] = [];
            }
            
            // Store field errors for UI feedback
            foreach ($errors as $error) {
                if (strpos($error, 'El nombre') === 0) {
                    $_SESSION['_flash']['error']['name'] = trim(str_replace('El nombre', '', $error));
                } elseif (strpos($error, 'El correo electrónico') === 0) {
                    $_SESSION['_flash']['error']['email'] = trim(str_replace('El correo electrónico', '', $error));
                } elseif (strpos($error, 'La contraseña') === 0) {
                    $_SESSION['_flash']['error']['password'] = trim(str_replace('La contraseña', '', $error));
                } elseif (strpos($error, 'La confirmación de la contraseña') === 0) {
                    $_SESSION['_flash']['error']['password_confirm'] = trim(str_replace('La confirmación de la contraseña', '', $error));
                }
            }
            
            header('Location: /admin/companies/' . $id . '/hr/employees/create');
            exit;
        }
        
        // Create the user
        $userId = $this->userModel->createUser($data);
        if (!$userId) {
            $_SESSION['error'] = 'Error al crear el usuario. Por favor, inténtelo de nuevo.';
            header('Location: /admin/companies/' . $id . '/hr/employees/create');
            exit;
        }
        
        $_SESSION['success'] = 'Empleado creado correctamente.';
        // Redirect to company details page so the new user is visible in the Company Users table
        header('Location: /admin/companies/' . $id);
        exit;
    }
    
    /**
     * Validate employee data
     */
    private function validateEmployeeData($data) {
        $errors = [];
        
        // Name validation
        if (empty($data['name'])) {
            $errors[] = 'El nombre es obligatorio.';
        } elseif (strlen($data['name']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'El correo electrónico ya existe.';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es obligatoria.';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        
        // Password confirmation validation
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'La confirmación de la contraseña no coincide.';
        }
        
        return $errors;
    }
}
