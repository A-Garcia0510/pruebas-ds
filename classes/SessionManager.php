<?php
interface SessionManagerInterface {
    public function set(string $key, $value): void;
    public function get(string $key);
    public function destroy(): void;
}

class PHPSessionManager implements SessionManagerInterface {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key) {
        return $_SESSION[$key] ?? null;
    }

    public function destroy(): void {
        $_SESSION = [];
        session_destroy();
    }
}