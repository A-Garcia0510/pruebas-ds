<?php
namespace App\Models\CustomCoffee;

use App\Core\Database\DatabaseInterface;

class CustomCoffeeComponent {
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los componentes disponibles de un tipo específico
     * @param string $tipo Tipo de componente (base, leche, endulzante, topping)
     * @return array
     */
    public function getComponentesByTipo(string $tipo): array {
        return $this->db->fetchAll(
            "SELECT * FROM custom_coffee_components WHERE tipo = ? AND estado = 'activo' ORDER BY nombre",
            [$tipo]
        );
    }

    /**
     * Obtiene un componente específico por su ID
     * @param int $id ID del componente
     * @return array|null
     */
    public function getComponenteById(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM custom_coffee_components WHERE componente_ID = ? AND estado = 'activo'",
            [$id]
        );
    }

    /**
     * Verifica si hay stock suficiente de un componente
     * @param int $id ID del componente
     * @param int $cantidad Cantidad requerida
     * @return bool
     */
    public function verificarStock(int $id, int $cantidad = 1): bool {
        $componente = $this->db->fetchOne(
            "SELECT stock FROM custom_coffee_components WHERE componente_ID = ? AND estado = 'activo'",
            [$id]
        );
        return $componente && $componente['stock'] >= $cantidad;
    }

    /**
     * Actualiza el stock de un componente
     * @param int $id ID del componente
     * @param int $cantidad Cantidad a restar del stock
     * @return bool
     */
    public function actualizarStock(int $id, int $cantidad): bool {
        return $this->db->update(
            'custom_coffee_components',
            ['stock' => 'stock - ?'],
            'componente_ID = ? AND stock >= ?',
            [$cantidad, $id, $cantidad]
        );
    }

    /**
     * Obtiene todos los componentes disponibles
     * @return array
     */
    public function getAllComponentes(): array {
        return $this->db->fetchAll(
            "SELECT * FROM custom_coffee_components WHERE estado = 'activo' ORDER BY tipo, nombre"
        );
    }

    /**
     * Obtiene los componentes agrupados por tipo
     * @return array
     */
    public function getComponentesAgrupados(): array {
        $componentes = $this->getAllComponentes();
        $agrupados = [
            'base' => [],
            'leche' => [],
            'endulzante' => [],
            'topping' => []
        ];

        foreach ($componentes as $componente) {
            $agrupados[$componente['tipo']][] = $componente;
        }

        return $agrupados;
    }

    /**
     * Verifica si un componente está activo
     * @param int $id ID del componente
     * @return bool
     */
    public function isComponenteActivo(int $id): bool {
        $componente = $this->getComponenteById($id);
        return $componente && $componente['estado'] === 'activo';
    }

    /**
     * Obtiene el precio de un componente
     * @param int $id ID del componente
     * @return float|null
     */
    public function getPrecio(int $id): ?float {
        $componente = $this->getComponenteById($id);
        return $componente ? (float)$componente['precio'] : null;
    }

    /**
     * Verifica si hay stock suficiente para todos los componentes de una receta
     * @param int $recetaId ID de la receta
     * @return bool True si hay stock suficiente para todos los componentes
     */
    public function verificarStockReceta(int $recetaId): bool {
        $componentes = $this->db->fetchAll(
            "SELECT r.componente_ID, r.cantidad, c.stock 
             FROM custom_coffee_recipe_details r
             JOIN custom_coffee_components c ON r.componente_ID = c.componente_ID
             WHERE r.receta_ID = ?",
            [$recetaId]
        );

        foreach ($componentes as $componente) {
            if ($componente['stock'] < $componente['cantidad']) {
                return false;
            }
        }

        return true;
    }
} 