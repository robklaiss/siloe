<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

class DashboardController extends Controller {
    
    /**
     * Display the dashboard based on user role
     */
    public function index() {
        // Check if user is logged in
        if (!$this->session->get('user_id')) {
            $this->session->setFlash('error', 'You must be logged in to access this page.');
            return $this->response->redirect('/login');
        }
        
        $userRole = $this->session->get('user_role');
        $userId = $this->session->get('user_id');
        
        // Redirect based on user role
        switch ($userRole) {
            case 'admin':
                return $this->response->redirect('/admin/dashboard');
            case 'company_admin':
            case 'hr':
                $companyId = $this->session->get('company_id');
                if ($companyId) {
                    return $this->response->redirect('/hr/dashboard/' . $companyId);
                } else {
                    $this->session->setFlash('error', 'Company not assigned.');
                    return $this->response->redirect('/login');
                }
            case 'employee':
                return $this->response->redirect('/employee/menu');
            default:
                $this->session->setFlash('error', 'Invalid user role.');
                return $this->response->redirect('/login');
        }
    }
}
