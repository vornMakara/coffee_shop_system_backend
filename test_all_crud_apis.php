<?php

/**
 * FULL E2E CRUD TEST SCRIPT
 * Tests Insert (POST), Update (PUT), and Delete (DELETE) against the live API.
 */

$baseUrl = 'https://coffee-shop-system.online';
// Replace this token with your actual Bearer token from Swagger
$token = 'YOUR_BEARER_TOKEN_HERE';

function sendRequest($method, $url, $token, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ];

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => $response];
}

function printResult($action, $res) {
    if ($res['code'] >= 200 && $res['code'] < 300) {
        echo "[$action] \033[32mSUCCESS\033[0m (HTTP {$res['code']})\n";
        return json_decode($res['body'], true);
    } else {
        echo "[$action] \033[31mFAILED\033[0m (HTTP {$res['code']})\n";
        echo "Response: " . $res['body'] . "\n";
        return null;
    }
}

echo "=================================================\n";
echo "Starting CRUD (Insert, Update, Delete) Test...\n";
echo "=================================================\n\n";

// 1. TEST BRANCHES
echo "--- Testing Branches ---\n";
// Create
$branchPayload = ['name' => 'Test Branch', 'code' => 'TB-99', 'address' => '123 Test St', 'is_active' => true];
$res = sendRequest('POST', "$baseUrl/api/v1/admin/branches", $token, $branchPayload);
$branch = printResult('CREATE Branch', $res);

if ($branch && isset($branch['data']['id'])) {
    $branchId = $branch['data']['id'];
    
    // Update
    $updatePayload = ['name' => 'Test Branch Updated'];
    $res = sendRequest('PUT', "$baseUrl/api/v1/admin/branches/$branchId", $token, $updatePayload);
    printResult('UPDATE Branch', $res);
    
    // 2. TEST TABLES (Requires Branch ID)
    echo "\n--- Testing Tables ---\n";
    $tablePayload = ['branch_id' => $branchId, 'table_number' => 'T-999', 'seating_capacity' => 4, 'status' => 'available'];
    $res = sendRequest('POST', "$baseUrl/api/v1/admin/tables", $token, $tablePayload);
    $table = printResult('CREATE Table', $res);
    
    if ($table && isset($table['data']['id'])) {
        $tableId = $table['data']['id'];
        $res = sendRequest('PUT', "$baseUrl/api/v1/admin/tables/$tableId", $token, ['status' => 'occupied']);
        printResult('UPDATE Table', $res);
        
        $res = sendRequest('DELETE', "$baseUrl/api/v1/admin/tables/$tableId", $token);
        printResult('DELETE Table', $res);
    }
    
    // Delete Branch (Cleanup)
    $res = sendRequest('DELETE', "$baseUrl/api/v1/admin/branches/$branchId", $token);
    printResult('DELETE Branch', $res);
}

// 3. TEST CATEGORIES
echo "\n--- Testing Categories ---\n";
$catPayload = ['name' => 'Test Category', 'description' => 'A test category', 'is_active' => true];
$res = sendRequest('POST', "$baseUrl/api/v1/admin/categories", $token, $catPayload);
$category = printResult('CREATE Category', $res);

if ($category && isset($category['data']['id'])) {
    $catId = $category['data']['id'];
    $res = sendRequest('PUT', "$baseUrl/api/v1/admin/categories/$catId", $token, ['name' => 'Test Cat Updated']);
    printResult('UPDATE Category', $res);
    
    $res = sendRequest('DELETE', "$baseUrl/api/v1/admin/categories/$catId", $token);
    printResult('DELETE Category', $res);
}

// 4. TEST CUSTOMERS
echo "\n--- Testing Customers ---\n";
$custPayload = ['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '1234567890'];
$res = sendRequest('POST', "$baseUrl/api/v1/admin/customers", $token, $custPayload);
$customer = printResult('CREATE Customer', $res);

if ($customer && isset($customer['data']['id'])) {
    $custId = $customer['data']['id'];
    $res = sendRequest('PUT', "$baseUrl/api/v1/admin/customers/$custId", $token, ['last_name' => 'Smith']);
    printResult('UPDATE Customer', $res);
    
    $res = sendRequest('DELETE', "$baseUrl/api/v1/admin/customers/$custId", $token);
    printResult('DELETE Customer', $res);
}

echo "\n=================================================\n";
echo "CRUD Test Completed!\n";
echo "=================================================\n";
