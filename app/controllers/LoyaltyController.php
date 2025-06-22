<?php
namespace App\Controllers;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Core\Container;

/**
 * Controlador para el sistema de fidelización
 */
class LoyaltyController extends BaseController
{
    private $api_url = 'http://localhost:8000';
    
    /**
     * Constructor del controlador
     */
    public function __construct(
        RequestInterface $request, 
        ResponseInterface $response,
        Container $container
    ) {
        parent::__construct($request, $response, $container);
        
        // Configurar URL de la API desde configuración o usar valor por defecto
        $config = $this->container->get('config');
        $this->api_url = $config['loyalty_api_url'] ?? 'http://localhost:8000';
    }
    
    /**
     * Página principal de fidelización
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Agregar log de depuración
        error_log("[LoyaltyController::index] Usuario actual - user_id: $user_id, correo: " . ($_SESSION['correo'] ?? 'N/A'));
        
        $user_profile = $this->getUserProfile($user_id);
        
        $data = [
            'title' => 'Fidelización - Café VT',
            'user_profile' => $user_profile,
            'user_id' => $user_id
        ];
        
        return $this->render('loyalty/index', $data);
    }
    
    /**
     * Página de recompensas
     */
    public function rewards()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $user_profile = $this->getUserProfile($user_id);
        $rewards = $this->getRewards();
        
        $data = [
            'title' => 'Recompensas - Fidelización',
            'user_profile' => $user_profile,
            'rewards' => $rewards,
            'user_id' => $user_id
        ];
        
