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
    public function index(Request $request, Response $response) {
        // Check if user is logged in
        if (!Session::get('user_id')) {
            Session::setFlash('error', 'You must be logged in to access this page.');
            return $response->redirect('/login');
        }
        
        // Get user role and redirect to appropriate dashboard
        $userRole = Session::get('user_role');
        
        // Redirect based on user role
        switch ($userRole) {
            case 'admin':
                return $response->redirect('/admin/dashboard');
            case 'company_admin':
                return $response->redirect('/hr/dashboard');
            case 'employee':
                return $response->redirect('/menu/select');
            default:
                // If role is not recognized, show a generic dashboard
                return $this->render('dashboard/index', [
                    'title' => 'Dashboard - ' . APP_NAME,
                    'user' => [
                        'id' => Session::get('user_id'),
                        'email' => Session::get('user_email'),
                        'role' => $userRole
                    ]
                ]);
        }
    }
}
