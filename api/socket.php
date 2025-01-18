<?php

$host = '127.0.0.1';
$port = 8080;

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
        $newClient = socket_accept($server);
        $clients[] = $newClient;
        echo "Nuevo cliente conectado.\n";
        unset($read[array_search($server, $read)]);
    }

    // Consultar la tabla de auditoría
    $conn = new PDO("sqlsrv:Server=localhost;Database=your_database", "username", "password");
    $stmt = $conn->query("SELECT * FROM products_audit WHERE action_sent = 0");
    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($changes as $change) {
        $message = json_encode($change);
        
        // Enviar el cambio a todos los clientes conectados
        foreach ($clients as $client) {
            @socket_write($client, $message);
        }

        // Marcar el registro como enviado
        $conn->query("UPDATE products_audit SET action_sent = 1 WHERE id = {$change['id']}");
    }

    // Leer mensajes de los clientes conectados
    foreach ($read as $client) {
        $data = @socket_read($client, 1024, PHP_BINARY_READ);
        if ($data === false || $data === "") {
            unset($clients[array_search($client, $clients)]);
            socket_close($client);
            echo "Cliente desconectado.\n";
        }
    }

    sleep(1); // Intervalo para verificar nuevos cambios
}
