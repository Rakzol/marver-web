<?php

    $contrasena = "1725jugo1725900876";

    $contenido_recibido = file_get_contents("php://input");
    $firma_recibida = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

    $firma_calculada = hash_hmac('sha256', $contenido_recibido, $contrasena);
    $son_iguales = hash_equals('sha256=' . $firma_calculada, $firma_recibida);

    if (!$son_iguales) {
        header('HTTP/1.0 401 Unauthorized');
        echo '<img src="https://i.pinimg.com/564x/79/c1/5c/79c15cf3af20d8417c41c384bb79cfb2.jpg" >';
    }else{
        exec('"C:\Program Files\Git\bin\git.exe" pull');
    }

?>