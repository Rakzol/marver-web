<?php

    require_once 'JWTHelper.php';

    $secretKey = '6969';

    $payload = [
        "usuario" => 6969,
        "nivel" => 1
    ];    

    $token = generarJWT($payload, $secretKey);

    echo json_encode(["token" => $token]);

?>
