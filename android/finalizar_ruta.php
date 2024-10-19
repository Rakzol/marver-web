<?php
try {
    require_once 'geometria/SphericalUtil.php';
    require_once 'geometria/PolyUtil.php';
    require_once 'geometria/MathUtil.php';

    header('Content-Type: application/json');

    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true', 'MARITE', '2505M$RITE');
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
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

    $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud FROM posiciones WHERE usuario = :repartidor ORDER BY fecha DESC;");
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $posicion = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => 25.794285, 'lng' => -108.985924], ['lat' => $posicion['latitud'], 'lng' => $posicion['longitud']]);
    if ($distancia_marver > 15) {
        $resultado["status"] = 2;
        $resultado["mensaje"] = "No esta dentro de la sucursal";
        echo json_encode($resultado);
        exit();
    }

    /*FINALIZAR RUTA*/
    /* Finalizamos todas las rutas que esten iniciadas y no finalizadas */
    $preparada = $conexion->prepare('UPDATE rutas_repartidores SET fecha_fin = GETDATE(), fecha_actualizacion = GETDATE(), fecha_llegada_eficiencia = DATEDIFF(SECOND, fecha_llegada_estimada, GETDATE()) WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    
    /* 
    Preguntamos si hay alguna rutia sin iniciar y sin finalizar, que seria obviamente la ruta que tenga enlazado todos los pedidos PENDIENTE y EN RUTA
    lo cual no necesitaria ninguna accion, pero si no hay, vemos todos los pedidos PENDIENTE y EN RUTA que quedaron sin enlazar a una rauta sin finalizar
    para agregarlos a una nueva ruta sin finalizar (a la conclucion que se llega es que la ruta que se finalizo puede tener rutas PENDIENTE y EN RUTA)
    */
    $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
    if (count($rutas_repartidores) == 0) {

        $preparada = $conexion->prepare("
                SELECT Pedido, Fecha, HoraEnvio, HoraSalida, Extra2
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
                            SET Extra2 = 'SIGUIENTE RUTA', Extra3 = '1'
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

                if($pedido_pendiente['Extra2'] == 'EN RUTA'){
                    $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, HoraSalida, Extra1, Extra2) VALUES (:folio, :responsable, :fecha, :hora_envio, REPLACE( REPLACE(FORMAT(GETDATE(), 'hh:mm tt'), 'AM', 'a. m.'), 'PM', 'p. m.'), :id_pedido_repartidor, :status )");
                    $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
                    $preparada->bindValue(':responsable', $_POST['clave']);
                    $preparada->bindValue(':fecha', $pedido_pendiente['Fecha']);
                    $preparada->bindValue(':hora_envio', $pedido_pendiente['HoraEnvio']);
                    $preparada->bindValue(':id_pedido_repartidor', $id_pedido_repartidor);
                    $preparada->bindValue(':status', $pedido_pendiente['Extra2']);
                    $preparada->execute();
                }else{
                    $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, Extra1, Extra2) VALUES (:folio, :responsable, :fecha, :hora_envio, :id_pedido_repartidor, :status )");
                    $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
                    $preparada->bindValue(':responsable', $_POST['clave']);
                    $preparada->bindValue(':fecha', $pedido_pendiente['Fecha']);
                    $preparada->bindValue(':hora_envio', $pedido_pendiente['HoraEnvio']);
                    $preparada->bindValue(':id_pedido_repartidor', $id_pedido_repartidor);
                    $preparada->bindValue(':status', $pedido_pendiente['Extra2']);
                    $preparada->execute();
                }
            }
        }
    }
    /* FINALIZAR RUTA*/

    $resultado["status"] = 0;
    $resultado["mensaje"] = "Ruta finalizada correctamente";
    echo json_encode($resultado);

    // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    // header('HTTP/1.1 500 ' . $exception->getMessage());

    $resultado["status"] = 6;
    $resultado["mensaje"] = "Error al inicializar la ruta";
    echo json_encode($resultado);
}
