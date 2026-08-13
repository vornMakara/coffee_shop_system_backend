<?php

$url = 'https://coffee-shop-system.online/api/v1/admin/tables';
$token = 'YOUR_BEARER_TOKEN_HERE'; // Replace with the token from Swagger UI

$data = [
    'branch_id' => '6b4dcd35-5408-4c4d-8f9b-cd6c5a23fe1b',
    'table_number' => 'T-01',
    'seating_capacity' => 4,
    'status' => 'available'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status Code: " . $httpCode . "\n\n";
    echo "Response Body:\n";
    
    // Try to pretty print JSON if it's JSON
    $decoded = json_decode($response);
    if ($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT);
    } else {
        echo $response;
    }
}

curl_close($ch);
echo "\n";
