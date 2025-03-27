<?php
namespace Interfaces;

interface PurchaseInterface {
    public function createPurchase(string $correo): array;
    public function getPurchaseHistory(string $correo): array;
    public function getPurchaseDetails(int $compraID): array;
}