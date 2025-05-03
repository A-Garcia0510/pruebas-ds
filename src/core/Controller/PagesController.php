<?php
/**
 * PagesController - Controlador para páginas estáticas del sitio
 * Maneja las páginas como Servicios, Ayuda, etc.
 */
require_once __DIR__ . '/BaseController.php';

class PagesController extends BaseController {
    /**
     * Constructor de la clase PagesController
     */
    public function __construct() {
        // Inicializar el controlador
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Método para mostrar la página de servicios
     */
    public function servicios() {
        // Preparar los datos para la vista
        $data = [
            'title' => 'Ethos Coffe - Servicios',
            'isLoggedIn' => $this->isLoggedIn(),
            'user' => $this->getLoggedInUser()
        ];
        
        // Cargar la vista de servicios
        $this->view('pages/servicios', $data);
    }
    
    /**
     * Método para mostrar la página de ayuda
     */
    public function ayuda() {
        // Preparar los datos para la vista
        $data = [
            'title' => 'Ethos Coffe - Ayuda',
            'isLoggedIn' => $this->isLoggedIn(),
            'user' => $this->getLoggedInUser()
        ];
        
        // Cargar la vista de ayuda
        $this->view('pages/ayuda', $data);
    }
}