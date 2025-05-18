<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Clase base para todos los middleware
 */
abstract class Middleware
{
    /**
     * Método que debe ser implementado por todas las clases hijas
     * 
     * @param Request $request Objeto de solicitud
     * @param Response $response Objeto de respuesta
     * @param callable $next Siguiente middleware o controlador
     * @return mixed
     */
    abstract public function execute(Request $request, Response $response, callable $next);
}