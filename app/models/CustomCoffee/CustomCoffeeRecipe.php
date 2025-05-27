<?php
namespace App\Models\CustomCoffee;

use App\Core\Database\DatabaseInterface;

class CustomCoffeeRecipe {
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }

    /**
     * Guarda una nueva receta de café
     * @param int $usuarioId ID del usuario
     * @param string $nombre Nombre de la receta
     * @param array $componentes Array de componentes con sus cantidades
     * @param float $precioTotal Precio total de la receta
     * @return int|null ID de la receta creada
     */
    public function guardarReceta(int $usuarioId, string $nombre, array $componentes, float $precioTotal): ?int {
        try {
            $this->db->beginTransaction();

            // Validar stock antes de crear la receta
            foreach ($componentes as $componente) {
                $stockActual = $this->db->fetchOne(
                    "SELECT stock FROM custom_coffee_components WHERE componente_ID = ?",
                    [$componente['componente_ID']]
                );

                if (!$stockActual || $stockActual['stock'] < $componente['cantidad']) {
                    throw new \Exception("Stock insuficiente para el componente ID: " . $componente['componente_ID']);
                }
            }

            // Insertar la receta
            $recetaId = $this->db->insert('custom_coffee_recipes', [
                'usuario_ID' => $usuarioId,
                'nombre' => $nombre,
                'precio_total' => $precioTotal,
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'estado' => 'activo'
            ]);

            if (!$recetaId) {
                throw new \Exception("Error al crear la receta");
            }

            // Insertar los detalles de la receta
            foreach ($componentes as $componente) {
                $this->db->insert('custom_coffee_recipe_details', [
                    'receta_ID' => $recetaId,
                    'componente_ID' => $componente['componente_ID'],
                    'cantidad' => $componente['cantidad'],
                    'precio_unitario' => $componente['precio']
                ]);
            }

            $this->db->commit();
            return $recetaId;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error en guardarReceta: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene las recetas de un usuario
     * @param int $usuarioId ID del usuario
     * @return array
     */
    public function getRecetasByUsuario(int $usuarioId): array {
        $sql = "SELECT r.*, 
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'nombre', c.nombre,
                        'cantidad', d.cantidad,
                        'precio_unitario', d.precio_unitario,
                        'tipo', c.tipo,
                        'unidad', c.unidad,
                        'subtotal', (d.cantidad * d.precio_unitario)
                    ) SEPARATOR '||'
                ) as detalles_json
                FROM custom_coffee_recipes r
                LEFT JOIN custom_coffee_recipe_details d ON r.receta_ID = d.receta_ID
                LEFT JOIN custom_coffee_components c ON d.componente_ID = c.componente_ID
                WHERE r.usuario_ID = ? AND r.estado = 'activo'
                GROUP BY r.receta_ID
                ORDER BY r.fecha_creacion DESC";

        $recetas = $this->db->fetchAll($sql, [$usuarioId]);

        foreach ($recetas as &$receta) {
            if (!empty($receta['detalles_json'])) {
                $detallesArray = explode('||', $receta['detalles_json']);
                $receta['detalles'] = [];
                $precioTotal = 0;

                foreach ($detallesArray as $detalle) {
                    $decoded = json_decode($detalle, true);
                    if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                        $receta['detalles'][] = $decoded;
                        $precioTotal += floatval($decoded['subtotal']);
                    } else {
                        error_log("Error decodificando detalle: " . json_last_error_msg() . " - Detalle: " . $detalle);
                    }
                }
                
                // Calcular el IVA y el precio total
                $subtotal = $precioTotal;
                $iva = $subtotal * 0.19;
                $receta['precio_total'] = round($subtotal + $iva, 2);
            } else {
                $receta['detalles'] = [];
                $receta['precio_total'] = 0;
            }
            unset($receta['detalles_json']);
        }

        return $recetas;
    }

    /**
     * Obtiene una receta específica con sus detalles
     * @param int $recetaId ID de la receta
     * @return array|null
     */
    public function getRecetaById(int $recetaId): ?array {
        $receta = $this->db->fetchOne(
            "SELECT * FROM custom_coffee_recipes WHERE receta_ID = ? AND estado = 'activo'",
            [$recetaId]
        );

        if (!$receta) {
            return null;
        }

        $detalles = $this->db->fetchAll(
            "SELECT d.*, c.nombre, c.tipo, c.cantidad_unidad, c.unidad
             FROM custom_coffee_recipe_details d
             JOIN custom_coffee_components c ON d.componente_ID = c.componente_ID
             WHERE d.receta_ID = ?",
            [$recetaId]
        );

        $receta['detalles'] = $detalles;
        return $receta;
    }

    /**
     * Elimina una receta (marcándola como inactiva)
     * @param int $recetaId ID de la receta
     * @param int $usuarioId ID del usuario (para verificación)
     * @return bool
     */
    public function eliminarReceta(int $recetaId, int $usuarioId): bool {
        try {
            // Verificar si hay pedidos activos
            $pedidosActivos = $this->db->fetchOne(
                "SELECT COUNT(*) as total 
                 FROM custom_coffee_orders 
                 WHERE receta_ID = ? 
                 AND estado IN ('pendiente', 'preparando')",
                [$recetaId]
            );

            if ($pedidosActivos && $pedidosActivos['total'] > 0) {
                throw new \Exception("No se puede eliminar la receta porque tiene pedidos activos");
            }

            // Si no hay pedidos activos, proceder con la eliminación
            return $this->db->update(
                'custom_coffee_recipes',
                ['estado' => 'inactivo'],
                'receta_ID = ? AND usuario_ID = ?',
                [$recetaId, $usuarioId]
            );
        } catch (\Exception $e) {
            error_log("Error en eliminarReceta: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si una receta pertenece a un usuario
     * @param int $recetaId ID de la receta
     * @param int $usuarioId ID del usuario
     * @return bool
     */
    public function perteneceAUsuario(int $recetaId, int $usuarioId): bool {
        $receta = $this->db->fetchOne(
            "SELECT receta_ID FROM custom_coffee_recipes 
             WHERE receta_ID = ? AND usuario_ID = ? AND estado = 'activo'",
            [$recetaId, $usuarioId]
        );
        
        return $receta !== null;
    }

    /**
     * Verifica si una receta tiene pedidos activos
     * @param int $recetaId ID de la receta
     * @return bool True si la receta tiene pedidos activos, false en caso contrario
     */
    public function tienePedidos(int $recetaId): bool
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as total 
             FROM custom_coffee_orders 
             WHERE receta_ID = ? 
             AND estado IN ('pendiente', 'preparando')",
            [$recetaId]
        );
        return $result && $result['total'] > 0;
    }
} 