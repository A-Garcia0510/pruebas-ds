<?php
// src/Auth/Models/User.php
namespace App\Auth\Models;

class User
{
    private $id;
    private $nombre;
    private $apellidos;
    private $email;
    private $password;
    private $rol;
    
    public function __construct(?int $id = null, ?string $nombre = null, ?string $apellidos = null, ?string $email = null, ?string $password = null, ?string $rol = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
    
    public function getNombre(): ?string
    {
        return $this->nombre;
    }
    
    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }
    
    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }
    
    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;
        return $this;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    public function getRol(): ?string
    {
        return $this->rol;
    }
    
    public function setRol(string $rol): self
    {
        $this->rol = $rol;
        return $this;
    }
    
    /**
     * Valida si la contraseña proporcionada es correcta
     * 
     * @param string $password La contraseña a validar
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        // En una implementación real, aquí deberías usar password_verify()
        // Por ahora, para mantener la compatibilidad con tu código actual:
        return $this->password === $password;
    }
}