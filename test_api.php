<?php
require_once 'includes/db.php';
$ch = curl_init();
$data = [
    "model" => "meta-llama/llama-3-8b-instruct:free",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful travel assistant. Always return valid HTML without markdown formatting."],
        ["role" => "user", "content" => "test"]
    ]
];
curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$headers = [
    "Authorization: Bearer " . OPENROUTER_API_KEY,
    "HTTP-Referer: http://localhost/Globxa",
    "X-Title: Globexa Travel Planner",
    "Content-Type: application/json"
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
echo "HTTP Code: $httpcode\n";
echo "cURL Error: $error\n";
echo "Response: $response\n";
?>
