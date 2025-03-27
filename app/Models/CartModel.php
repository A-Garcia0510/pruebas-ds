<?php
namespace App\Models;

class Cart {
    private array $items = [];
    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function addItem(Product $product, int $quantity): void {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("La cantidad debe ser mayor a cero");
        }

        // Verificar límite de stock
        if ($quantity > $product->getStock()) {
            throw new \RuntimeException("Cantidad solicitada supera el stock disponible");
        }

        // Si el producto ya existe, actualizar cantidad
        foreach ($this->items as &$item) {
            if ($item['product']->getId() === $product->getId()) {
                $newQuantity = $item['quantity'] + $quantity;
                if ($newQuantity > $product->getStock()) {
                    throw new \RuntimeException("Cantidad total solicitada supera el stock disponible");
                }
                $item['quantity'] = $newQuantity;
                return;
            }
        }

        // Si no existe, agregar nuevo item
        $this->items[] = [
            'product' => $product,
            'quantity' => $quantity
        ];
    }

    public function removeItem(Product $product): void {
        $this->items = array_filter($this->items, function($item) use ($product) {
            return $item['product']->getId() !== $product->getId();
        });
    }

    public function calculateTotal(): float {
        return array_reduce($this->items, function($total, $item) {
            return $total + ($item['product']->getPrice() * $item['quantity']);
        }, 0.0);
    }

    public function getItems(): array {
        return $this->items;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function isEmpty(): bool {
        return empty($this->items);
    }
}