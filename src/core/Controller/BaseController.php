<?php
/**
 * BaseController - Controlador base del que heredan todos los controladores
 * Proporciona funcionalidad común como la carga de vistas
 */
class BaseController {
    /**
     * Instancia del Router
     */
    protected $router;
    
    /**
     * Establece la instancia del router
     * 
     * @param Router $router Instancia del router
     */
    public function setRouter($router) {
        $this->router = $router;
    }
    
    /**
     * Obtiene la URL base del proyecto
     * 
     * @return string URL base del proyecto
     */
    protected function getBaseUrl() {
        return $this->router ? $this->router->getBaseUrl() : '';
    }
    
    /**
     * Carga una vista y la renderiza con los datos proporcionados
     * 
     * @param string $view Nombre de la vista a cargar
     * @param array $data Datos a pasar a la vista
     * @return void
     */
    protected function view($view, $data = []) {
        // Añadir la URL base a los datos para que esté disponible en todas las vistas
        $data['baseUrl'] = $this->getBaseUrl();
        
        // Extraer los datos para que estén disponibles en la vista como variables
        if(!empty($data)) {
            extract($data);
        }
        
        // Verificar las posibles ubicaciones de las vistas
        $possibleViewPaths = [
            __DIR__ . '/../View/' . $view . '.php',
            dirname(__DIR__) . '/View/' . $view . '.php',
            dirname(dirname(__DIR__)) . '/core/View/' . $view . '.php',
            $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/View/' . $view . '.php'
        ];
        
        $viewFound = false;
        $viewFile = null;
        
        foreach ($possibleViewPaths as $path) {
            if(file_exists($path)) {
                $viewFile = $path;
                $viewFound = true;
                break;
            }
        }
        
        if($viewFound) {
            // Buscar las ubicaciones de las plantillas
            $headerPaths = [
                __DIR__ . '/../View/templates/header.php',
                dirname(__DIR__) . '/View/templates/header.php',
                dirname(dirname(__DIR__)) . '/core/View/templates/header.php',
                $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/View/templates/header.php'
            ];
            
            $footerPaths = [
                __DIR__ . '/../View/templates/footer.php',
                dirname(__DIR__) . '/View/templates/footer.php',
                dirname(dirname(__DIR__)) . '/core/View/templates/footer.php',
                $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/View/templates/footer.php'
            ];
            
            // Buscar el header
            $headerFound = false;
            foreach ($headerPaths as $headerPath) {
                if(file_exists($headerPath)) {
                    require_once $headerPath;
                    $headerFound = true;
                    break;
                }
            }
            
            if(!$headerFound) {
                echo "<div style='background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 5px;'>";
                echo "Advertencia: No se encontró la plantilla de encabezado.";
                echo "</div>";
            }
            
            // Cargar la vista principal
            require_once $viewFile;
            
            // Buscar el footer
            $footerFound = false;
            foreach ($footerPaths as $footerPath) {
                if(file_exists($footerPath)) {
                    require_once $footerPath;
                    $footerFound = true;
                    break;
                }
            }
            
            if(!$footerFound) {
                echo "<div style='background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 5px;'>";
                echo "Advertencia: No se encontró la plantilla de pie de página.";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 5px;'>";
            echo "Error: Vista no encontrada - " . $view;
            echo "<hr>";
            echo "Ubicaciones buscadas:";
            echo "<ul>";
            foreach ($possibleViewPaths as $path) {
                echo "<li>" . $path . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
    /**
     * Redirecciona a una URL específica
     * 
     * @param string $url URL a la que redirigir
     * @return void
     */
    protected function redirect($url) {
        // Añadir la URL base si la URL no comienza con http o https
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = $this->getBaseUrl() . '/' . ltrim($url, '/');
        }
        
        header("Location: " . $url);
        exit;
    }
    
    /**
     * Verifica si el usuario está autenticado
     * 
     * @return bool True si el usuario está autenticado, false en caso contrario
     */
    protected function isLoggedIn() {
        return isset($_SESSION['correo']);
    }
    
    /**
     * Obtiene información del usuario autenticado
     * 
     * @return array|null Información del usuario o null si no está autenticado
     */
    protected function getLoggedInUser() {
        if($this->isLoggedIn()) {
            return [
                'correo' => $_SESSION['correo'],
                // Aquí puedes agregar más información del usuario si está disponible en la sesión
            ];
        }
        return null;
    }
}