<?php

/**
 * SMOKE TEST SCRIPT
 * This script will ping all the main GET endpoints of the API
 * to ensure that none of them return 500 Server Errors.
 */

$baseUrl = 'https://coffee-shop-system.online';
// Replace this token with your actual Bearer token from Swagger
$token = 'YOUR_BEARER_TOKEN_HERE';

$endpoints = [
    '/api/v1/auth/me',
    
    // Admin Endpoints
    '/api/v1/admin/branches',
    '/api/v1/admin/categories',
    '/api/v1/admin/customers',
    '/api/v1/admin/modifiers',
    '/api/v1/admin/products',
    '/api/v1/admin/roles',
    '/api/v1/admin/tables',
    '/api/v1/admin/users',
    
    // POS/Frontend Endpoints
    '/api/v1/categories',
    '/api/v1/customers',
    '/api/v1/orders',
    '/api/v1/products',
    '/api/v1/shifts/current',
    '/api/v1/tables',
    '/api/v1/tables/categories',
];

echo "=================================================\n";
echo "Starting API Smoke Test...\n";
echo "=================================================\n\n";

$failedEndpoints = [];

foreach ($endpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    echo "Testing GET $endpoint ... ";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "[ERROR] cURL error: " . curl_error($ch) . "\n";
        $failedEndpoints[$endpoint] = "cURL Error";
    } else {
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "[\033[32mOK\033[0m] (HTTP $httpCode)\n";
        } elseif ($httpCode == 401) {
            echo "[\033[33mUNAUTHORIZED\033[0m] (HTTP $httpCode) - Please check your token!\n";
        } else {
            echo "[\033[31mFAILED\033[0m] (HTTP $httpCode)\n";
            $failedEndpoints[$endpoint] = $response;
        }
    }
    
    curl_close($ch);
}

echo "\n=================================================\n";
if (empty($failedEndpoints)) {
    echo "🎉 All GET endpoints responded successfully!\n";
} else {
    echo "⚠️  Some endpoints failed. See details below:\n\n";
    foreach ($failedEndpoints as $failedEndpoint => $errorResponse) {
        echo "Endpoint: $failedEndpoint\n";
        
        $decoded = json_decode($errorResponse);
        if ($decoded) {
            echo "Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
        } else {
            // Strip HTML tags if it's a raw Laravel 500 error page
            echo "Response: " . substr(strip_tags($errorResponse), 0, 500) . "...\n\n";
        }
    }
}
echo "=================================================\n";
