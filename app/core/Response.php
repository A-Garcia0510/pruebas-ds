<?php
namespace App\Core;

use App\Core\Interfaces\ResponseInterface;

/**
 * Gestiona las respuestas HTTP
 */
class Response implements ResponseInterface
{
    private array $headers = [];
    private int $statusCode = 200;
    
    /**
     * Establece el código de estado HTTP
     * 
     * @param int $code
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }
    
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        header("$name: $value");
        return $this;
    }
    
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }
    
    /**
     * Redirige a una URL específica
     * 
     * @param string $url URL a redireccionar
     */
    public function redirect(string $url): void
    {
        $this->setHeader('Location', $url);
        exit;
    }
    
    /**
     * Establece el tipo de contenido de la respuesta
     * 
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        header('Content-Type: ' . $contentType);
    }
    
    /**
     * Envía una respuesta JSON
     * 
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código de estado HTTP
     */
    public function json($data): void
    {
        $this->setHeader('Content-Type', 'application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Envía una respuesta de texto plano
     * 
     * @param string $text Texto a enviar
     * @param int $statusCode Código de estado HTTP
     */
    public function text($text, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setContentType('text/plain');
        echo $text;
        exit;
    }
    
    public function html(string $html): void
    {
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        echo $html;
        exit;
    }
    
    public function error(int $code, string $message): void
    {
        $this->setStatusCode($code);
        $this->json([
            'success' => false,
            'message' => $message,
            'code' => $code
        ]);
    }
}