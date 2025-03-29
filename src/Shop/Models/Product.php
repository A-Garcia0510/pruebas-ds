<?php
// src/Shop/Models/Product.php
namespace App\Shop\Models;

class Product
{
    private $id;
    private $name;
    private $description;
    private $price;
    private $stock;
    private $category;
    
    public function __construct(
        ?int $id = null, 
        ?string $name = null, 
        ?string $description = null, 
        ?float $price = null, 
        ?int $stock = null, 
        ?string $category = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->category = $category;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
    
    public function getStock(): ?int
    {
        return $this->stock;
    }
    
    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }
    
    public function getCategory(): ?string
    {
        return $this->category;
    }
    
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }
    
    /**
     * Verifica si hay suficiente stock disponible
     * 
     * @param int $quantity
     * @return bool
     */
    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }
    
    /**
     * Reduce el stock del producto
     * 
     * @param int $quantity
     * @return bool
     */
    public function reduceStock(int $quantity): bool
    {
        if (!$this->hasStock($quantity)) {
            return false;
        }
        
        $this->stock -= $quantity;
        return true;
    }
}