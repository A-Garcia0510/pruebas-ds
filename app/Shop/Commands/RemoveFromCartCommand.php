<?php
namespace App\Shop\Commands;

use App\Shop\Services\CartService;

class RemoveFromCartCommand implements CommandInterface, \Serializable {
    private $cartService;
    private $userEmail;
    private $productId;
    private $quantity;
    private $success = false;

    public function __construct(CartService $cartService, string $userEmail, int $productId, int $quantity) {
        $this->cartService = $cartService;
        $this->userEmail = $userEmail;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function execute() {
        try {
            $this->success = $this->cartService->removeItem(
                $this->userEmail,
                $this->productId
            );
            return $this->success;
        } catch (\Exception $e) {
            error_log("RemoveFromCartCommand::execute() - Error: " . $e->getMessage());
            return false;
        }
    }

    public function undo() {
        if ($this->success) {
            try {
                return $this->cartService->addItem(
                    $this->userEmail,
                    $this->productId,
                    $this->quantity
                );
            } catch (\Exception $e) {
                error_log("RemoveFromCartCommand::undo() - Error: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    public function serialize() {
        return serialize([
            'userEmail' => $this->userEmail,
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'success' => $this->success
        ]);
    }

    public function unserialize($data) {
        $data = unserialize($data);
        $this->userEmail = $data['userEmail'];
        $this->productId = $data['productId'];
        $this->quantity = $data['quantity'];
        $this->success = $data['success'];
    }

    public function setCartService(CartService $cartService) {
        $this->cartService = $cartService;
    }
} 