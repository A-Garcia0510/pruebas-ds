<?php
// src/Shop/Models/PurchaseDetail.php
namespace App\Shop\Models;

class PurchaseDetail
{
    private $id;
    private $purchaseId;
    private $productId;
    private $productName;
    private $quantity;
    private $price;
    
    public function __construct(
        ?int $id = null,
        ?int $purchaseId = null,
        ?int $productId = null,
        ?string $productName = null,
        ?int $quantity = null,
        ?float $price = null
    ) {
        $this->id = $id;
        $this->purchaseId = $purchaseId;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->price = $price;
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
    
    public function getPurchaseId(): ?int
    {
        return $this->purchaseId;
    }
    
    public function setPurchaseId(int $purchaseId): self
    {
        $this->purchaseId = $purchaseId;
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
    
    public function getProductName(): ?string
    {
        return $this->productName;
    }
    
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;
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
    
    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
    
    /**
     * Calcula el subtotal del detalle
     * 
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->price * $this->quantity;
    }
}