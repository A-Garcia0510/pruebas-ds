<?php
// src/Shop/Models/Purchase.php
namespace App\Shop\Models;

use DateTime;

class Purchase
{
    private $id;
    private $userId;
    private $total;
    private $date;
    private $status;
    private $details = [];
    
    const STATUS_PENDING = 'pendiente';
    const STATUS_COMPLETED = 'completado';
    const STATUS_CANCELLED = 'cancelado';
    
    public function __construct(
        ?int $id = null,
        ?string $userId = null,
        ?float $total = 0,
        ?DateTime $date = null,
        string $status = self::STATUS_PENDING
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->total = $total;
        $this->date = $date ?: new DateTime();
        $this->status = $status;
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
    
    public function getUserId(): ?string
    {
        return $this->userId;
    }
    
    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getTotal(): float
    {
        return $this->total;
    }
    
    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }
    
    public function getDate(): DateTime
    {
        return $this->date;
    }
    
    public function setDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
    
    public function getDetails(): array
    {
        return $this->details;
    }
    
    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }
    
    public function addDetail(PurchaseDetail $detail): self
    {
        $this->details[] = $detail;
        return $this;
    }
    
    /**
     * Calcula el total de la compra basado en los detalles
     * 
     * @return float
     */
    public function calculateTotal(): float
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->getSubtotal();
        }
        $this->total = $total;
        return $total;
    }
}