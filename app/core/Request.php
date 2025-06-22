<?php
namespace App\Core;

use App\Core\Interfaces\RequestInterface;

/**
 * Gestiona los datos de la solicitud HTTP
 */
class Request implements RequestInterface
{
    private array $body = [];
    private array $queryParams = [];
    private array $headers = [];
    
    public function __construct()
    {
        $this->body = $this->getRequestBody();
        $this->queryParams = $_GET;
        
        // Manejar headers de forma segura
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        } else {
            // Fallback para CLI o cuando getallheaders no está disponible
            $this->headers = [];
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->headers['Host'] = $_SERVER['HTTP_HOST'];
            }
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
            }
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->headers['Accept'] = $_SERVER['HTTP_ACCEPT'];
            }
            if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
                $this->headers['Content-Type'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->headers['X-Requested-With'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
            }
        }
    }
    
    /**
     * Obtiene la ruta actual de la URL
     * 
     * @return string
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Si estamos en un subdirectorio, necesitamos ajustar la ruta
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // Si no estamos en el directorio raíz y el path comienza con ese directorio
        if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }
        
        // Eliminar parámetros de consulta si existen
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        // Asegurarse de que el path comience con /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Depuración
        error_log("RequestURI: " . $_SERVER['REQUEST_URI']);
        error_log("ScriptName: " . $scriptName);
        error_log("ScriptDir: " . $scriptDir);
        error_log("Path procesado: " . $path);
        
        return $path;
    }
    
    /**
     * Obtiene el método HTTP actual
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }
    
    /**
     * Determina si la petición es GET
     * 
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }
    
    /**
     * Determina si la petición es POST
     * 
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Obtiene los datos del cuerpo (para peticiones POST)
     * 
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }
    
    /**
     * Obtiene los parámetros de consulta
     * 
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
    
    /**
     * Obtiene los encabezados de la solicitud
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Obtiene un valor específico de la solicitud
     * 
     * @param string $key Nombre del parámetro
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Primero buscar en los parámetros GET
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        
        // Luego buscar en el cuerpo de la solicitud
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }
    
    /**
     * Obtiene un encabezado específico de la solicitud
     * 
     * @param string $name Nombre del encabezado
     * @return ?string
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
    
    /**
     * Determina si la solicitud es una solicitud AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($this->headers['X-Requested-With']) && 
               strtolower($this->headers['X-Requested-With']) === 'xmlhttprequest';
    }
    
    private function getRequestBody(): array
    {
        $body = [];
        
        if ($this->getMethod() === 'GET') {
            return $body;
        }
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        error_log("[Request] Content-Type: " . $contentType);
        
        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            error_log("[Request] Raw input: " . $rawInput);
            
            $body = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("[Request] JSON decode error: " . json_last_error_msg());
                error_log("[Request] Raw input was: " . $rawInput);
                return [];
            }
            error_log("[Request] Decoded JSON body: " . print_r($body, true));
        } else {
            $body = $_POST;
            error_log("[Request] POST body: " . print_r($body, true));
        }
        
        return $body;
    }
}