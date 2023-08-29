<?php
    //Iniciamos la sesion para capturar los datos y poder borrar la cookie
    session_start();

    //Hacemos que la cookie que guarda el ID de la sesion expire y la borre el navegador
    // $params = session_get_cookie_params();
    // setcookie(session_name(), '', time() - 42000,
    //     $params["path"], $params["domain"],
    //     $params["secure"], $params["httponly"]
    // );

    //Borramos los datos de la sesion
    session_destroy();

    header("Location: https://www.marverrefacciones.mx/");
?>