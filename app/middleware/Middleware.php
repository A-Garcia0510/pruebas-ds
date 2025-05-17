<?php
namespace App\Middleware;

use App\Auth\AuthFactory;
use App\Core\Request;
use App\Core\Response;

/**
 * Middleware base para la aplicación
 */
abstract class Middleware
{
    /**
     * Ejecuta el middleware
     * 
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    abstract public function handle(Request $request, Response $response, callable $next);
}