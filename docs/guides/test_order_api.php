<?php
// Simulate a request to the API

// First, get a user token by making a login request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'channel' => 'pos',
    'items' => [
        [
            'menu_item_id' => 69,
            'quantity' => 1
        ]
    ],
    'payment' => [
        'amount' => 1000,
        'method' => 'cash',
        'reference' => 'POS-123'
    ],
    'discount' => 0,
    'tax' => 0
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response:\n";
echo $response . "\n";
