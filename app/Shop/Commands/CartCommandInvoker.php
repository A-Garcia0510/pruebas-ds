<?php
namespace App\Shop\Commands;

use App\Shop\Services\CartService;

class CartCommandInvoker {
    private $commands = [];
    private $undoneCommands = [];
    private $sessionKey = 'cart_command_history';
    private $cartService;
    private $undoStack;
    private $redoStack;

    /**
     * Constructor del invocador de comandos
     * 
     * @param CartService $cartService Servicio del carrito
     */
    public function __construct(CartService $cartService) {
        $this->cartService = $cartService;
        $this->undoStack = new \SplStack();
        $this->redoStack = new \SplStack();
        // Cargar el historial desde la sesión
        if (isset($_SESSION[$this->sessionKey])) {
            $history = $_SESSION[$this->sessionKey];
            $this->commands = $this->restoreCommands($history['commands'] ?? []);
            $this->undoneCommands = $this->restoreCommands($history['undoneCommands'] ?? []);
        }
    }

    private function restoreCommands($serializedCommands) {
        $restoredCommands = [];
        foreach ($serializedCommands as $command) {
            $restoredCommand = unserialize($command);
            if ($restoredCommand instanceof CommandInterface) {
                $restoredCommand->setCartService($this->cartService);
                $restoredCommands[] = $restoredCommand;
            }
        }
        return $restoredCommands;
    }

    private function saveToSession() {
        $_SESSION[$this->sessionKey] = [
            'commands' => array_map('serialize', $this->commands),
            'undoneCommands' => array_map('serialize', $this->undoneCommands)
        ];
    }

    public function executeCommand(CommandInterface $command) {
        if ($command->execute()) {
            $this->commands[] = $command;
            // Limpiar los comandos deshechos ya que estamos creando una nueva rama
            $this->undoneCommands = [];
            $this->saveToSession();
            return true;
        }
        return false;
    }

    public function undoLastCommand() {
        if (!empty($this->commands)) {
            $command = array_pop($this->commands);
            if ($command->undo()) {
                $this->undoneCommands[] = $command;
                $this->saveToSession();
                return true;
            }
        }
        return false;
    }

    public function redoLastCommand() {
        if (!empty($this->undoneCommands)) {
            $command = array_pop($this->undoneCommands);
            if ($command->execute()) {
                $this->commands[] = $command;
                $this->saveToSession();
                return true;
            }
        }
        return false;
    }

    public function getCommandHistory() {
        return [
            'executed' => $this->commands,
            'undone' => $this->undoneCommands
        ];
    }

    /**
     * Verifica si hay acciones disponibles para deshacer
     * 
     * @return bool
     */
    public function hasUndoActions() {
        return !empty($this->commands);
    }

    /**
     * Verifica si hay acciones disponibles para rehacer
     * 
     * @return bool
     */
    public function hasRedoActions() {
        return !empty($this->undoneCommands);
    }

    /**
     * Limpia el historial de comandos
     */
    public function clearHistory() {
        $this->commands = [];
        $this->undoneCommands = [];
        $this->saveToSession();
    }
} 