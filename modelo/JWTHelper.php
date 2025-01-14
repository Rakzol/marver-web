<?php
// Funciones para trabajar con JWT

// Función para codificar en Base64 URL-safe
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

// Función para decodificar Base64 URL-safe
function base64UrlDecode($data) {
    $decodedData = str_replace(['-', '_'], ['+', '/'], $data);
    return base64_decode($decodedData);
}

// Generar un JWT
function generarJWT($payload, $secretKey) {
    // Header
    $header = [
        "alg" => "HS256", // Algoritmo
        "typ" => "JWT"    // Tipo de token
    ];

    // Codificar Header y Payload
    $headerEncoded = base64UrlEncode(json_encode($header));
    $payloadEncoded = base64UrlEncode(json_encode($payload));

    // Crear la firma
    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true);
    $signatureEncoded = base64UrlEncode($signature);

    // Retornar el token
    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

// Validar un JWT
function validarJWT($jwt, $secretKey) {
    // Separar las partes del token
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false; // Token inválido
    }

    [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

    // Verificar la firma
    $signatureVerified = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true);
    $signatureVerifiedEncoded = base64UrlEncode($signatureVerified);

    if ($signatureVerifiedEncoded !== $signatureProvided) {
        return false; // Firma inválida
    }

    // Decodificar el payload
    $payload = json_decode(base64UrlDecode($payloadEncoded), true);

    // Verificar la expiración (exp)
    if (isset($payload['exp']) && time() > $payload['exp']) {
        return false; // Token expirado
    }

    return $payload; // Token válido, retorna el payload
}
?>
