<?php
namespace App\Interfaces;

interface PasswordHasherInterface {
    /**
     * Hashea una contraseña
     * @param string $password Contraseña en texto plano
     * @return string Contraseña hasheada
     */
    public function hash(string $password): string;

    /**
     * Verifica una contraseña contra su hash
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash de contraseña almacenado
     * @return bool Resultado de la verificación
     */
    public function verify(string $password, string $hash): bool;
}