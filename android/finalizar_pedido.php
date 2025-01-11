<?php
try {
    require_once 'geometria/SphericalUtil.php';
    require_once 'geometria/PolyUtil.php';
    require_once 'geometria/MathUtil.php';

    header('Content-Type: application/json');
    
    switch($_POST["sucursal"]){
        case "Mochis":
            $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
            $latMarver = 25.794334;
            $lngMarver = -108.985983;
            break;
        case "Guasave":
            $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
            $latMarver = 25.571846;
            $lngMarver = -108.466774;
            break;
    }
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

    if ($_POST['codigo'] != '3334') {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "El Codigo no es correcto";
        echo json_encode($resultado);
        exit();
    }

    $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud FROM posiciones WHERE usuario = :repartidor ORDER BY fecha DESC;");
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();
    $posicion = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => $latMarver, 'lng' => $lngMarver], ['lat' => $posicion['latitud'], 'lng' => $posicion['longitud']]);
    if ($distancia_marver > 50) {
        $resultado["status"] = 2;
        $resultado["mensaje"] = "No esta dentro de la sucursal";
        echo json_encode($resultado);
        exit();
    }

    $preparada = $conexion->prepare("UPDATE EnvioPedidoCliente SET Extra2 = CONCAT(Extra2, '-FINALIZADO'), HoraLlegada = REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), Extra3 = 1 WHERE Pedido = :pedido AND Responsable = :repartidor AND Extra2 IN ('ENTREGADO', 'ENTREGADO NO PAGADO', 'NO ENTREGADO', 'RECHAZADO')");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->bindValue(':repartidor', $_POST['clave']);
    $preparada->execute();

    $preparada = $conexion->prepare("UPDATE PedidosCliente SET Status = 'R' WHERE Folio = :pedido");
    $preparada->bindValue(':pedido', $_POST['folio']);
    $preparada->execute();

    $resultado["status"] = 0;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se finalizo correctamente";
    echo json_encode($resultado);

    // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (Exception $exception) {
    // header('HTTP/1.1 500 ' . $exception->getMessage());

    $resultado["status"] = 4;
    $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
    echo json_encode($resultado);
}
