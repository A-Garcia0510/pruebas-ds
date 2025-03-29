<?php
// src/Shop/Services/PurchaseService.php
namespace App\Shop\Services;

use App\Core\Database\DatabaseInterface;
use App\Shop\Interfaces\PurchaseServiceInterface;
use App\Shop\Interfaces\CartInterface;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Models\Purchase;
use App\Shop\Models\PurchaseDetail;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Exceptions\CheckoutException;
use DateTime;

class PurchaseService implements PurchaseServiceInterface
{
    private $db;
    private $cartService;
    private $productRepository;
    private $purch