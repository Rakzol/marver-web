<?php
// Variables de JWT
require_once 'credenciales.php';

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
function generarJWT($payload) {
    global $secretKey;

    // Header
    $header = [
        "alg" => "HS256", // Algoritmo
        "typ" => "JWT"    // Tipo de token
    ];

    // Codificar Header y Payload
    $headerEncoded = base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
    $payloadEncoded = base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

    // Crear la firma
    $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true);
    $signatureEncoded = base64UrlEncode($signature);

    // Retornar el token
    return "$headerEncoded.$payloadEncoded.$signatureEncoded";
}

function obtenerJWT(){
    $headers = getallheaders();
    $token = '';
    if (isset($headers['Authorization'])) {
        return str_replace('Bearer ', '', $headers['Authorization']);
    }
    return $token;
}

// Validar un JWT
function validarJWT($jwt) {
    global $secretKey;

    // Separar las partes del token
    $parts = explode('.', $jwt);
    if (count($parts) != 3) {
        return false; // Token inválido
    }

    [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

    // Verificar la firma
    $signatureVerified = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true);
    $signatureVerifiedEncoded = base64UrlEncode($signatureVerified);

    if ($signatureVerifiedEncoded != $signatureProvided) {
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
