<?php
namespace App\Models\CustomCoffee\Interfaces;

use App\Models\CustomCoffee\CustomCoffee;

interface CoffeeBuilderInterface {
    /**
     * Establece la base del café (espresso, americano, etc.)
     * @param int $componenteId ID del componente base
     * @return self
     */
    public function setBase(int $componenteId): self;

    /**
     * Agrega leche al café
     * @param int $componenteId ID del componente de leche
     * @param int $cantidad Cantidad de leche
     * @return self
     */
    public function addMilk(int $componenteId, int $cantidad = 1): self;

    /**
     * Agrega endulzante al café
     * @param int $componenteId ID del componente endulzante
     * @param int $cantidad Cantidad de endulzante
     * @return self
     */
    public function addSweetener(int $componenteId, int $cantidad = 1): self;

    /**
     * Agrega topping al café
     * @param int $componenteId ID del componente topping
     * @param int $cantidad Cantidad de topping
     * @return self
     */
    public function addTopping(int $componenteId, int $cantidad = 1): self;

    /**
     * Obtiene el café construido
     * @return CustomCoffee
     */
    public function build(): CustomCoffee;

    /**
     * Reinicia el builder para una nueva construcción
     * @return self
     */
    public function reset(): self;
} 