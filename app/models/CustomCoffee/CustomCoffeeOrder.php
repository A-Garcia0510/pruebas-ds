<?php
namespace App\Models\CustomCoffee;

use App\Core\Database\DatabaseInterface;

class CustomCoffeeOrder {
    private DatabaseInterface $db;
    private CustomCoffeeComponent $componentModel;

    public function __construct(DatabaseInterface $db, CustomCoffeeComponent $componentModel) {
        $this->db = $db;
        $this->componentModel = $componentModel;
    }

    // --- Métodos de Transacción ---
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollBack() {
        $this->db->rollBack();
    }

    public function inTransaction(): bool {
        return $this->db->inTransaction();
    }
    // --- Fin Métodos de Transacción ---

    /**
     * Crea un nuevo pedido de café personalizado
     * @param int $usuarioId ID del usuario
     * @param int $recetaId ID de la receta
     * @param float $precioTotal Precio total del pedido
     * @return int|null ID del pedido creado
     */
    public function crearPedido(int $usuarioId, int $recetaId, float $precioTotal): ?int {
        try {
            error_log("[CustomCoffeeOrder::crearPedido] Iniciando creación de pedido - Usuario: $usuarioId, Receta: $recetaId, Precio: $precioTotal");
            
            // Validar que el usuario existe
            $usuario = $this->db->fetchOne(
                "SELECT usuario_ID FROM Usuario WHERE usuario_ID = ?",
                [$usuarioId]
            );
            if (!$usuario) {
                error_log("[CustomCoffeeOrder::crearPedido] Error: Usuario no encontrado (ID: $usuarioId)");
                throw new \Exception("Usuario no encontrado");
            }

            // Validar que la receta existe y está activa
            $receta = $this->db->fetchOne(
                "SELECT receta_ID, precio_total FROM custom_coffee_recipes WHERE receta_ID = ? AND estado = 'activo'",
                [$recetaId]
            );
            if (!$receta) {
                error_log("[CustomCoffeeOrder::crearPedido] Error: Receta no encontrada o inactiva (ID: $recetaId)");
                throw new \Exception("Receta no encontrada o inactiva");
            }

            // Validar que el precio coincide
            $precioReceta = floatval($receta['precio_total']);
            $precioRecibido = floatval($precioTotal);
            
            error_log("[CustomCoffeeOrder::crearPedido] Precios - Receta: $precioReceta, Recibido: $precioRecibido");
            
            // Convertir el precio recibido a decimal (dividir por 1000)
            $precioRecibido = $precioRecibido / 1000;
            
            // Normalizar los precios a 2 decimales
            $precioReceta = round($precioReceta, 2);
            $precioRecibido = round($precioRecibido, 2);
            
            error_log("[CustomCoffeeOrder::crearPedido] Precios normalizados - Receta: $precioReceta, Recibido: $precioRecibido");
            
            // Comparación exacta después de normalizar
            if ($precioReceta !== $precioRecibido) {
                error_log("[CustomCoffeeOrder::crearPedido] Error: Precio no coincide - Recibido: $precioRecibido, Receta: $precioReceta");
                throw new \Exception("El precio total no coincide con la receta (Recibido: $precioRecibido, Receta: $precioReceta)");
            }

            // La transacción se manejará desde el controlador
            // $this->db->beginTransaction();

            try {
                // Obtener los componentes de la receta y validar stock
                $componentes = $this->db->fetchAll(
                    "SELECT r.componente_ID, r.cantidad, c.precio, c.nombre, c.stock
                     FROM custom_coffee_recipe_details r
                     JOIN custom_coffee_components c ON r.componente_ID = c.componente_ID
                     WHERE r.receta_ID = ? AND c.estado = 'activo'",
                    [$recetaId]
                );

                if (empty($componentes)) {
                    error_log("[CustomCoffeeOrder::crearPedido] Error: No se encontraron componentes para la receta $recetaId");
                    throw new \Exception("No se encontraron componentes para la receta");
                }

                // Validar stock antes de crear el pedido
                foreach ($componentes as $componente) {
                    if ($componente['stock'] < $componente['cantidad']) {
                        error_log("[CustomCoffeeOrder::crearPedido] Error: Stock insuficiente para {$componente['nombre']} (ID: {$componente['componente_ID']}) - Stock: {$componente['stock']}, Requerido: {$componente['cantidad']}");
                        throw new \Exception("Stock insuficiente para {$componente['nombre']}");
                    }
                }

                // Crear el pedido
                $pedidoId = $this->db->insert('custom_coffee_orders', [
                    'usuario_ID' => $usuarioId,
                    'receta_ID' => $recetaId,
                    'fecha_pedido' => date('Y-m-d H:i:s'),
                    'estado' => 'pendiente',
                    'precio_total' => $precioTotal
                ]);

                if (!$pedidoId) {
                    error_log("[CustomCoffeeOrder::crearPedido] Error: No se pudo insertar el pedido");
                    throw new \Exception("Error al crear el pedido");
                }

                error_log("[CustomCoffeeOrder::crearPedido] Pedido creado con ID: $pedidoId");

                // Insertar los detalles del pedido
                foreach ($componentes as $componente) {
                    $detalleId = $this->db->insert('custom_coffee_order_details', [
                        'orden_ID' => $pedidoId,
                        'componente_ID' => $componente['componente_ID'],
                        'cantidad' => $componente['cantidad'],
                        'precio_unitario' => $componente['precio']
                    ]);

                    if (!$detalleId) {
                        error_log("[CustomCoffeeOrder::crearPedido] Error: No se pudo insertar el detalle para el componente {$componente['componente_ID']}");
                        throw new \Exception("Error al crear los detalles del pedido");
                    }
                }

                // La transacción se manejará desde el controlador
                // $this->db->commit();
                error_log("[CustomCoffeeOrder::crearPedido] Pedido creado exitosamente - ID: $pedidoId");
                return $pedidoId;

            } catch (\Exception $e) {
                // El rollback se manejará desde el controlador
                // $this->db->rollback();
                error_log("[CustomCoffeeOrder::crearPedido] Error en la transacción: " . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            error_log("[CustomCoffeeOrder::crearPedido] Error general: " . $e->getMessage());
            error_log("[CustomCoffeeOrder::crearPedido] Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Crea un nuevo pedido directo desde el constructor
     * @param int $usuarioId ID del usuario
     * @param array $componentes Array de componentes con sus cantidades
     * @param float $precioTotal Precio total del pedido
     * @return int|null ID del pedido creado
     */
    public function crearPedidoDirecto(int $usuarioId, array $componentes, float $precioTotal): ?int {
        try {
            // La transacción se manejará desde el controlador
            // $this->db->beginTransaction();

            // Validar stock antes de crear el pedido
            foreach ($componentes as $componente) {
                $cantidad = intval($componente['cantidad'] ?? 1);
                $stockActual = $this->db->fetchOne(
                    "SELECT stock FROM custom_coffee_components WHERE componente_ID = ? AND estado = 'activo'",
                    [$componente['componente_ID']]
                );

                if (!$stockActual || $stockActual['stock'] < $cantidad) {
                    throw new \Exception("No hay stock suficiente para el componente ID: {$componente['componente_ID']}");
                }
            }

            // Crear el pedido
            $pedidoId = $this->db->insert(
                'custom_coffee_orders',
                [
                    'usuario_ID' => $usuarioId,
                    'receta_ID' => null,
                    'precio_total' => $precioTotal,
                    'estado' => 'pendiente',
                    'fecha_pedido' => date('Y-m-d H:i:s')
                ]
            );

            if (!$pedidoId) {
                throw new \Exception("Error al crear el pedido");
            }

            // Crear los detalles del pedido
            foreach ($componentes as $componente) {
                $cantidad = intval($componente['cantidad'] ?? 1);
                
                // Insertar detalle
                $this->db->insert(
                    'custom_coffee_order_details',
                    [
                        'orden_ID' => $pedidoId,
                        'componente_ID' => $componente['componente_ID'],
                        'cantidad' => $cantidad,
                        'precio_unitario' => $componente['precio']
                    ]
                );
            }

            // La transacción se manejará desde el controlador
            // $this->db->commit();
            return $pedidoId;

        } catch (\Exception $e) {
            // El rollback se manejará desde el controlador
            // $this->db->rollBack();
            error_log("Error en crearPedidoDirecto: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los pedidos de un usuario
     * @param int $usuarioId ID del usuario
     * @return array
     */
    public function getPedidosByUsuario(int $usuarioId): array {
        $sql = "SELECT o.*, 
                r.nombre as nombre_receta,
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'nombre', c.nombre,
                        'cantidad', d.cantidad,
                        'precio', d.precio_unitario
                    )
                ) as detalles_json
         FROM custom_coffee_orders o
         LEFT JOIN custom_coffee_recipes r ON o.receta_ID = r.receta_ID
         LEFT JOIN custom_coffee_order_details d ON o.orden_ID = d.orden_ID
         LEFT JOIN custom_coffee_components c ON d.componente_ID = c.componente_ID
         WHERE o.usuario_ID = ?
         GROUP BY o.orden_ID
         ORDER BY o.fecha_pedido DESC";

        $pedidos = $this->db->fetchAll($sql, [$usuarioId]);
        
        // Procesar los detalles JSON
        foreach ($pedidos as &$pedido) {
            if (!empty($pedido['detalles_json'])) {
                $pedido['detalles'] = array_map(function($detalle) {
                    return json_decode($detalle, true);
                }, explode(',', $pedido['detalles_json']));
            } else {
                $pedido['detalles'] = [];
            }
            unset($pedido['detalles_json']);
        }

        return $pedidos;
    }

    /**
     * Obtiene un pedido específico con sus detalles
     * @param int $pedidoId ID del pedido
     * @return array|null
     */
    public function getPedidoById(int $pedidoId): ?array {
        error_log("[CustomCoffeeOrder::getPedidoById] Obteniendo pedido ID: " . $pedidoId);
        
        $pedido = $this->db->fetchOne(
            "SELECT o.orden_ID, o.usuario_ID, o.receta_ID, o.fecha_pedido, o.estado,
                    o.precio_total,
                    r.nombre as nombre_receta
             FROM custom_coffee_orders o
             LEFT JOIN custom_coffee_recipes r ON o.receta_ID = r.receta_ID
             WHERE o.orden_ID = ?",
            [$pedidoId]
        );

        error_log("[CustomCoffeeOrder::getPedidoById] Pedido obtenido: " . print_r($pedido, true));

        if (!$pedido) {
            error_log("[CustomCoffeeOrder::getPedidoById] Pedido no encontrado");
            return null;
        }

        // Asegurar que el precio sea un número y se maneje correctamente
        $pedido['precio_total'] = round(floatval($pedido['precio_total']), 2);
        error_log("[CustomCoffeeOrder::getPedidoById] Precio total convertido: " . $pedido['precio_total']);

        // Si el pedido tiene una receta, obtener los detalles de la receta
        if ($pedido['receta_ID']) {
            $detalles = $this->db->fetchAll(
                "SELECT d.componente_ID, d.cantidad,
                        d.precio_unitario,
                        c.nombre, c.tipo
                 FROM custom_coffee_recipe_details d
                 JOIN custom_coffee_components c ON d.componente_ID = c.componente_ID
                 WHERE d.receta_ID = ?",
                [$pedido['receta_ID']]
            );
        } else {
            // Si es un pedido directo, obtener los detalles del pedido
            $detalles = $this->db->fetchAll(
                "SELECT d.componente_ID, d.cantidad,
                        d.precio_unitario,
                        c.nombre, c.tipo
                 FROM custom_coffee_order_details d
                 JOIN custom_coffee_components c ON d.componente_ID = c.componente_ID
                 WHERE d.orden_ID = ?",
                [$pedidoId]
            );
        }

        error_log("[CustomCoffeeOrder::getPedidoById] Detalles obtenidos: " . print_r($detalles, true));

        $pedido['detalles'] = $detalles;
        return $pedido;
    }

    /**
     * Actualiza el estado de un pedido
     * @param int $pedidoId ID del pedido
     * @param string $estado Nuevo estado
     * @return bool
     */
    public function actualizarEstado(int $pedidoId, string $estado): bool {
        return $this->db->update(
            'custom_coffee_orders',
            ['estado' => $estado],
            'orden_ID = ?',
            [$pedidoId]
        );
    }

    /**
     * Cancela un pedido y restaura el stock
     * @param int $pedidoId ID del pedido
     * @return bool
     */
    public function cancelarPedido(int $pedidoId): bool {
        try {
            $this->db->beginTransaction();

            // Obtener la receta y sus componentes
            $pedido = $this->getPedidoById($pedidoId);
            if (!$pedido || $pedido['estado'] !== 'pendiente') {
                throw new \Exception("Pedido no encontrado o no se puede cancelar");
            }

            // Actualizar estado del pedido (el trigger se encargará de restaurar el stock)
            $this->actualizarEstado($pedidoId, 'cancelado');

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("[CustomCoffeeOrder::cancelarPedido] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un pedido pertenece a un usuario
     * @param int $pedidoId ID del pedido
     * @param int $usuarioId ID del usuario
     * @return bool
     */
    public function perteneceAUsuario(int $pedidoId, int $usuarioId): bool {
        $pedido = $this->db->fetchOne(
            "SELECT orden_ID FROM custom_coffee_orders 
             WHERE orden_ID = ? AND usuario_ID = ?",
            [$pedidoId, $usuarioId]
        );
        
        return $pedido !== null;
    }
} 