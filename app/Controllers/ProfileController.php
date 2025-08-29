<?php

namespace App\Controllers;

use App\Models\User;

class ProfileController extends Controller {
    private $userModel;

    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $this->userModel = new User();
    }
    
    /**
     * Show profile index page
     */
    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página.';
            header('Location: /login');
            exit;
        }
        
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: /dashboard');
            exit;
        }
        
        return $this->view('profile/index', [
            'title' => 'Mi Perfil - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'user' => $user,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'profile'
        ])->layout('layouts/app');
    }
    
    /**
     * Show the profile edit form
     */
    public function edit() {
        $userId = $_SESSION['user_id'] ?? null;
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: /dashboard');
            exit;
        }
        
        return $this->view('profile/edit', [
            'title' => 'Editar Perfil - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'user' => $user,
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'profile'
        ])->layout('layouts/app');
    }
    
    /**
     * Show security settings (e.g., change password)
     */
    public function security() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página.';
            header('Location: /login');
            exit;
        }
        
        return $this->view('profile/security', [
            'title' => 'Seguridad de la Cuenta - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'csrf_token' => $_SESSION['csrf_token'] ?? '',
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'profile'
        ])->layout('layouts/app');
    }
    
    /**
     * Update user profile
     */
    public function update() {
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, actualice la página e inténtelo de nuevo.';
            header('Location: /profile');
            exit;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $_SESSION['error'] = 'Debe iniciar sesión para actualizar su perfil.';
            header('Location: /login');
            exit;
        }
        
        // Get form data
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? '')
        ];
        
        // Validate data
        $errors = $this->validateProfileData($data, $userId);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = $_POST;
            header('Location: /profile');
            exit;
        }
        
        // Update user
        $success = $this->userModel->updateUser($userId, $data);
        
        if ($success) {
            // Update session data
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['user_email'] = $data['email'];
            
            $_SESSION['success'] = 'Perfil actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo actualizar el perfil. Por favor, inténtelo de nuevo.';
        }
        
        header('Location: /profile');
        exit;
    }
    
    /**
     * Update user password
     */
    public function updatePassword() {
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, actualice la página e inténtelo de nuevo.';
            header('Location: /profile/security');
            exit;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $_SESSION['error'] = 'Debe iniciar sesión para actualizar su contraseña.';
            header('Location: /login');
            exit;
        }
        
        // Get form data
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        $errors = $this->validatePasswordData($currentPassword, $newPassword, $confirmPassword, $userId);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: /profile/security');
            exit;
        }
        
        // Update password
        $success = $this->userModel->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        
        if ($success) {
            $_SESSION['success'] = 'Contraseña actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo actualizar la contraseña. Por favor, inténtelo de nuevo.';
        }
        
        header('Location: /profile/security');
        exit;
    }
    
    /**
     * Validate profile data
     */
    private function validateProfileData($data, $userId) {
        $errors = [];
        
        // Validate name
        if (empty($data['name'])) {
            $errors[] = 'El nombre es obligatorio.';
        } elseif (strlen($data['name']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'El nombre no debe exceder los 100 caracteres.';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $errors[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Por favor, introduzca una dirección de correo electrónico válida.';
        } elseif (strlen($data['email']) > 255) {
            $errors[] = 'El correo electrónico no debe exceder los 255 caracteres.';
        } else {
            // Check if email is already taken by another user
            $existingUser = $this->userModel->getUserByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors[] = 'Esta dirección de correo electrónico ya está en uso.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate password data
     */
    private function validatePasswordData($currentPassword, $newPassword, $confirmPassword, $userId) {
        $errors = [];
        
        // Validate current password
        if (empty($currentPassword)) {
            $errors[] = 'La contraseña actual es obligatoria.';
        } else {
            $user = $this->userModel->getUserById($userId);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $errors[] = 'La contraseña actual es incorrecta.';
            }
        }
        
        // Validate new password
        if (empty($newPassword)) {
            $errors[] = 'La nueva contraseña es obligatoria.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif (strlen($newPassword) > 255) {
            $errors[] = 'La nueva contraseña no debe exceder los 255 caracteres.';
        }
        
        // Validate password confirmation
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'La confirmación de la contraseña no coincide.';
        }
        
        return $errors;
    }
}