        return $this->render('loyalty/rewards', $data);
    }
    
    /**
     * Página de perfil
     */
    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $user_profile = $this->getUserProfile($user_id);
        $user_stats = $this->getUserStats($user_id);
        
        $data = [
            'title' => 'Mi Perfil - Fidelización',
            'user_profile' => $user_profile,
            'user_stats' => $user_stats,
            'user_id' => $user_id
        ];
        
        return $this->render('loyalty/profile', $data);
    }
    
    /**
     * Página de transacciones
     */
    public function transactions()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $user_profile = $this->getUserProfile($user_id);
        $transactions = $this->getUserTransactions($user_id);
        
        $data = [
            'title' => 'Transacciones - Fidelización',
            'user_profile' => $user_profile,
            'transactions' => $transactions,
            'user_id' => $user_id
        ];
        
        return $this->render('loyalty/transactions', $data);
    }
    
    /**
     * Obtener perfil del usuario desde la API
     */
    public function getUserProfile($user_id)
    {
        try {
            $response = $this->makeApiRequest("GET", "/api/v1/loyalty/profile/{$user_id}");
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            // Datos por defecto si la API no responde
            return [
                'current_points' => 0,
                'total_points' => 0,
                'current_tier' => 'cafe_bronze',
                'progress_percentage' => 0,
                'next_tier' => 'cafe_silver',
                'points_to_next_tier' => 100,
                'current_benefits' => [
                    '1 punto por cada $1 gastado',
                    'Descuento del 5% en cumpleaños',
                    'Acceso a recompensas básicas'
                ],
                'next_benefits' => [
                    '1.2 puntos por cada $1 gastado',
                    'Descuento del 10% en cumpleaños',
                    'Recompensas exclusivas',
                    'Prioridad en pedidos'
                ],
                'join_date' => date('Y-m-d'),
                'last_visit' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo perfil de usuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas del usuario
     */
    public function getUserStats($user_id)
    {
        try {
            $response = $this->makeApiRequest("GET", "/api/v1/loyalty/stats/{$user_id}");
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            // Datos por defecto
            return [
                'total_visits' => 0,
                'total_spent' => 0,
                'rewards_redeemed' => 0,
                'referrals_count' => 0
            ];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener recompensas disponibles
     */
    public function getRewards()
    {
        try {
            $response = $this->makeApiRequest("GET", "/api/v1/loyalty/rewards");
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            // Recompensas por defecto si la API no responde
            return [
                [
                    'id' => 1,
                    'name' => 'Café Americano Gratis',
                    'description' => 'Café americano gratis para empezar el día',
                    'points_cost' => 1500,
                    'discount_percent' => 100.00,
                    'tier_required' => 'cafe_bronze'
                ],
                [
                    'id' => 2,
                    'name' => '10% Descuento',
                    'description' => '10% de descuento en tu próxima compra',
                    'points_cost' => 1000,
                    'discount_percent' => 10.00,
                    'tier_required' => 'cafe_bronze'
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo recompensas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener transacciones del usuario
     */
    public function getUserTransactions($user_id, $page = 1)
    {
        try {
            $response = $this->makeApiRequest("GET", "/api/v1/loyalty/transactions/{$user_id}?page={$page}");
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'];
            }
            
            return [];
            
        } catch (\Exception $e) {
            error_log("Error obteniendo transacciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Realizar petición a la API
     */
    private function makeApiRequest($method, $endpoint, $data = null)
    {
        $url = $this->api_url . $endpoint;
        
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        } elseif ($method === 'PUT') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        } elseif ($method === 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new \Exception('Error de conexión con la API');
        }
        
        if ($http_code >= 400) {
            throw new \Exception('Error HTTP: ' . $http_code);
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decodificando respuesta JSON');
        }
        
        return $decoded;
    }
    
    /**
     * Verificar si la API está disponible
     */
    public function isApiAvailable()
    {
        try {
            $response = $this->makeApiRequest("GET", "/health");
            return isset($response['status']) && $response['status'] === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener configuración de la API
     */
    public function getApiConfig()
    {
        return [
            'base_url' => $this->api_url,
            'available' => $this->isApiAvailable()
        ];
    }
    
    /**
     * API: Obtener perfil del usuario
     */
    public function getProfileApi($params)
    {
        $user_id = $params['id'] ?? null;
        
        if (!$user_id) {
            $this->json(['success' => false, 'message' => 'ID de usuario requerido'], 400);
            return;
        }
        
        try {
            $profile = $this->getUserProfile($user_id);
            $this->json(['success' => true, 'data' => $profile]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Obtener recompensas disponibles
     */
    public function getRewardsApi()
    {
        try {
            $rewards = $this->getRewards();
            $this->json(['success' => true, 'data' => $rewards]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Obtener transacciones del usuario
     */
    public function getTransactionsApi($params)
    {
        $user_id = $params['id'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        if (!$user_id) {
            $this->json(['success' => false, 'message' => 'ID de usuario requerido'], 400);
            return;
        }
        
        try {
            $transactions = $this->getUserTransactions($user_id, $page);
            $this->json(['success' => true, 'data' => $transactions]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Canjear recompensa
     */
    public function redeem()
    {
        error_log('[DEBUG][redeem] SESSION: ' . print_r($_SESSION, true));
        error_log("[DEBUG][redeem] --- INICIO ---");
        if (!isset($_SESSION['user_id'])) {
            error_log("[DEBUG][redeem] Usuario no autenticado (no hay user_id en sesión)");
            $this->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            return;
        }
        
        try {
            $rawInput = file_get_contents('php://input');
            error_log("[DEBUG][redeem] Raw input: " . $rawInput);
            $input = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[DEBUG][redeem] Error decodificando JSON: " . json_last_error_msg());
                $this->json(['success' => false, 'message' => 'Datos JSON inválidos'], 400);
                return;
            }
            
            $reward_id = $input['reward_id'] ?? null;
            $user_id = $input['user_id'] ?? $_SESSION['user_id'];
            error_log("[DEBUG][redeem] reward_id: " . var_export($reward_id, true) . ", user_id: " . var_export($user_id, true));
            
            if (!$reward_id) {
                error_log("[DEBUG][redeem] Falta reward_id");
                $this->json(['success' => false, 'message' => 'ID de recompensa requerido'], 400);
                return;
            }
            
            error_log("[DEBUG][redeem] Llamando a makeApiRequest con user_id=$user_id, reward_id=$reward_id");
            $response = $this->makeApiRequest("POST", "/api/v1/loyalty/redeem-reward", [
                'user_id' => $user_id,
                'reward_id' => $reward_id
            ]);
            error_log("[DEBUG][redeem] Respuesta de la API: " . var_export($response, true));
            
            // Asegurar que la respuesta sea un array
            if (!is_array($response)) {
                $response = ['success' => true, 'data' => $response];
            }
            
            $this->json($response);
            
        } catch (\Exception $e) {
            error_log("[DEBUG][redeem] Excepción: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Canjear recompensa (alias para compatibilidad)
     */
    public function redeemCoupon()
    {
        return $this->redeem();
    }
    
    /**
     * API: Obtener ID del usuario actual
     */
    public function getCurrentUserId()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            return;
        }
        
        $this->json([
            'success' => true, 
            'data' => [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['nombre'] ?? 'Usuario',
                'email' => $_SESSION['correo'] ?? ''
            ]
        ]);
    }
    
    /**
     * API: Verificar estado de la API de fidelización
     */
    public function checkApiStatus()
    {
        try {
            $isAvailable = $this->isApiAvailable();
            $config = $this->getApiConfig();
            
            $this->json([
                'success' => true,
                'data' => [
                    'available' => $isAvailable,
                    'base_url' => $config['base_url'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Otorgar puntos por compra
     * @param int $user_id ID del usuario
     * @param float $amount Monto de la compra
     * @param string $description Descripción de la compra
     * @return array Respuesta de la API
     */
    public function awardPointsForPurchase($user_id, $amount, $description = '')
    {
        try {
            error_log("[LoyaltyController::awardPointsForPurchase] Otorgando puntos - Usuario: $user_id, Monto: $amount, Descripción: $description");
            
            // Calcular puntos (1 punto por cada $100 CLP)
            $points = floor($amount / 100);
            
            $data = [
                'user_id' => $user_id,
                'points_amount' => $points,
                'transaction_type' => 'earn',
                'description' => $description ?: "Puntos ganados por compra de $" . number_format($amount, 0, ',', '.') . " CLP"
            ];
            
            $response = $this->makeApiRequest("POST", "/api/v1/loyalty/earn-points", $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                error_log("[LoyaltyController::awardPointsForPurchase] Puntos otorgados exitosamente: " . $points);
                return [
                    'success' => true,
                    'points_earned' => $points,
                    'message' => "Se otorgaron $points puntos por tu compra"
                ];
            } else {
                error_log("[LoyaltyController::awardPointsForPurchase] Error en la API: " . json_encode($response));
                return [
                    'success' => false,
                    'message' => 'No se pudieron otorgar puntos'
                ];
            }
            
        } catch (\Exception $e) {
            error_log("[LoyaltyController::awardPointsForPurchase] Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al otorgar puntos: ' . $e->getMessage()
            ];
        }
    }
} 