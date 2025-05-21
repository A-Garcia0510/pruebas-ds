<?php
namespace App\Core\Interfaces;

interface ResponseInterface
{
    /**
     * Establece el código de estado HTTP
     * 
     * @param int $code
     * @return self
     */
    public function setStatusCode(int $code): self;
    
    /**
     * Establece un header HTTP
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self;
    
    /**
     * Establece múltiples headers HTTP
     * 
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self;
    
    /**
     * Envía una respuesta JSON
     * 
     * @param mixed $data
     * @return void
     */
    public function json($data): void;
    
    /**
     * Envía una respuesta HTML
     * 
     * @param string $html
     * @return void
     */
    public function html(string $html): void;
    
    /**
     * Redirige a una URL
     * 
     * @param string $url
     * @return void
     */
    public function redirect(string $url): void;
    
    /**
     * Envía una respuesta de error
     * 
     * @param int $code
     * @param string $message
     * @return void
     */
    public function error(int $code, string $message): void;
} 