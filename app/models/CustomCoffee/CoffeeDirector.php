<?php
namespace App\Models\CustomCoffee;

use App\Models\CustomCoffee\Interfaces\CoffeeBuilderInterface;

class CoffeeDirector {
    private CoffeeBuilderInterface $builder;

    public function __construct(CoffeeBuilderInterface $builder) {
        $this->builder = $builder;
    }

    /**
     * Crea un café americano básico
     * @return CustomCoffee
     */
    public function makeAmericano(): CustomCoffee {
        $this->builder->reset();
        $this->builder->setBase(1); // ID de café americano
        return $this->builder->build();
    }

    /**
     * Crea un café con leche
     * @return CustomCoffee
     */
    public function makeCafeConLeche(): CustomCoffee {
        $this->builder->reset();
        $this->builder->setBase(2); // ID de café expreso
        $this->builder->addMilk(1); // ID de leche entera
        return $this->builder->build();
    }

    /**
     * Crea un café moka
     * @return CustomCoffee
     */
    public function makeMoka(): CustomCoffee {
        $this->builder->reset();
        $this->builder->setBase(2); // ID de café expreso
        $this->builder->addMilk(1); // ID de leche entera
        $this->builder->addSweetener(2); // ID de chocolate
        $this->builder->addTopping(1); // ID de crema batida
        return $this->builder->build();
    }

    /**
     * Crea un café caramelo macchiato
     * @return CustomCoffee
     */
    public function makeCaramelMacchiato(): CustomCoffee {
        $this->builder->reset();
        $this->builder->setBase(2); // ID de café expreso
        $this->builder->addMilk(2); // ID de leche descremada
        $this->builder->addSweetener(1); // ID de caramelo
        $this->builder->addTopping(2); // ID de espuma de leche
        return $this->builder->build();
    }

    /**
     * Crea un café personalizado
     * @param array $componentes Array con los componentes a agregar
     * @return CustomCoffee
     * @throws \Exception Si hay error en la construcción
     */
    public function makeCustom(array $componentes): CustomCoffee {
        $this->builder->reset();
        
        foreach ($componentes as $componente) {
            switch ($componente['tipo']) {
                case 'base':
                    $this->builder->setBase($componente['id']);
                    break;
                case 'leche':
                    $this->builder->addMilk($componente['id'], $componente['cantidad'] ?? 1);
                    break;
                case 'endulzante':
                    $this->builder->addSweetener($componente['id'], $componente['cantidad'] ?? 1);
                    break;
                case 'topping':
                    $this->builder->addTopping($componente['id'], $componente['cantidad'] ?? 1);
                    break;
                default:
                    throw new \Exception("Tipo de componente no válido: {$componente['tipo']}");
            }
        }

        return $this->builder->build();
    }
} 