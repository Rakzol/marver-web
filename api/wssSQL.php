<?php

require_once 'JWTHelper.php';

$host = '0.0.0.0';
$port = 8888;

// Ruta al archivo de certificado SSL y clave
$certFile = 'crt.crt';
$keyFile = 'key.pem';

// Crear un servidor SSL (HTTPS)
$context = stream_context_create([
    'ssl' => [
        'local_cert'  => $certFile,
        'local_pk'    => $keyFile,
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => false,   // Set to true if using self-signed cert
    ]
]);

// Crear servidor WebSocket sobre SSL
$server = stream_socket_server("tls://$host:$port", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
if (!$server) {
    echo "Error al iniciar servidor: $errstr ($errno)\n";
    exit(1);
}

$clients = [];
echo "Servidor WebSocket iniciado en wss://$host:$port\n";

while (true) {
    // Monitorear nuevos clientes
    $read = $clients;
    $read[] = $server;
    stream_select($read, $write, $except, 0, 10);

    if (in_array($server, $read)) {
        echo "Intento usuarios\n";
        $newClient = stream_socket_accept($server);

        // Realizar el handshake WebSocket
        $request = fread($newClient, 1024);
        $headers = parse_headers($request);
        
        // Si es un WebSocket, responder al handshake
        if (isset($headers['Sec-WebSocket-Key'])) {
            $acceptKey = base64_encode(sha1($headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
            fwrite($newClient, $response);
            $clients[] = $newClient;
            echo "Nuevo cliente conectado.\n";
        }
        
        unset($read[array_search($server, $read)]);
    }

    /* SQL */

    $conexion = new PDO($arregloConexionMochis[0], $arregloConexionMochis[1], $arregloConexionMochis[2]);
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $preparada = $conexion->prepare("SELECT * FROM bitacoras_backup");
    $preparada->execute();
    $bitacoras = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if($bitacoras){
        foreach ($clients as $otherClient) {
            sendMessage($otherClient, json_encode(["bitacoras" => $bitacoras], JSON_UNESCAPED_UNICODE));
        }
    }

    $preparada = $conexion->prepare("DELETE bitacoras_backup");
    $preparada->execute();

    /* SQL */

    sleep(1); // Intervalo para verificar nuevos cambios
}

// Función para analizar los encabezados HTTP
function parse_headers($request) {
    $headers = [];
    $lines = explode("\r\n", $request);
    foreach ($lines as $line) {
        if (preg_match('/^([^:]+): (.+)$/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }
    return $headers;
}

// Función para enviar mensajes a los clientes WebSocket
function sendMessage($client, $message) {
    // Convertir el mensaje en un string de bytes (UTF-8 o JSON)
    $message = (string)$message;
    $messageLength = strlen($message);
    
    // Determinar el encabezado para el WebSocket según el tamaño del mensaje
    $header = chr(0x81);  // 0x81 indica un mensaje de texto

    if ($messageLength <= 125) {
        // Si el mensaje es corto (<= 125 bytes), solo agregamos la longitud directamente
        $header .= chr($messageLength);
    } elseif ($messageLength > 125 && $messageLength <= 65535) {
        // Si el mensaje es más largo, usamos un encabezado de 2 bytes para la longitud
        $header .= chr(126) . pack('n', $messageLength);  // 126 significa longitud de 2 bytes
    } else {
        // Si el mensaje es aún más largo (mayor a 65535), usamos 8 bytes para la longitud
        $header .= chr(127) . pack('J', $messageLength);  // 127 significa longitud de 8 bytes
    }
    
    // Enviar el mensaje
    fwrite($client, $header . $message);
}

// Función para decodificar un mensaje WebSocket recibido
function decodeMessage($data) {
    // Obtener la longitud de los datos
    $length = ord($data[1]) & 127; // Extraer longitud de datos (segundo byte)

    // Si la longitud es 126, leer 2 bytes adicionales para la longitud
    if ($length === 126) {
        $length = unpack('n', substr($data, 2, 2))[1];
        $data = substr($data, 4); // Eliminar los 2 bytes de longitud extendida
    }
    // Si la longitud es 127, leer 8 bytes adicionales para la longitud
    elseif ($length === 127) {
        $length = unpack('J', substr($data, 2, 8))[1];
        $data = substr($data, 10); // Eliminar los 8 bytes de longitud extendida
    } else {
        $data = substr($data, 2); // Eliminar el primer byte (opcod) y el byte de longitud
    }

    // Leer la máscara
    $mask = substr($data, 0, 4);
    $data = substr($data, 4); // Eliminar los primeros 4 bytes de la máscara

    // Desenmascarar los datos
    $message = '';
    for ($i = 0; $i < $length; $i++) {
        $message .= $data[$i] ^ $mask[$i % 4]; // Desenmascarar byte a byte
    }

    return $message;
}
