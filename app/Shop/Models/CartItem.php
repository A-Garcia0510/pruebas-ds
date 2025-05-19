<?php
// src/Shop/Models/CartItem.php
namespace App\Shop\Models;

class CartItem
{
    private $userId;
    private $productId;
    private $quantity;
    private $productName;
    private $productPrice;
    
    public function __construct(
        ?string $userId = null,
        ?int $productId = null,
        ?int $quantity = null,
        ?string $productName = null,
        ?float $productPrice = null
    ) {
        $this->userId = $userId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
    }
    
    public function getUserId(): ?string
    {
        return $this->userId;
    }
    
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getProductId(): ?int
    {
        return $this->productId;
    }
    
    public function setProductId(int $productId): self
    {
        $this->productId = $productId;
        return $this;
    }
    
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
    
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    public function getProductName(): ?string
    {
        return $this->productName;
    }
    
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
        return $this;
    }
    
    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }
    
    public function setProductPrice(float $productPrice): self
    {
        $this->productPrice = $productPrice;
        return $this;
    }
    
    /**
     * Calcula el subtotal del ítem
     * 
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->productPrice * $this->quantity;
    }
}