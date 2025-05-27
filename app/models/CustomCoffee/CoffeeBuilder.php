<?php
namespace App\Models\CustomCoffee;

use App\Core\Database\DatabaseInterface;
use App\Models\CustomCoffee\Interfaces\CoffeeBuilderInterface;

class CoffeeBuilder implements CoffeeBuilderInterface {
    private CustomCoffee $coffee;
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
        $this->reset();
    }

    /**
     * Reinicia el builder para crear un nuevo café
     */
    public function reset(): self {
        $this->coffee = new CustomCoffee();
        return $this;
    }

    /**
     * Establece la base del café
     * @param int $baseId ID de la base seleccionada
     * @throws \Exception Si la base no existe o no hay stock
     */
    public function setBase(int $baseId): self {
        $base = $this->db->fetchOne(
            "SELECT * FROM custom_coffee_components WHERE id = ? AND tipo = 'base' AND stock > 0",
            [$baseId]
        );

        if (!$base) {
            throw new \Exception("Base no disponible o sin stock");
        }

        $this->coffee->addComponente($baseId, 'base', $base['precio']);
        return $this;
    }

    /**
     * Agrega leche al café
     * @param int $lecheId ID de la leche seleccionada
     * @param int $cantidad Cantidad de leche
     * @throws \Exception Si la leche no existe o no hay stock suficiente
     */
    public function addMilk(int $lecheId, int $cantidad = 1): self {
        $leche = $this->db->fetchOne(
            "SELECT * FROM custom_coffee_components WHERE id = ? AND tipo = 'leche' AND stock >= ?",
            [$lecheId, $cantidad]
        );

        if (!$leche) {
            throw new \Exception("Leche no disponible o stock insuficiente");
        }

        $this->coffee->addComponente($lecheId, 'leche', $leche['precio'], $cantidad);
        return $this;
    }

    /**
     * Agrega endulzante al café
     * @param int $endulzanteId ID del endulzante seleccionado
     * @param int $cantidad Cantidad de endulzante
     * @throws \Exception Si el endulzante no existe o no hay stock suficiente
     */
    public function addSweetener(int $endulzanteId, int $cantidad = 1): self {
        $endulzante = $this->db->fetchOne(
            "SELECT * FROM custom_coffee_components WHERE id = ? AND tipo = 'endulzante' AND stock >= ?",
            [$endulzanteId, $cantidad]
        );

        if (!$endulzante) {
            throw new \Exception("Endulzante no disponible o stock insuficiente");
        }

        $this->coffee->addComponente($endulzanteId, 'endulzante', $endulzante['precio'], $cantidad);
        return $this;
    }

    /**
     * Agrega topping al café
     * @param int $toppingId ID del topping seleccionado
     * @param int $cantidad Cantidad de topping
     * @throws \Exception Si el topping no existe o no hay stock suficiente
     */
    public function addTopping(int $toppingId, int $cantidad = 1): self {
        $topping = $this->db->fetchOne(
            "SELECT * FROM custom_coffee_components WHERE id = ? AND tipo = 'topping' AND stock >= ?",
            [$toppingId, $cantidad]
        );

        if (!$topping) {
            throw new \Exception("Topping no disponible o stock insuficiente");
        }

        $this->coffee->addComponente($toppingId, 'topping', $topping['precio'], $cantidad);
        return $this;
    }

    /**
     * Construye y retorna el café personalizado
     * @return CustomCoffee
     * @throws \Exception Si no se ha establecido una base
     */
    public function build(): CustomCoffee {
        if (!$this->coffee->getBaseId()) {
            throw new \Exception("Debe seleccionar una base para el café");
        }
        return $this->coffee;
    }
} 