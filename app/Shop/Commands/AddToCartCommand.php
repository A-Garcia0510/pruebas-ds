<?php
namespace App\Shop\Commands;

use App\Shop\Services\CartService;

class AddToCartCommand implements CommandInterface, \Serializable {
    private $cartService;
    private $userEmail;
    private $productId;
    private $quantity;
    private $actualizar;
    private $success = false;

    public function __construct(CartService $cartService, string $userEmail, int $productId, int $quantity, bool $actualizar = false) {
        $this->cartService = $cartService;
        $this->userEmail = $userEmail;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->actualizar = $actualizar;
    }

    public function execute() {
        try {
            $this->success = $this->cartService->addItem(
                $this->userEmail,
                $this->productId,
                $this->quantity,
                $this->actualizar
            );
            return $this->success;
        } catch (\Exception $e) {
            error_log("AddToCartCommand::execute() - Error: " . $e->getMessage());
            return false;
        }
    }

    public function undo() {
        if ($this->success) {
            try {
                return $this->cartService->removeItem(
                    $this->userEmail,
                    $this->productId
                );
            } catch (\Exception $e) {
                error_log("AddToCartCommand::undo() - Error: " . $e->getMessage());
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
            'actualizar' => $this->actualizar,
            'success' => $this->success
        ]);
    }

    public function unserialize($data) {
        $data = unserialize($data);
        $this->userEmail = $data['userEmail'];
        $this->productId = $data['productId'];
        $this->quantity = $data['quantity'];
        $this->actualizar = $data['actualizar'];
        $this->success = $data['success'];
    }

    public function setCartService(CartService $cartService) {
        $this->cartService = $cartService;
    }
} 