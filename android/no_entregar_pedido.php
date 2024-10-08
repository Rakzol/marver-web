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

    $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud FROM posiciones WHERE usuario = :repartidor ORDER BY fecha DESC;");
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $posicionRepartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $distancia_de_cliente = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => $pedido['Latitud'], 'lng' => $pedido['Longitud']], ['lat' => $posicionRepartidor['latitud'], 'lng' => $posicionRepartidor['longitud']]);
    /* Verificamos si esta fuera de la ubicacion del cliente para no dejarlo entregar el pedido si se salio de ella */
    if ($distancia_de_cliente > 100) {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "Se encuentra lejos de la ubicacion del cliente";
        echo json_encode($resultado);
        exit();
    }

    $preparada = $conexion->prepare("SELECT Extra1 FROM EnvioPedidoCliente WHERE Pedido = :pedido AND Responsable = :repartidor AND Extra2 = 'EN RUTA'");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $EnvioPedidoCliente = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $preparada = $conexion->prepare("UPDATE EnvioPedidoCliente SET Extra2 = 'NO ENTREGADO' WHERE Pedido = :pedido AND Responsable = :repartidor AND Extra2 = 'EN RUTA'");
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
    $preparada = $conexion->prepare("UPDATE Ventas SET Status = 19 WHERE Folio = :folio AND Tipocomprobante = :comprobante");
    $preparada->bindValue(':folio', $pedido['FolioComprobante']);
    $preparada->bindValue(':comprobante', $pedido['Tipocomprobante']);
    $preparada->execute();

    $preparada = $conexion->prepare("UPDATE Preventa SET Status = 19 WHERE Folio = :folio AND Tipocomprobante = :comprobante");
    $preparada->bindValue(':folio', $pedido['FolioComprobante']);
    $preparada->bindValue(':comprobante', $pedido['Tipocomprobante']);
    $preparada->execute();
    /* ?????? */

    $resultado["status"] = 0;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se marco como no entregado, correctamente";
    echo json_encode($resultado);

    // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    // header('HTTP/1.1 500 ' . $exception->getMessage());

    $resultado["status"] = 4;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
    echo json_encode($resultado);
}
