<?php
require_once 'includes/db.php';
$ch = curl_init();
$data = [
    "model" => "google/gemma-7b-it:free",
    "messages" => [
        ["role" => "user", "content" => "test"]
    ]
];
curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$headers = [
    "Authorization: Bearer " . OPENROUTER_API_KEY,
    "Content-Type: application/json"
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
?>
