<?php
$cnpj = "06101525000108";
$token = "rh3pn2VK8bRcMxcU8bBi1n2rN6iQmbxEGUkrTm2SI5DwOl8BjrglAtZOMQKD";

$url = "https://api.cnpj.biz/v1/cnpj/$cnpj/"; // <-- Importante a barra final

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token"
]);

// Solução: seguir redirecionamentos
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Apenas para testes — desabilita verificação SSL (não recomendado em produção)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<pre>";
echo "Código HTTP: " . $info['http_code'] . "\n\n";
echo "Erro cURL: " . $err . "\n\n";
echo "Resposta:\n";
print_r($response);
echo "</pre>";