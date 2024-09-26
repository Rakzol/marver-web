<?php
try {
    header('Content-Type: application/json');

    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true', 'MARITE', '2505M$RITE');
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    /* Verificamos que el vendedor exista */
    $preparada = $conexion->prepare('SELECT TOP 1 Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
    $preparada->bindValue(':clave', $_POST['clave']);
    $preparada->bindValue(':contrasena', $_POST['contraseña']);
    $preparada->execute();

    $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) == 0) {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "El vendedor no existe";
        echo json_encode($resultado);
        exit();
    }

    $preparada = $conexion->prepare("
        SELECT TOP 1
        en.Responsable,
        pc.Cliente,
        pc.FolioComprobante,
        CASE WHEN pc.FolioComprobante > 0
            THEN cn.latitud
            ELSE ce.latitud
        END AS Latitud,
        CASE WHEN pc.FolioComprobante > 0
            THEN cn.longitud
            ELSE ce.longitud
        END AS Longitud
        FROM PedidosCliente pc
        LEFT JOIN EnvioPedidoCliente en
        ON en.Pedido = pc.Folio AND en.Extra2 NOT IN ( 'NO ENTREGADO-REENVIADO', 'SIGUIENTE RUTA' )
        LEFT JOIN clientes_posiciones cn
        ON cn.clave = pc.Cliente
        LEFT JOIN ubicaciones_especiales ce
        ON ce.clave = pc.Cliente
        WHERE pc.Folio = :folio;
        ");
    $preparada->bindValue(':folio', $_POST['folio']);
    $preparada->execute();

    $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
    if (count($pedido) == 0) {
        $resultado["status"] = 2;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no existe";
        echo json_encode($resultado);
        exit();
    }

    if (!$pedido[0]['Latitud'] || !$pedido[0]['Longitud']) {
        $resultado["status"] = 3;
        $resultado["mensaje"] = "El cliente con la clave: " . $pedido[0]['Cliente'] . " no tiene ubicacion en el mapa";
        echo json_encode($resultado);
        exit();
    }

    if ($pedido[0]['Responsable']) {
        $resultado["status"] = 3;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
        echo json_encode($resultado);
        exit();
    }

    /*FINALIZAR RUTA*/
    /*
            Si tiene una ruta iniciada, se finaliza, despues se crea una ruta nueva donde se asigna
            el pedido escaneado y todos los anteriroes donde el Extra2 de EnvioPedidoCliente sea 'PENDIENTE' OR 'EN RUTA' y del Responsable de este repartidor
        */
    $preparada = $conexion->prepare('UPDATE rutas_repartidores SET fecha_fin = GETDATE(), fecha_actualizacion = GETDATE() WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    /*  Consultamos todos los envio EnvioPedidoCliente que tenga el Extra2 en 'PENDIENTE' OR 'EN RUTA' para ponerlo como 'SIGUIENTE RUTA'
                de que se transfirio a otra ruta para ese pedido y lo agregamos de nuevo con otra id en Extra1 despues de agregar el que sera su id en pedidos_repartidores :D 
                manteniendo su Extra2 */
    $preparada = $conexion->prepare("
                SELECT Pedido, Responsable, Fecha, HoraEnvio, HoraSalida, Extra2
                FROM EnvioPedidoCliente
                WHERE Responsable = :repartidor AND Extra2 IN ( 'PENDIENTE', 'EN RUTA' )
            ");
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $pedidos_pendientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if (count($pedidos_pendientes) > 0) {

        $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor, fecha_actualizacion) VALUES (:repartidor, GETDATE())');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();
        $nueva_ruta = $conexion->lastInsertId();

        foreach ($pedidos_pendientes as $pedido_pendiente) {
            $preparada = $conexion->prepare("
                        UPDATE EnvioPedidoCliente
                        SET Extra2 = 'SIGUIENTE RUTA'
                        WHERE Responsable = :repartidor AND Pedido = :pedido AND Extra2 IN ( 'PENDIENTE', 'EN RUTA' )
                    ");
            $preparada->bindValue(':repartidor', $_POST['clave']);
            $preparada->bindValue(':pedido', $pedido_pendiente['Pedido']);
            $preparada->execute();

            $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores (ruta_repartidor, folio) VALUES (:ruta_repartidor, :folio)');
            $preparada->bindValue(':ruta_repartidor', $nueva_ruta);
            $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
            $preparada->execute();
            $id_pedido_repartidor = $conexion->lastInsertId();

            $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, HoraSalida, Extra1, Extra2) VALUES (:folio, :responsable, :fecha, :hora_envio, :hora_salida, :id_pedido_repartidor, :status )");
            $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
            $preparada->bindValue(':responsable', $pedido_pendiente['Responsable']);
            $preparada->bindValue(':fecha', $pedido_pendiente['Fecha']);
            $preparada->bindValue(':hora_envio', $pedido_pendiente['HoraEnvio']);
            $preparada->bindValue(':hora_salida', $pedido_pendiente['HoraSalida']);
            $preparada->bindValue(':id_pedido_repartidor', $id_pedido_repartidor);
            $preparada->bindValue(':status', $pedido_pendiente['Extra2']);
            $preparada->execute();
        }
    }
    /* FINALIZAR RUTA*/

    $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
    if (count($rutas_repartidores) == 0) {
        $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor, fecha_actualizacion) VALUES (:repartidor, GETDATE())');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        $id_ruta_reparto = $conexion->lastInsertId();
    } else {
        $id_ruta_reparto = $rutas_repartidores[0]['id'];
    }

    $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores (ruta_repartidor, folio) VALUES (:ruta_repartidor, :folio)');
    $preparada->bindValue(':ruta_repartidor', $id_ruta_reparto);
    $preparada->bindValue(':folio', $_POST['folio']);
    $preparada->execute();
    $id_pedido_nuevo = $conexion->lastInsertId();

    if($pedido[0]['FolioComprobante'] > 0){
        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, Extra1, Extra2) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), :id_pedido_nuevo, 'PENDIENTE' )");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':responsable', $_POST['clave']);
        $preparada->bindValue(':id_pedido_nuevo', $id_pedido_nuevo);
        $preparada->execute();
    }else{
        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, HoraSalida, Extra1, Extra2) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), :id_pedido_nuevo, 'EN RUTA')");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':responsable', $_POST['clave']);
        $preparada->bindValue(':id_pedido_nuevo', $id_pedido_nuevo);
        $preparada->execute();
    }

    /* ????? */
    $preparada = $conexion->prepare("UPDATE PedidosCliente SET Status = 'E' WHERE Folio = :pedido");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->execute();
    /* ?????? */

    $resultado["status"] = 0;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se asigno correctamente";
    echo json_encode($resultado);

    // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    // header('HTTP/1.1 500 ' . $exception->getMessage());

    $resultado["status"] = 5;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
    echo json_encode($resultado);
}
