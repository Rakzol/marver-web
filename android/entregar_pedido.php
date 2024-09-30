<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        /* Verificamos que exista el vendedor */
        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }
        
        /* Obtenemos el folio del pedido que se va a entregar */
        $preparada = $conexion->prepare("SELECT EnvioPedidoCliente.Responsable, EnvioPedidoCliente.Pedido, PedidosCliente.Cliente FROM EnvioPedidoCliente INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido WHERE PedidosCliente.FolioComprobante = :folio AND PedidosCliente.Tipocomprobante = :comprobante;");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':comprobante', $_POST['comprobante']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no esta asignado";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != $_POST['clave'] ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        /*$preparada = $conexion->prepare("UPDATE EnvioPedidoCliente SET Extra1 = GETDATE() WHERE Pedido = :pedido");
        $preparada->bindValue(':pedido', $pedido[0]['Pedido']);
        $preparada->execute();*/

        $preparada = $conexion->prepare("UPDATE Ventas SET Status = 18 WHERE Folio = :folio AND Tipocomprobante = :comprobante");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':comprobante', $_POST['comprobante']);
        $preparada->execute();

        $preparada = $conexion->prepare("SELECT TOP 1 latitud, longitud FROM posiciones WHERE usuario = :vendedor ORDER BY fecha DESC;");
        $preparada->bindValue(':vendedor', $_POST['clave']);
        $preparada->execute();
        $posicion = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => 25.794285, 'lng' => -108.985924 ], [ 'lat' => $posicion['latitud'], 'lng' => $posicion['longitud'] ] );
        /* Verificamos si esta fuera de marver para poder actualizar la posicion del cliente */
        if( $distancia_marver >= 150 ){
            $preparada = $conexion->prepare("UPDATE clientes_posiciones SET latitud = :latitud, longitud = :longitud WHERE clave = :cliente;");
            $preparada->bindValue(':latitud', $posicion['latitud']);
            $preparada->bindValue(':longitud', $posicion['longitud']);
            $preparada->bindValue(':cliente', $pedido[0]['Cliente']);
            $preparada->execute();
        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se entrego correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 4;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
        echo json_encode($resultado);
    }
?>
