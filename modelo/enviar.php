<?php

    $Bearer = "EAAVE5rJaMKwBO980nYpnZCI4PiJVcssTkhplxFLNvvyUVdFvwqd5m5JMPbLZCA6XxWpNANrd9QoRNPsk6WhQZBhfvcFsps5a1Bp7PHWSkhycZCwb31GH2BkupUPySiyi0ZA1gE9mdL0SZBPWEJonpZAVkoZCjPg2XZCgU6dLZAzgP1UcGKaiUelN8s9jCDoZBi0FtKq";
    //NUESTRO TELEFONO
    $telefono = $_GET['cel'];
    //URL A DONDE SE MANDARA EL MENSAJE
    $url = 'https://graph.facebook.com/v20.0/426242453898687/messages';

    //CONFIGURACION DEL MENSAJE
    $mensaje = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"to": "'.$telefono.'", '
            . '"type": "template", '
            . '"template": '
            . '{'
            . '     "name": "hello_world",'
            . '     "language":{ "code": "es_MX" } ,'
            .       '"components": [
                        {
                            "type": "body",
                            "parameters": [
                            {
                                "type": "text",
                                "text": "Nombre del cliente"
                            },
                            {
                                "type": "text",
                                "text": "Folio del paquete"
                            },
                            {
                                "type": "text",
                                "text": "Nombre del camión"
                            }]
                        }]'
            . '} '
            . '}';
    echo $mensaje;
    //DECLARAMOS LAS CABECERAS
    $header = array("Authorization: Bearer " . $Bearer, "Content-Type: application/json");
    //INICIAMOS EL CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
    $response = json_decode(curl_exec($curl), true);
    //IMPRIMIMOS LA RESPUESTA 
    print_r($response);
    //OBTENEMOS EL CODIGO DE LA RESPUESTA
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //CERRAMOS EL CURL
curl_close($curl);

?>