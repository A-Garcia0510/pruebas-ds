<?php
namespace App\Core;

/**
 * Gestiona las respuestas HTTP
 */
class Response
{
    /**
     * Establece el código de estado HTTP
     * 
     * @param int $code
     */
    public function setStatusCode($code)
    {
        http_response_code($code);
    }
    
    /**
     * Redirige a una URL específica
     * 
     * @param string $url URL a redireccionar
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
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
    public function json($data, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setContentType('application/json');
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
}