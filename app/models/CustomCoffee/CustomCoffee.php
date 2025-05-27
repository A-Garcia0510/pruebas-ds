<?php
namespace App\Models\CustomCoffee;

class CustomCoffee {
    private array $componentes = [];
    private float $precioTotal = 0.0;
    private int $baseId;
    private array $leches = [];
    private array $endulzantes = [];
    private array $toppings = [];

    /**
     * Agrega un componente al café
     * @param int $componenteId ID del componente
     * @param string $tipo Tipo de componente (base, leche, endulzante, topping)
     * @param float $precio Precio del componente
     * @param int $cantidad Cantidad del componente
     */
    public function addComponente(int $componenteId, string $tipo, float $precio, int $cantidad = 1): void {
        $this->componentes[] = [
            'id' => $componenteId,
            'tipo' => $tipo,
            'precio' => $precio,
            'cantidad' => $cantidad
        ];

        $this->precioTotal += ($precio * $cantidad);

        switch($tipo) {
            case 'base':
                $this->baseId = $componenteId;
                break;
            case 'leche':
                $this->leches[] = ['id' => $componenteId, 'cantidad' => $cantidad];
                break;
            case 'endulzante':
                $this->endulzantes[] = ['id' => $componenteId, 'cantidad' => $cantidad];
                break;
            case 'topping':
                $this->toppings[] = ['id' => $componenteId, 'cantidad' => $cantidad];
                break;
        }
    }

    /**
     * Obtiene todos los componentes del café
     * @return array
     */
    public function getComponentes(): array {
        return $this->componentes;
    }

    /**
     * Obtiene el precio total del café
     * @return float
     */
    public function getPrecioTotal(): float {
        return $this->precioTotal;
    }

    /**
     * Obtiene el ID de la base del café
     * @return int
     */
    public function getBaseId(): int {
        return $this->baseId;
    }

    /**
     * Obtiene las leches agregadas
     * @return array
     */
    public function getLeches(): array {
        return $this->leches;
    }

    /**
     * Obtiene los endulzantes agregados
     * @return array
     */
    public function getEndulzantes(): array {
        return $this->endulzantes;
    }

    /**
     * Obtiene los toppings agregados
     * @return array
     */
    public function getToppings(): array {
        return $this->toppings;
    }

    /**
     * Verifica si el café tiene un componente específico
     * @param int $componenteId ID del componente a verificar
     * @return bool
     */
    public function tieneComponente(int $componenteId): bool {
        foreach ($this->componentes as $componente) {
            if ($componente['id'] === $componenteId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene la cantidad de un componente específico
     * @param int $componenteId ID del componente
     * @return int
     */
    public function getCantidadComponente(int $componenteId): int {
        foreach ($this->componentes as $componente) {
            if ($componente['id'] === $componenteId) {
                return $componente['cantidad'];
            }
        }
        return 0;
    }
} 