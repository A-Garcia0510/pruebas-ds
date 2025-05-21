<?php
namespace App\Core;

/**
 * Contenedor de Inyección de Dependencias
 * 
 * Esta clase maneja el registro y resolución de dependencias de la aplicación
 */
class Container
{
    /**
     * @var array Almacena las definiciones de servicios
     */
    public $bindings = [];

    /**
     * @var array Almacena las instancias singleton
     */
    private $instances = [];

    /**
     * Registra un servicio en el contenedor
     * 
     * @param string $abstract Nombre de la clase o interfaz
     * @param string|callable|null $concrete Implementación concreta o closure
     * @param bool $shared Si es true, se mantiene una única instancia
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared
        ];
    }

    /**
     * Registra un servicio singleton
     * 
     * @param string $abstract Nombre de la clase o interfaz
     * @param string|callable|null $concrete Implementación concreta o closure
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resuelve una dependencia del contenedor
     * 
     * @param string $abstract Nombre de la clase o interfaz a resolver
     * @return mixed Instancia resuelta
     */
    public function resolve($abstract)
    {
        // Si es un valor directo, lo retornamos
        if (isset($this->bindings[$abstract]) && !is_array($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }

        // Si es un singleton y ya existe una instancia, la retornamos
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Si no está registrado, asumimos que es el nombre de la clase
        if (!isset($this->bindings[$abstract])) {
            $this->bind($abstract);
        }

        $binding = $this->bindings[$abstract];
        
        // Si es un valor directo, lo retornamos
        if (!is_array($binding)) {
            return $binding;
        }

        // Verificar que el binding tenga la estructura correcta
        if (!isset($binding['concrete']) || !isset($binding['shared'])) {
            throw new \Exception("Invalid binding structure for {$abstract}");
        }

        $concrete = $binding['concrete'];
        $shared = $binding['shared'];

        // Si es un closure, lo ejecutamos
        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } else {
            // Si es una clase, la instanciamos
            $instance = $this->build($concrete);
        }

        // Si es un singleton, guardamos la instancia
        if ($shared) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Construye una instancia de una clase
     * 
     * @param string $concrete Nombre de la clase
     * @return object Instancia de la clase
     */
    private function build($concrete)
    {
        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("La clase {$concrete} no puede ser instanciada");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->getClass()) {
                $dependencies[] = $this->resolve($parameter->getClass()->getName());
            } else {
                if (!$parameter->isOptional()) {
                    throw new \Exception("No se puede resolver la dependencia {$parameter->getName()}");
                }
                $dependencies[] = $parameter->getDefaultValue();
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Verifica si un servicio está registrado
     * 
     * @param string $abstract Nombre de la clase o interfaz
     * @return bool
     */
    public function has($abstract)
    {
        return isset($this->bindings[$abstract]);
    }

    /**
     * Limpia todas las instancias singleton
     */
    public function clear()
    {
        $this->instances = [];
    }

    /**
     * Registra un valor directamente en el container
     * 
     * @param string $id Identificador del valor
     * @param mixed $value Valor a almacenar
     */
    public function registerValue(string $id, $value): void
    {
        $this->bindings[$id] = $value;
    }

    /**
     * Obtiene un valor directamente del container
     * 
     * @param string $id Identificador del valor
     * @return mixed Valor almacenado
     */
    public function get(string $id)
    {
        // Si es un valor directo, lo retornamos
        if (isset($this->bindings[$id]) && !is_array($this->bindings[$id])) {
            return $this->bindings[$id];
        }
        
        // Si no existe, intentamos resolverlo
        if (!isset($this->bindings[$id])) {
            throw new \Exception("No binding found for {$id}");
        }
        
        return $this->resolve($id);
    }
} 