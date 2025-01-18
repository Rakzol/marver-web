<?php
// Dirección y puerto del servidor
$host = '127.0.0.1';
$port = 8080;

// Crear un socket TCP/IP
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $host, $port);
socket_listen($socket);

echo "Servidor WebSocket iniciado en $host:$port\n";

$clients = [$socket];

while (true) {
    $read = $clients;
    socket_select($read, $write = null, $except = null, 0, 10);

    // Detectar nuevas conexiones
    if (in_array($socket, $read)) {
        $newClient = socket_accept($socket);
        $clients[] = $newClient;

        echo "Nuevo cliente conectado.\n";
        $key = array_search($socket, $read);
        unset($read[$key]);
    }

    // Manejar mensajes de los clientes conectados
    foreach ($read as $client) {
        $data = socket_read($client, 1024, PHP_BINARY_READ);
        if (!$data) {
            // Desconectar cliente
            $index = array_search($client, $clients);
            unset($clients[$index]);
            socket_close($client);
            echo "Cliente desconectado.\n";
            continue;
        }

        // Handshake inicial
        if (strpos($data, 'GET') !== false) {
            $headers = performHandshake($data, $client);
            echo "Handshake completado con cliente.\n";
            continue;
        }

        // Decodificar el mensaje recibido
        $decodedData = decodeMessage($data);
        echo "Mensaje recibido: $decodedData\n";

        // Enviar respuesta
        $response = encodeMessage("Servidor dice: $decodedData");
        socket_write($client, $response);
    }
}

socket_close($socket);

// Realizar el handshake para establecer conexión WebSocket
function performHandshake($data, $client) {
    $lines = preg_split("/\r\n/", $data);
    $headers = [];
    foreach ($lines as $line) {
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line);
            $headers[$key] = trim($value);
        }
    }

    $secWebSocketKey = $headers['Sec-WebSocket-Key'];
    $secWebSocketAccept = base64_encode(pack(
        'H*',
        sha1($secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
    ));

    $upgradeHeaders = "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $secWebSocketAccept\r\n\r\n";

    socket_write($client, $upgradeHeaders);
}

// Decodificar mensaje recibido de WebSocket
function decodeMessage($data) {
    $payloadLen = ord($data[1]) & 127;
    if ($payloadLen === 126) {
        $masks = substr($data, 4, 4);
        $payload = substr($data, 8);
    } elseif ($payloadLen === 127) {
        $masks = substr($data, 10, 4);
        $payload = substr($data, 14);
    } else {
        $masks = substr($data, 2, 4);
        $payload = substr($data, 6);
    }

    $text = '';
    for ($i = 0; $i < strlen($payload); ++$i) {
        $text .= $payload[$i] ^ $masks[$i % 4];
    }

    return $text;
}

// Codificar mensaje para enviar a WebSocket
function encodeMessage($text) {
    $b1 = 0x81; // 0x81 = FIN + texto
    $length = strlen($text);

    if ($length <= 125) {
        return pack('CC', $b1, $length) . $text;
    } elseif ($length <= 65535) {
        return pack('CCn', $b1, 126, $length) . $text;
    } else {
        return pack('CCNN', $b1, 127, 0, $length) . $text;
    }
}
