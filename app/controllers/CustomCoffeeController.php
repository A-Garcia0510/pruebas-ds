<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Core\Database\DatabaseInterface;
use App\Models\CustomCoffee\CustomCoffeeComponent;
use App\Models\CustomCoffee\CustomCoffeeRecipe;
use App\Models\CustomCoffee\CustomCoffeeOrder;
use App\Models\CustomCoffee\CoffeeBuilder;
use App\Models\CustomCoffee\CoffeeDirector;
use App\Core\Controller;
use App\Models\CustomCoffee\RecetaModel;
use App\Models\CustomCoffee\ComponenteModel;
use App\Models\CustomCoffee\OrderModel;
use App\Core\Database\Database;
use Exception;

/**
 * Controlador para gestionar los cafés personalizados
 */
class CustomCoffeeController extends BaseController
{
    private CustomCoffeeComponent $componentModel;
    private CustomCoffeeRecipe $recipeModel;
    private CustomCoffeeOrder $orderModel;
    private CoffeeBuilder $coffeeBuilder;
    private CoffeeDirector $coffeeDirector;

    /**
     * Constructor del controlador
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        Container $container,
        CustomCoffeeComponent $componentModel,
        CustomCoffeeRecipe $recipeModel,
        CustomCoffeeOrder $orderModel,
        CoffeeBuilder $coffeeBuilder,
        CoffeeDirector $coffeeDirector
    ) {
        parent::__construct($request, $response, $container);
        $this->componentModel = $componentModel;
        $this->recipeModel = $recipeModel;
        $this->orderModel = $orderModel;
        $this->coffeeBuilder = $coffeeBuilder;
        $this->coffeeDirector = $coffeeDirector;
    }

    /**
     * Muestra la página principal del constructor de café
     */
    public function index()
    {
        // Obtener todos los componentes agrupados por tipo
        $componentes = $this->componentModel->getComponentesAgrupados();

        // Verificar si el usuario está autenticado
        $isLoggedIn = isset($_SESSION['correo']);

        return $this->render('custom-coffee/builder', [
            'title' => 'Crea tu Café Personalizado - Ethos Coffee',
            'description' => 'Diseña tu café perfecto con nuestros ingredientes de alta calidad',
            'componentes' => $componentes,
            'isLoggedIn' => $isLoggedIn,
            'css' => ['custom-coffee'],
            'js' => ['coffee-builder']
        ]);
    }

    /**
     * API para obtener los componentes disponibles
     */
    public function getComponentes()
    {
        try {
            $params = $this->request->getQueryParams();
            $tipo = $params['tipo'] ?? null;
            
            // Obtener componentes
            $componentes = $tipo 
                ? $this->componentModel->getComponentesByTipo($tipo)
                : $this->componentModel->getAllComponentes();

            // Verificar que se obtuvieron componentes
            if (empty($componentes)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No se encontraron componentes disponibles'
                ], 404);
            }

            // Asegurarse de que los componentes tengan el formato correcto
            $componentes = array_map(function($comp) {
                return [
                    'id' => $comp['componente_ID'],
                    'nombre' => $comp['nombre'],
                    'tipo' => $comp['tipo'],
                    'precio' => floatval($comp['precio']),
                    'stock' => intval($comp['stock']),
                    'descripcion' => $comp['descripcion'] ?? ''
                ];
            }, $componentes);

