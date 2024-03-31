<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_GET['clave']);
        $preparada->bindValue(':contrasena', $_GET['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_GET['clave']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) > 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "Tienes entregas en proceso";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("SELECT PedidosCliente.Folio, EnvioPedidoCliente.Responsable FROM PedidosCliente LEFT JOIN EnvioPedidoCliente ON EnvioPedidoCliente.Pedido = PedidosCliente.Folio WHERE PedidosCliente.Folio = :folio");
        $preparada->bindValue(':folio', $_GET['folio']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 && true ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " no existe";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != NULL && true ){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
        $preparada->bindValue(':repartidor', $_GET['clave']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if(count($rutas_repartidores) == 0){
            $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor) VALUES (:repartidor)');
            $preparada->bindValue(':repartidor', $_GET['clave']);
            $preparada->execute();

            $id_ruta_reparto = $conexion->lastInsertId();
        }else{
            $id_ruta_reparto = $rutas_repartidores[0]['id'];
        }

        $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores VALUES (:ruta_repartidor,:folio)');
        $preparada->bindValue(':ruta_repartidor', $id_ruta_reparto);
        $preparada->bindValue(':folio', $_GET['folio']);
        $preparada->execute();

        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoClienteTest (Pedido, Responsable, Fecha, HoraEnvio) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ) )");
        $preparada->bindValue(':folio', $_GET['folio']);
        $preparada->bindValue(':responsable', $_GET['clave']);
        $preparada->execute();

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " se asigno correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 5;
        $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " no es valido";
        echo json_encode($resultado);
    }
?>