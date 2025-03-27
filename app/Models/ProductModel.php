<?php
namespace Models;

class Product {
    private int $id;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private string $category;

    public function __construct(
        string $name, 
        string $description, 
        float $price, 
        int $stock, 
        string $category
    ) {
        $this->validatePrice($price);
        $this->validateStock($stock);

        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->category = $category;
    }

    private function validatePrice(float $price): void {
        if ($price <= 0) {
            throw new \InvalidArgumentException("El precio debe ser mayor a cero");
        }
    }

    private function validateStock(int $stock): void {
        if ($stock < 0) {
            throw new \InvalidArgumentException("El stock no puede ser negativo");
        }
    }

    public function reduceStock(int $quantity): void {
        if ($quantity > $this->stock) {
            throw new \RuntimeException("Stock insuficiente");
        }
        $this->stock -= $quantity;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getCategory(): string {
        return $this->category;
    }
}