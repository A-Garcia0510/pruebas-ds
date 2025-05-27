<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Core\Container;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseInterface;
use App\Models\Review;

/**
 * Controlador para la sección de Dashboard del usuario
 */
class DashboardController extends BaseController
{
    private $database;
    private $authenticator;
    private $userRepository;

    public function __construct(
        Request $request,
        Response $response,
        Container $container,
        DatabaseInterface $database
    ) {
        parent::__construct($request, $response, $container);
        $this->database = $database;
        $this->authenticator = AuthFactory::createAuthenticator($database);
        $this->userRepository = AuthFactory::createUserRepository($database);
    }

    /**
     * Muestra el dashboard del usuario
     * 
     * @return string
     */
    public function index()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página.';
                return $this->redirect('/login');
            }
            
            // Obtener email del usuario actual
            $correo = $_SESSION['correo'] ?? null;

            if (!$correo) {
                 throw new \Exception("Error: Correo del usuario no encontrado en la sesión.");
            }
            
            // Obtener los datos del usuario
            $user = $this->userRepository->findByEmail($correo);
            
            if (!$user) {
                throw new \Exception("Error: No se pudieron recuperar los datos del usuario.");
            }
            
            $data = [
                'title' => 'Mi Cuenta - Café Aroma',
                'user' => $user,
                'css' => ['dashboard'],
                'layout' => 'main'
            ];
            
            // Pasar también si puede moderar a la vista para controlar la visibilidad
            $data['canModerate'] = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Empleado', 'Administrador']);

            return $this->render('dashboard/index', $data);
            
        } catch (\Exception $e) {
            error_log('Error en DashboardController::index: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema: ' . $e->getMessage();
            // Redirigir al login o a una página de error
            return $this->redirect('/login');
        }
    }

    public function moderation() {
        // Verificar si el usuario está autenticado y tiene el rol correcto
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Empleado', 'Administrador'])) {
             $_SESSION['error'] = 'No tienes permisos para acceder a esta sección.';
            return $this->redirect('/dashboard'); // Redirigir al dashboard o a 403
        }

        $reviewModel = new Review(); // Usar la clase directamente si ya está en el mismo namespace o importada
        $pendingReviews = $reviewModel->getPendingReviews();
        $reportedReviews = $reviewModel->getReportedReviews();

        return $this->render('dashboard/moderation', [
            'title' => 'Moderación de Reseñas',
            'pendingReviews' => $pendingReviews,
            'reportedReviews' => $reportedReviews,
             'css' => ['moderation'], // Asegurar que el CSS de moderación se cargue
             'js' => ['moderation'] // Asegurar que el JS de moderación se cargue
        ]);
    }

    public function moderateReview() {
        error_log('DashboardController::moderateReview - Iniciando');
        
        // Verificar si el usuario está autenticado y tiene el rol correcto
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Empleado', 'Administrador'])) {
            error_log('DashboardController::moderateReview - No autorizado');
            return $this->jsonResponse(['success' => false, 'message' => 'No autorizado']);
        }

        $rawInput = file_get_contents('php://input');
        error_log('DashboardController::moderateReview - Raw input: ' . $rawInput);
        
        $data = json_decode($rawInput, true);
        error_log('DashboardController::moderateReview - Datos decodificados: ' . print_r($data, true));
        
        if (!isset($data['review_id']) || !isset($data['action'])) {
            error_log('DashboardController::moderateReview - Datos incompletos');
            return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
        }

        try {
            $reviewModel = new Review();
            $moderadorId = $_SESSION['user_id'] ?? null;
            if (!$moderadorId) {
                error_log('DashboardController::moderateReview - ID del moderador no encontrado');
                throw new \Exception("ID del moderador no encontrado en la sesión.");
            }

            error_log('DashboardController::moderateReview - Actualizando estado de la reseña');
            $reviewModel->updateStatus(
                $data['review_id'],
                $data['action'],
                $moderadorId,
                $data['comment'] ?? null
            );

            error_log('DashboardController::moderateReview - Reseña moderada exitosamente');
            return $this->jsonResponse(['success' => true, 'message' => 'Reseña moderada exitosamente']);
        } catch (\Exception $e) {
            error_log('Error en DashboardController::moderateReview: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al moderar la reseña: ' . $e->getMessage()]);
        }
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}