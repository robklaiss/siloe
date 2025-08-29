<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\DeleteRequest;
use App\Models\Company;

class DeleteRequestController extends Controller
{
    protected $deleteRequestModel;
    protected $companyModel;

    public function __construct()
    {
        parent::__construct();
        $this->deleteRequestModel = new DeleteRequest();
        $this->companyModel = new Company();

        // Verificar que el usuario sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta página';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Mostrar lista de solicitudes de eliminación pendientes
     */
    public function index()
    {
        $pendingRequests = $this->deleteRequestModel->getPendingRequests();
        
        return $this->view('admin/delete_requests/index', [
            'requests' => $pendingRequests,
            'title' => 'Solicitudes de Eliminación Pendientes'
        ]);
    }

    /**
     * Ver detalles de una solicitud
     */
    public function show($id)
    {
        $request = $this->deleteRequestModel->getRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            header('Location: /admin/delete-requests');
            exit;
        }
        
        // Obtener detalles adicionales según el tipo de entidad
        $entityDetails = [];
        if ($request['entity_type'] === 'company') {
            $entityDetails = $this->companyModel->getCompanyById($request['entity_id']);
        }
        
        return $this->view('admin/delete_requests/show', [
            'request' => $request,
            'entityDetails' => $entityDetails,
            'title' => 'Detalles de Solicitud'
        ]);
    }

    /**
     * Aprobar una solicitud de eliminación
     */
    public function approve($id)
    {
        // Verificar CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, inténtelo de nuevo.';
            header('Location: /admin/delete-requests');
            exit;
        }

        $request = $this->deleteRequestModel->getRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            header('Location: /admin/delete-requests');
            exit;
        }
        
        // Si la solicitud ya no está pendiente
        if ($request['status'] !== 'pending') {
            $_SESSION['error'] = 'Esta solicitud ya ha sido procesada';
            header('Location: /admin/delete-requests');
            exit;
        }
        
        // Aprobar la solicitud
        $userId = $_SESSION['user_id'] ?? 0;
        $approved = $this->deleteRequestModel->approveRequest($id, $userId);
        
        if (!$approved) {
            $_SESSION['error'] = 'Error al aprobar la solicitud';
            header("Location: /admin/delete-requests/{$id}");
            exit;
        }
        
        // Procesar la eliminación según el tipo de entidad
        $success = false;
        $message = '';
        
        if ($request['entity_type'] === 'company') {
            if ($this->companyModel->deleteCompany($request['entity_id'])) {
                $success = true;
                $message = "Empresa '{$request['entity_name']}' eliminada correctamente";
            } else {
                $message = "Error al eliminar la empresa '{$request['entity_name']}'";
            }
        }
        
        if ($success) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }
        
        header('Location: /admin/delete-requests');
        exit;
    }

    /**
     * Rechazar una solicitud de eliminación
     */
    public function reject($id)
    {
        // Verificar CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, inténtelo de nuevo.';
            header('Location: /admin/delete-requests');
            exit;
        }

        $request = $this->deleteRequestModel->getRequestById($id);
        
        if (!$request) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            header('Location: /admin/delete-requests');
            exit;
        }
        
        // Si la solicitud ya no está pendiente
        if ($request['status'] !== 'pending') {
            $_SESSION['error'] = 'Esta solicitud ya ha sido procesada';
            header('Location: /admin/delete-requests');
            exit;
        }
        
        // Rechazar la solicitud
        $userId = $_SESSION['user_id'] ?? 0;
        $rejected = $this->deleteRequestModel->rejectRequest($id, $userId);
        
        if ($rejected) {
            $_SESSION['success'] = "Solicitud para eliminar '{$request['entity_name']}' rechazada correctamente";
        } else {
            $_SESSION['error'] = 'Error al rechazar la solicitud';
        }
        
        header('Location: /admin/delete-requests');
        exit;
    }
}
