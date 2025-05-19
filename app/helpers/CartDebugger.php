<?php
// app/helpers/CartDebugger.php

class CartDebugger {
    /**
     * Logs debug information to a file
     * 
     * @param string $message Message to log
     * @param mixed $data Optional data to include in the log
     * @return void
     */
    public static function log($message, $data = null) {
        $logDir = BASE_PATH . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/cart_debug.log';
        
        // Format the log message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] {$message}";
        
        // Include data if provided
        if ($data !== null) {
            $formattedData = is_array($data) || is_object($data) ? print_r($data, true) : $data;
            $formattedMessage .= "\nData: {$formattedData}";
        }
        
        $formattedMessage .= "\n" . str_repeat('-', 80) . "\n";
        
        // Write to log file
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }
}