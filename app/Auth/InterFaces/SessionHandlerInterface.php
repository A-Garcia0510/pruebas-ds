<?php
// src/Auth/Interfaces/SessionHandlerInterface.php
namespace App\Auth\Interfaces;

interface SessionHandlerInterface
{
    /**
     * Inicia una sesión
     */
    public function startSession(): void;
    
    /**
     * Almacena un valor en la sesión
     * 
     * @param string $key La clave
     * @param mixed $value El valor
     */
    public function set(string $key, $value): void;
    
    /**
     * Obtiene un valor de la sesión
     * 
     * @param string $key La clave
     * @param mixed $default El valor por defecto si la clave no existe
     * @return mixed
     */
    public function get(string $key, $default = null);
    
    /**
     * Verifica si existe una clave en la sesión
     * 
     * @param string $key La clave
     * @return bool
     */
    public function has(string $key): bool;
    
    /**
     * Elimina una clave de la sesión
     * 
     * @param string $key La clave
     */
    public function remove(string $key): void;
    
    /**
     * Destruye la sesión
     */
    public function destroy(): void;
}