            return $this->json([
                'success' => true,
                'componentes' => $componentes
            ]);
        } catch (\Exception $e) {
            error_log("Error en CustomCoffeeController::getComponentes: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener componentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API para crear una receta personalizada
     */
    public function saveRecipe()
    {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para guardar recetas'
            ], 401);
        }

        try {
            $data = $this->request->getBody();
            
            // Validar datos requeridos
            if (!isset($data['nombre']) || !isset($data['componentes']) || !isset($data['precio_total'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Datos incompletos: se requiere nombre, componentes y precio_total'
                ], 400);
            }

            // Validar que haya al menos un componente
            if (empty($data['componentes'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'La receta debe tener al menos un componente'
                ], 400);
            }

            // Validar que haya una base
            $hasBase = false;
            $componentes = [];
            foreach ($data['componentes'] as $componente) {
                if (!isset($componente['componente_ID']) || !isset($componente['cantidad']) || !isset($componente['precio'])) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Datos de componente incompletos'
                    ], 400);
                }

                if ($componente['tipo'] === 'base') {
                    $hasBase = true;
                }

                $componentes[] = [
                    'componente_ID' => intval($componente['componente_ID']),
                    'cantidad' => floatval($componente['cantidad']),
                    'precio' => floatval($componente['precio'])
                ];
            }

            if (!$hasBase) {
                return $this->json([
                    'success' => false,
                    'message' => 'La receta debe tener una base de café'
                ], 400);
            }

            // Obtener el ID del usuario
            $usuarioId = $_SESSION['user_id'];
            $precioTotal = floatval($data['precio_total']);

            // Guardar la receta
            $recetaId = $this->recipeModel->guardarReceta(
                $usuarioId,
                trim($data['nombre']),
                $componentes,
                $precioTotal
            );

            if ($recetaId) {
                return $this->json([
                    'success' => true,
                    'message' => 'Receta guardada exitosamente',
                    'receta_id' => $recetaId
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Error al guardar la receta'
            ], 500);

        } catch (\Exception $e) {
            error_log("Error en CustomCoffeeController::saveRecipe: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al procesar la receta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra las recetas guardadas del usuario
     */
    public function recipes()
    {
        // Verificar si el usuario está autenticado
        $isLoggedIn = isset($_SESSION['correo']);
        
        if (!$isLoggedIn) {
            $_SESSION['message'] = 'Debes iniciar sesión para ver tus recetas';
            $_SESSION['message_type'] = 'error';
            return $this->redirect('/login');
        }

        $usuarioId = $_SESSION['user_id'];
        $recetas = $this->recipeModel->getRecetasByUsuario($usuarioId);

        return $this->render('custom-coffee/recipes', [
            'title' => 'Mis Recetas - Ethos Coffee',
            'description' => 'Tus recetas de café personalizadas',
            'recetas' => $recetas,
            'isLoggedIn' => $isLoggedIn,
            'css' => ['custom-coffee']
        ]);
    }

    /**
     * API para realizar un pedido de café personalizado
     */
    public function placeOrder()
    {
        try {
            error_log("[CustomCoffeeController::placeOrder] ===== INICIO DE PLACE ORDER =====");
            
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                error_log("[CustomCoffeeController::placeOrder] Error: Usuario no autenticado - SESSION: " . print_r($_SESSION, true));
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $usuarioId = (int)$_SESSION['user_id'];
            error_log("[CustomCoffeeController::placeOrder] Usuario autenticado - ID: $usuarioId");

            // Obtener y validar datos del request
            $rawData = file_get_contents('php://input');
            error_log("[CustomCoffeeController::placeOrder] Datos raw recibidos: " . $rawData);

            if (empty($rawData)) {
                error_log("[CustomCoffeeController::placeOrder] Error: No se recibieron datos");
                return $this->json([
                    'success' => false,
                    'message' => 'No se recibieron datos'
                ], 400);
            }

            $data = json_decode($rawData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[CustomCoffeeController::placeOrder] Error al decodificar JSON: " . json_last_error_msg() . " - Raw data: " . $rawData);
                return $this->json([
                    'success' => false,
                    'message' => 'Datos JSON inválidos: ' . json_last_error_msg()
                ], 400);
            }

            error_log("[CustomCoffeeController::placeOrder] Datos decodificados: " . print_r($data, true));

            // Validar que existe precio_total
            if (!isset($data['precio_total'])) {
                error_log("[CustomCoffeeController::placeOrder] Error: Falta precio_total");
                return $this->json([
                    'success' => false,
                    'message' => 'Falta el precio total'
                ], 400);
            }

            $precioTotal = filter_var($data['precio_total'], FILTER_VALIDATE_FLOAT);
            if ($precioTotal === false) {
                error_log("[CustomCoffeeController::placeOrder] Error: Precio total inválido - Valor recibido: " . $data['precio_total']);
                return $this->json([
                    'success' => false,
                    'message' => 'Precio total inválido'
                ], 400);
            }

            error_log("[CustomCoffeeController::placeOrder] Precio total validado: " . $precioTotal);

            // Determinar si es un pedido directo o desde receta
            if (isset($data['receta_id'])) {
                // Pedido desde receta guardada
                $recetaId = filter_var($data['receta_id'], FILTER_VALIDATE_INT);
                if ($recetaId === false) {
                    error_log("[CustomCoffeeController::placeOrder] Error: ID de receta inválido");
                    return $this->json([
                        'success' => false,
                        'message' => 'ID de receta inválido'
                    ], 400);
                }

                // Verificar que la receta pertenece al usuario
                if (!$this->recipeModel->perteneceAUsuario($recetaId, $usuarioId)) {
                    error_log("[CustomCoffeeController::placeOrder] Error: La receta $recetaId no pertenece al usuario $usuarioId");
                    return $this->json([
                        'success' => false,
                        'message' => 'No tienes permiso para realizar este pedido'
                    ], 403);
                }

                error_log("[CustomCoffeeController::placeOrder] Creando pedido desde receta - usuarioId: $usuarioId, recetaId: $recetaId, precioTotal: $precioTotal");
                $pedidoId = $this->orderModel->crearPedido($usuarioId, $recetaId, $precioTotal);

            } elseif (isset($data['componentes'])) {
                // Pedido directo desde el builder
                if (!is_array($data['componentes']) || empty($data['componentes'])) {
                    error_log("[CustomCoffeeController::placeOrder] Error: Componentes inválidos o vacíos");
                    return $this->json([
                        'success' => false,
                        'message' => 'Debes seleccionar al menos un componente'
                    ], 400);
                }

                error_log("[CustomCoffeeController::placeOrder] Creando pedido directo - usuarioId: $usuarioId, componentes: " . print_r($data['componentes'], true));
                $pedidoId = $this->orderModel->crearPedidoDirecto($usuarioId, $data['componentes'], $precioTotal);

            } else {
                error_log("[CustomCoffeeController::placeOrder] Error: No se especificó receta_id ni componentes");
                return $this->json([
                    'success' => false,
                    'message' => 'Debes especificar una receta o componentes'
                ], 400);
            }
            
            if ($pedidoId) {
                error_log("[CustomCoffeeController::placeOrder] Pedido creado exitosamente - ID: $pedidoId");
                // Otorgar puntos de fidelización
                try {
                    error_log("[CustomCoffeeController::placeOrder] Llamando a LoyaltyController->awardPointsForPurchase con usuarioId=$usuarioId, precioTotal=$precioTotal, pedidoId=$pedidoId");
                    $loyaltyController = $this->container->resolve(\App\Controllers\LoyaltyController::class);
                    $loyaltyResponse = $loyaltyController->awardPointsForPurchase(
                        $usuarioId,
                        $precioTotal,
                        "Compra de café personalizado #{$pedidoId}"
                    );
                    error_log("[CustomCoffeeController::placeOrder] Respuesta de LoyaltyController: " . print_r($loyaltyResponse, true));
                    
                    // Si todo va bien, confirmar la transacción
                    $this->orderModel->commit();

                    if ($loyaltyResponse['success']) {
                        return $this->json([
                            'success' => true,
                            'message' => 'Pedido realizado con éxito y puntos de fidelización otorgados.',
                            'pedido_id' => $pedidoId,
                            'loyalty_points' => $loyaltyResponse['points_earned'] ?? 0,
                            'loyalty_response' => $loyaltyResponse
                        ]);
                    } else {
                        // La compra se realizó pero hubo error en fidelización
                        return $this->json([
                            'success' => true,
                            'message' => 'Pedido realizado con éxito',
                            'pedido_id' => $pedidoId,
                            'loyalty_warning' => 'No se pudieron otorgar puntos de fidelización'
                        ]);
                    }

                } catch (Exception $loyaltyException) {
                    // Si la API de fidelización falla, el pedido se completa igual
                    $this->orderModel->commit();
                    error_log("[CustomCoffeeController::placeOrder] Error en la API de fidelización, pero pedido #{$pedidoId} completado: " . $loyaltyException->getMessage());
                    return $this->json([
                        'success' => true,
                        'message' => 'Pedido realizado con éxito',
                        'pedido_id' => $pedidoId,
                        'loyalty_warning' => 'No se pudieron otorgar puntos de fidelización'
                    ]);
                }

            } else {
                $this->orderModel->rollBack();
                error_log("[CustomCoffeeController::placeOrder] Error al crear el pedido");
                return $this->json([
                    'success' => false,
                    'message' => 'Error al crear el pedido'
                ], 500);
            }

        } catch (\Exception $e) {
            // Asegurarse de revertir si algo falla antes del commit
            if ($this->orderModel->inTransaction()) {
                $this->orderModel->rollBack();
            }
            error_log("[CustomCoffeeController::placeOrder] Error: " . $e->getMessage());
            error_log("[CustomCoffeeController::placeOrder] Stack trace: " . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], $e->getCode() >= 400 ? $e->getCode() : 500);
        }
    }

    /**
     * Muestra los detalles de un pedido específico
     * @param int $id ID del pedido
     */
    public function orderDetails($id)
    {
        if (!isset($_SESSION['correo'])) {
            $_SESSION['message'] = 'Debes iniciar sesión para ver los detalles del pedido';
            $_SESSION['message_type'] = 'error';
            return $this->redirect('/login');
        }

        $usuarioId = $_SESSION['user_id'];

        // Verificar que el pedido pertenece al usuario
        if (!$this->orderModel->perteneceAUsuario($id, $usuarioId)) {
            $_SESSION['message'] = 'Pedido no encontrado';
            $_SESSION['message_type'] = 'error';
            return $this->redirect('/custom-coffee/orders');
        }

        $pedido = $this->orderModel->getPedidoById($id);

        return $this->render('custom-coffee/order-details', [
            'title' => 'Detalles del Pedido - Ethos Coffee',
            'description' => 'Detalles de tu pedido de café personalizado',
            'pedido' => $pedido,
            'css' => ['custom-coffee']
        ]);
    }

    /**
     * API para eliminar una receta
     */
    public function deleteRecipe($id)
    {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para eliminar recetas'
                ], 401);
            }

            $usuarioId = $_SESSION['user_id'];
            
            // Validar que el ID sea un número válido
            if (!is_numeric($id)) {
                return $this->json([
                    'success' => false,
                    'message' => 'ID de receta inválido'
                ], 400);
            }
            
            // Verificar que la receta existe
            $receta = $this->recipeModel->getRecetaById($id);
            if (!$receta) {
                return $this->json([
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ], 404);
            }
            
            // Verificar que la receta pertenece al usuario
            if (!$this->recipeModel->perteneceAUsuario($id, $usuarioId)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta receta'
                ], 403);
            }

            // Verificar si la receta tiene pedidos asociados
            if ($this->recipeModel->tienePedidos($id)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la receta porque tiene pedidos asociados'
                ], 400);
            }

            // Eliminar la receta (marcándola como inactiva)
            if (!$this->recipeModel->eliminarReceta($id, $usuarioId)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al eliminar la receta'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => 'Receta eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            error_log("Error en CustomCoffeeController::deleteRecipe: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API para cancelar un pedido
     */
    public function cancel($orderId)
    {
        if (!isset($_SESSION['correo'])) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {
            $usuarioId = $_SESSION['user_id'];
            
            // Verificar que el pedido pertenece al usuario
            $pedido = $this->orderModel->getPedidoById($orderId);
            if (!$pedido || $pedido['usuario_ID'] != $usuarioId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado o no tienes permisos para cancelarlo'
                ], 404);
            }

            // Verificar que el pedido esté en estado pendiente
            if ($pedido['estado'] !== 'pendiente') {
                return $this->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar pedidos pendientes'
                ], 400);
            }

            // Cancelar el pedido
            $resultado = $this->orderModel->cancelarPedido($orderId);
            
            if ($resultado) {
                return $this->json([
                    'success' => true,
                    'message' => 'Pedido cancelado exitosamente'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al cancelar el pedido'
                ], 500);
            }

        } catch (\Exception $e) {
            error_log("Error cancelando pedido: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Muestra la lista de pedidos del usuario
     */
    public function orders()
    {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['message'] = 'Debes iniciar sesión para ver tus pedidos';
            $_SESSION['message_type'] = 'error';
            return $this->redirect('/login');
        }

        try {
            $usuarioId = $_SESSION['user_id'];
            $pedidos = $this->orderModel->getPedidosByUsuario($usuarioId);

            // Verificar que se obtuvieron los pedidos
            if ($pedidos === null) {
                throw new \Exception("Error al obtener los pedidos");
            }

            // Procesar los detalles de cada pedido
            foreach ($pedidos as &$pedido) {
                if (isset($pedido['detalles_json'])) {
                    $detalles = json_decode($pedido['detalles_json'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $pedido['detalles'] = $detalles;
                    } else {
                        error_log("Error decodificando JSON de detalles del pedido {$pedido['orden_ID']}: " . json_last_error_msg());
                        $pedido['detalles'] = [];
                    }
                    unset($pedido['detalles_json']);
                } else {
                    $pedido['detalles'] = [];
                }
            }

            return $this->render('custom-coffee/orders', [
                'title' => 'Mis Pedidos - Ethos Coffee',
                'description' => 'Historial de tus pedidos de café personalizado',
                'pedidos' => $pedidos,
                'isLoggedIn' => true,
                'css' => ['custom-coffee']
            ]);
        } catch (\Exception $e) {
            error_log("Error en CustomCoffeeController::orders: " . $e->getMessage());
            $_SESSION['message'] = 'Error al cargar los pedidos: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
            return $this->redirect('/custom-coffee');
        }
    }
} 