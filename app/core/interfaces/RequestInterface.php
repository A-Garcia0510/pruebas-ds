<?php
namespace App\Core\Interfaces;

interface RequestInterface
{
    /**
     * Obtiene el método HTTP de la petición
     * 
     * @return string
     */
    public function getMethod(): string;
    
    /**
     * Obtiene la ruta de la petición
     * 
     * @return string
     */
    public function getPath(): string;
    
    /**
     * Obtiene el cuerpo de la petición
     * 
     * @return array
     */
    public function getBody(): array;
    
    /**
     * Obtiene los parámetros de la URL
     * 
     * @return array
     */
    public function getQueryParams(): array;
    
    /**
     * Obtiene los headers de la petición
     * 
     * @return array
     */
    public function getHeaders(): array;
    
    /**
     * Obtiene un header específico
     * 
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string;
    
    /**
     * Verifica si la petición es AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool;
} 