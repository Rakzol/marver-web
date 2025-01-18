<?php

$host = '127.0.0.1';
$port = 8888;

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($server, $host, $port);
socket_listen($server);

$clients = [];
echo "Servidor WebSocket iniciado en ws://$host:$port\n";

while (true) {
    // Monitorear nuevos clientes
    $read = $clients;
    $read[] = $server;
    socket_select($read, $write, $except, 0, 10);

    if (in_array($server, $read)) {
        echo "Intento usuarios\n";
        $newClient = socket_accept($server);
        
        // Realizar el handshake WebSocket
        $request = socket_read($newClient, 1024);
        $headers = parse_headers($request);
        
        // Si es un WebSocket, responder al handshake
        if (isset($headers['Sec-WebSocket-Key'])) {
            $acceptKey = base64_encode(sha1($headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
            socket_write($newClient, $response);
            $clients[] = $newClient;
            echo "Nuevo cliente conectado.\n";
        }
        
        unset($read[array_search($server, $read)]);
    }

    // Leer mensajes de los clientes conectados
    foreach ($read as $client) {
        if ($client != $server) {  // Evitar leer del servidor mismo
            $data = socket_read($client, 1024, PHP_BINARY_READ);
            if ($data) {
                // Decodificar el mensaje recibido
                $decodedMessage = decodeMessage($data);
                echo $decodedMessage;
                
                // Reenviar el mensaje a todos los demás clientes
                foreach ($clients as $otherClient) {
                    if ($otherClient != $client) {
                        sendMessage($otherClient, $decodedMessage);
                    }
                }
            }else {
                unset($clients[array_search($client, $clients)]);
                socket_close($client);
                echo "Cliente desconectado.\n";
            }
        }
    }

    //sleep(1); // Intervalo para verificar nuevos cambios
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
    // Codificar el mensaje en formato WebSocket (frame)
    $frame = chr(0x81) . chr(strlen($message)) . $message;
    socket_write($client, $frame);
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