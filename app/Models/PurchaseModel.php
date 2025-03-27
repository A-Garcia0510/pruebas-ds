<?php
namespace Models;

class Purchase {
    private int $id;
    private User $user;
    private Cart $cart;
    private \DateTime $purchaseDate;
    private float $total;
    private string $status;

    public function __construct(User $user, Cart $cart) {
        if ($cart->isEmpty()) {
            throw new \RuntimeException("No se puede crear una compra con un carrito vacío");
        }

        $this->user = $user;
        $this->cart = $cart;
        $this->purchaseDate = new \DateTime();
        $this->total = $cart->calculateTotal();
        $this->status = 'PENDING';
    }

    public function complete(): void {
        $this->status = 'COMPLETED';
    }

    public function cancel(): void {
        $this->status = 'CANCELLED';
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getCart(): Cart {
        return $this->cart;
    }

    public function getPurchaseDate(): \DateTime {
        return $this->purchaseDate;
    }

    public function getTotal(): float {
        return $this->total;
    }

    public function getStatus(): string {
        return $this->status;
    }
}
