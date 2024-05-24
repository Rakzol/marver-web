<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

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

        $preparada = $conexion->prepare("SELECT Pedido FROM EnvioPedidoCliente INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido WHERE PedidosCliente.FolioComprobante = :folio AND PedidosCliente.Tipocomprobante = :comprobante;");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':comprobante', $_POST['comprobante']);
        $preparada->execute();
        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['Pedido'];

        $preparada = $conexion->prepare("DELETE EnvioPedidoCliente WHERE Pedido = :pedido");
        $preparada->bindValue(':pedido', $pedido);
        $preparada->execute();

        $preparada = $conexion->prepare("SELECT ruta_repartidor FROM pedidos_repartidores WHERE folio = :pedido");
        $preparada->bindValue(':pedido', $pedido);
        $preparada->execute();
        $ruta_repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['ruta_repartidor'];

        $preparada = $conexion->prepare("DELETE pedidos_repartidores WHERE folio = :pedido");
        $preparada->bindValue(':pedido', $pedido);
        $preparada->execute();

        $preparada = $conexion->prepare("UPDATE rutas_repartidores SET fecha_inicio = NULL, fecha_fin = NULL, ruta = NULL WHERE id = :ruta_repartidor");
        $preparada->bindValue(':ruta_repartidor', $ruta_repartidor);
        $preparada->execute();

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se elimino correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 5;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
        echo json_encode($resultado);
    }
?>