<?php


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

// Configurar el servidor como no bloqueante
stream_set_blocking($server, false);

echo "Servidor WSS escuchando en tls://0.0.0.0:8888\n";

$clients = []; // Lista de clientes conectados
$handshakes = []; // Estado de handshake por cliente

while (true) {
    $read = $clients;
    $read[] = $server; // Incluir el servidor para detectar nuevas conexiones
    $write = null;
    $except = null;

    // Usar stream_select para manejar múltiples conexiones
    if (stream_select($read, $write, $except, 0, 10)) {
        // Manejar nuevas conexiones
        if (in_array($server, $read)) {
            $newClient = @stream_socket_accept($server, 0);
            if ($newClient) {
                stream_set_blocking($newClient, false);
                $clients[] = $newClient;
                $handshakes[(int)$newClient] = false; // Marcar como no conectado
                echo "Nuevo cliente conectado\n";
            }
            unset($read[array_search($server, $read)]);
        }

        // Leer datos de los clientes conectados
        foreach ($read as $client) {
            $data = @fread($client, 1024);

            if ($data === false || $data === '') {
                // Si no hay datos, cerrar la conexión
                fclose($client);
                unset($clients[array_search($client, $clients)]);
                unset($handshakes[(int)$client]);
                echo "Cliente desconectado\n";
                continue;
            }

            // Verificar si el cliente ya completó el handshake
            if (!$handshakes[(int)$client]) {
                if (strpos($data, 'Sec-WebSocket-Key:') !== false) {
                    // Procesar el handshake
                    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $matches)) {
                        $key = trim($matches[1]);
                        $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

                        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                                    "Upgrade: websocket\r\n" .
                                    "Connection: Upgrade\r\n" .
                                    "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

                        fwrite($client, $response);
                        $handshakes[(int)$client] = true; // Handshake completo
                        echo "Handshake completado con un cliente\n";
                    }
                } else {
                    // Si no hay handshake, cerrar el cliente
                    //fclose($client);
                    //unset($clients[array_search($client, $clients)]);
                    //unset($handshakes[(int)$client]);
                    //echo "Handshake fallido, cliente desconectado\n";
                }
                continue; // No procesar más datos en este ciclo
            }

            // Si el handshake está completo, procesar los mensajes
            $message = unmaskWebSocketPayload($data);
            echo "Mensaje recibido: $message\n";

            // Retransmitir a todos los demás clientes
            $response = maskWebSocketPayload("Cliente dice: $message");
            foreach ($clients as $otherClient) {
                if ($otherClient !== $client) {
                    fwrite($otherClient, $response);
                }
            }
        }
    }
}

// Función para desenmarcar la carga útil de WebSocket
function unmaskWebSocketPayload($payload) {
    $length = ord($payload[1]) & 127;

    if ($length === 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length === 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    $decoded = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $decoded .= $data[$i] ^ $masks[$i % 4];
    }

    return $decoded;
}

// Función para enmarcar la carga útil de WebSocket
function maskWebSocketPayload($data) {
    $frame = chr(129); // 10000001 (FIN + texto)
    $length = strlen($data);

    if ($length <= 125) {
        $frame .= chr($length);
    } elseif ($length <= 65535) {
        $frame .= chr(126) . pack('n', $length);
    } else {
        $frame .= chr(127) . pack('Q', $length);
    }

    $frame .= $data;
    return $frame;
}
