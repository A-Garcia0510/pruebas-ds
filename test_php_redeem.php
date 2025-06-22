<?php
// Simple test script to check the redeem functionality
session_start();

// Simulate a logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['nombre'] = 'Test User';
$_SESSION['correo'] = 'test@example.com';

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Simple test without full container setup
function testRedeem() {
    $api_url = 'http://localhost:8000';
    $url = $api_url . '/api/v1/loyalty/redeem-reward';
    
    $data = [
        'user_id' => 1,
        'reward_id' => 1
    ];
    
    echo "Testing API endpoint: $url\n";
    echo "Sending data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ];
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    echo "HTTP Code: $http_code\n";
    echo "CURL Error: " . ($curl_error ?: 'None') . "\n";
    echo "Raw Response: $response\n\n";
    
    if ($response === false) {
        echo "❌ CURL Error: $curl_error\n";
        return false;
    }
    
    if ($http_code >= 400) {
        echo "❌ HTTP Error: $http_code\n";
        return false;
    }
    
    $decoded = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Decode Error: " . json_last_error_msg() . "\n";
        echo "Response that failed to decode: $response\n";
        return false;
    }
    
    echo "✅ JSON Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    return true;
}

// Run the test
echo "=== PHP Redeem Test ===\n";
$success = testRedeem();
echo $success ? "\n✅ Test completed successfully" : "\n❌ Test failed";
?> 