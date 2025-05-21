<?php
namespace App\Shop\Commands;

use App\Shop\Services\CartService;

interface CommandInterface {
    public function execute();
    public function undo();
    public function setCartService(CartService $cartService);
} 