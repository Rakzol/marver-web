<?php
try {
    require_once 'geometria/SphericalUtil.php';
    require_once 'geometria/PolyUtil.php';
    require_once 'geometria/MathUtil.php';

    header('Content-Type: application/json');

    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true', 'MARITE', '2505M$RITE');
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    /* Verificamos que exista el vendedor */
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

    $preparada = $conexion->prepare("
        SELECT
        pc.FolioComprobante,
        pc.Tipocomprobante,
        pc.Cliente,
        CASE WHEN pc.Tipocomprobante != 3
            THEN cn.latitud
            ELSE ce.latitud
        END AS Latitud,
        CASE WHEN pc.Tipocomprobante != 3
            THEN cn.longitud
            ELSE ce.longitud
        END AS Longitud
        FROM PedidosCliente pc
        LEFT JOIN clientes_posiciones cn
        ON cn.clave = pc.Cliente
        LEFT JOIN ubicaciones_especiales ce
        ON ce.clave = pc.Cliente
        WHERE pc.Folio = :folio;
    ");
    $preparada->bindValue(':folio', $_POST['folio']);
    $preparada->execute();
    $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud, fecha FROM posiciones WHERE usuario = :vendedor ORDER BY fecha DESC;");
    $preparada->bindValue(':vendedor', $_POST['clave']);
    $preparada->execute();
    $posicionRepartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $distancia_de_cliente = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => $pedido['Latitud'], 'lng' => $pedido['Longitud']], ['lat' => $posicionRepartidor['latitud'], 'lng' => $posicionRepartidor['longitud']]);
    /* Verificamos si esta fuera de la ubicacion del cliente para no dejarlo entregar el pedido si se salio de ella */
    if ($distancia_de_cliente > 200 ) {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "Se encuentra lejos de la ubicacion del cliente";

        $preparada = $conexion->prepare('INSERT INTO posicionesBoton VALUES(:repartidor, :pedido, GETDATE(), :fechaUltimaUbicacion, :latitudCliente, :longitudCliente, :latitudRepartidor, :longitudRepartidor, :distancia, 0 )');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->bindValue(':pedido', $_POST['folio']);
        $preparada->bindValue(':fechaUltimaUbicacion', $posicionRepartidor['fecha']);
        $preparada->bindValue(':latitudCliente', $pedido['Latitud']);
        $preparada->bindValue(':longitudCliente', $pedido['Longitud']);
        $preparada->bindValue(':latitudRepartidor', $posicionRepartidor['latitud']);
        $preparada->bindValue(':longitudRepartidor', $posicionRepartidor['longitud']);
        $preparada->bindValue(':distancia', $distancia_de_cliente);
        $preparada->execute();

        echo json_encode($resultado);
        exit();
    }

    $preparada = $conexion->prepare('INSERT INTO posicionesBoton VALUES(:repartidor, :pedido, GETDATE(), :fechaUltimaUbicacion, :latitudCliente, :longitudCliente, :latitudRepartidor, :longitudRepartidor, :distancia, 1 )');
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->bindValue(':fechaUltimaUbicacion', $posicionRepartidor['fecha']);
    $preparada->bindValue(':latitudCliente', $pedido['Latitud']);
    $preparada->bindValue(':longitudCliente', $pedido['Longitud']);
    $preparada->bindValue(':latitudRepartidor', $posicionRepartidor['latitud']);
    $preparada->bindValue(':longitudRepartidor', $posicionRepartidor['longitud']);
    $preparada->bindValue(':distancia', $distancia_de_cliente);
    $preparada->execute();

    $preparada = $conexion->prepare("SELECT Extra1 FROM EnvioPedidoCliente WHERE Pedido = :pedido AND Responsable = :repartidor AND Extra2 = 'EN RUTA'");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $EnvioPedidoCliente = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    //HOT FIX Extra3 = 1
    $preparada = $conexion->prepare("UPDATE EnvioPedidoCliente SET Extra3 = 1, Extra2 = 'RECHAZADO' WHERE Pedido = :pedido AND Responsable = :repartidor AND Extra2 = 'EN RUTA'");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    $preparada = $conexion->prepare("UPDATE pedidos_repartidores SET fecha_llegada = GETDATE(), fecha_llegada_eficiencia = DATEDIFF(SECOND, fecha_llegada_estimada, GETDATE()) WHERE id = :id");
    $preparada->bindValue(':id', $EnvioPedidoCliente['Extra1']);
    $preparada->execute();

    $preparada = $conexion->prepare("SELECT ruta_repartidor FROM pedidos_repartidores WHERE id = :id");
    $preparada->bindValue(':id', $EnvioPedidoCliente['Extra1']);
    $preparada->execute();
    $rutaRepartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $preparada = $conexion->prepare("UPDATE rutas_repartidores SET fecha_actualizacion = GETDATE() WHERE id = :ruta_repartidor");
    $preparada->bindValue(':ruta_repartidor', $rutaRepartidor['ruta_repartidor']);
    $preparada->execute();

    /* ?????? */
    $preparada = $conexion->prepare("UPDATE Ventas SET Status = 21 WHERE Folio = :folio AND Tipocomprobante = :comprobante");
    $preparada->bindValue(':folio', $pedido['FolioComprobante']);
    $preparada->bindValue(':comprobante', $pedido['Tipocomprobante']);
    $preparada->execute();

    $preparada = $conexion->prepare("UPDATE Preventa SET Status = 21 WHERE Folio = :folio AND Tipocomprobante = :comprobante");
    $preparada->bindValue(':folio', $pedido['FolioComprobante']);
    $preparada->bindValue(':comprobante', $pedido['Tipocomprobante']);
    $preparada->execute();
    /* ?????? */

    $resultado["status"] = 0;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se marco como rechazado, correctamente";
    echo json_encode($resultado);

    // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    // header('HTTP/1.1 500 ' . $exception->getMessage());

    $resultado["status"] = 4;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
    echo json_encode($resultado);
}
