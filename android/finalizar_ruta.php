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

    $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud FROM posiciones WHERE usuario = :vendedor ORDER BY fecha DESC;");
    $preparada->bindValue(':vendedor', $_POST['clave']);
    $preparada->execute();
    $posicion = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => 25.794285, 'lng' => -108.985924], ['lat' => $posicion['latitud'], 'lng' => $posicion['longitud']]);
    if ($distancia_marver > 15) {
        $resultado["status"] = 2;
        $resultado["mensaje"] = "No esta dentro de la sucursal";
        echo json_encode($resultado);
        exit();
    }

    /*
            Si tiene una ruta iniciada, se finaliza, despues se crea una ruta nueva donde se asigna
            el pedido escaneado y todos los anteriroes donde el Extra2 de EnvioPedidoCliente sea 'PENDIENTE' OR 'EN RUTA' y del Responsable de este repartidor
        */
    $preparada = $conexion->prepare('UPDATE rutas_repartidores SET fecha_fin = GETDATE() WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    /*  Consultamos todos los envio EnvioPedidoCliente que tenga el Extra2 en 'PENDIENTE' OR 'EN RUTA' para ponerlo como 'SIGUIENTE RUTA'
                    de que se transfirio a otra ruta para ese pedido y lo agregamos de nuevo con otra id en Extra1 despues de agregar el que sera su id en pedidos_repartidores :D 
                    manteniendo su Extra2 */
    $preparada = $conexion->prepare("
                    SELECT Pedido, Extra2
                    FROM EnvioPedidoCliente
                    WHERE Responsable = :repartidor AND ( Extra2 = 'PENDIENTE' OR Extra2 = 'EN RUTA' )
                ");
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $pedidos_pendientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if (count($pedidos_pendientes) > 0) {

        $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor) VALUES (:repartidor)');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();
        $nueva_ruta = $conexion->lastInsertId();

        foreach ($pedidos_pendientes as $pedido_pendiente) {
            $preparada = $conexion->prepare("
                            UPDATE EnvioPedidoCliente
                            SET Extra2 = 'SIGUIENTE RUTA'
                            WHERE Responsable = :repartidor AND Pedido = :pedido AND ( Extra2 = 'PENDIENTE' OR Extra2 = 'EN RUTA' )
                        ");
            $preparada->bindValue(':repartidor', $_POST['clave']);
            $preparada->bindValue(':pedido', $pedido_pendiente['Pedido']);
            $preparada->execute();

            $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores VALUES (:ruta_repartidor,:folio, NULL, NULL)');
            $preparada->bindValue(':ruta_repartidor', $nueva_ruta);
            $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
            $preparada->execute();
            $id_pedido_repartidor = $conexion->lastInsertId();

            $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, Extra1, Extra2) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), :id_pedido_repartidor, :status )");
            $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
            $preparada->bindValue(':responsable', $_POST['clave']);
            $preparada->bindValue(':id_pedido_repartidor', $id_pedido_repartidor);
            $preparada->bindValue(':status', $pedido_pendiente['Extra2']);
            $preparada->execute();
        }
    }

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
