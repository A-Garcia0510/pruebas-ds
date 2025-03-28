<?php
namespace App\Services;

use App\Interfaces\PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface {
    /**
     * Hash de contraseña utilizando bcrypt
     * @param string $password Contraseña en texto plano
     * @return string Hash de contraseña
     */
    public function hash(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verifica una contraseña contra su hash
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash de contraseña almacenado
     * @return bool Resultado de la verificación
     */
    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}