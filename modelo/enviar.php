<?php

    $Bearer = "EAAVE5rJaMKwBOwLIs594cVSihea0hQC2sgo3hx6vgjmZBQwERZBC7FiI2vMr7h7KfZB9My3lHrGT3bwZA8dOq6ZAbjUjJugkp7GYNZABIaQJxNmLiBnGqX8K99I7OJxc0rBervA7vZCpZAbA9iRMeG8FmqTZAZCmFExJIo3bBYCYXQpNn4wmFUixcyYxcqNui6bQNR";
    //NUESTRO TELEFONO
    $telefono = $_GET['cel'];
    //URL A DONDE SE MANDARA EL MENSAJE
    $url = 'https://graph.facebook.com/v15.0/105233795800723/messages';

    //CONFIGURACION DEL MENSAJE
    $mensaje = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"to": "'.$telefono.'", '
            . '"type": "template", '
            . '"template": '
            . '{'
            . '     "name": "hello_world",'
            . '     "language":{ "code": "en_US" } '
            . '} '
            . '}';
    //DECLARAMOS LAS CABECERAS
    $header = array("Authorization: Bearer " . $Bearer, "Content-Type: application/json");
    //INICIAMOS EL CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
    $response = json_decode(curl_exec($curl), true);
    //IMPRIMIMOS LA RESPUESTA 
    print_r($response);
    //OBTENEMOS EL CODIGO DE LA RESPUESTA
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //CERRAMOS EL CURL
curl_close($curl);

?>