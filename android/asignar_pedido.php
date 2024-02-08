<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Nombre = :usuario AND Contraseña = :contrasena');
        $preparada->bindValue(':usuario', $_POST['usuario']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("SELECT PedidosCliente.Folio, EnvioPedidoCliente.Responsable FROM PedidosCliente LEFT JOIN EnvioPedidoCliente ON EnvioPedidoCliente.Pedido = PedidosCliente.Folio WHERE PedidosCliente.Folio = :folio");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no existe";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != NULL ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoClienteTest (Pedido, Responsable, Fecha, HoraEnvio) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ) )");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':responsable', $usuarios[0]['Clave']);
        $preparada->execute();

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se asigno correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 4;
        $resultado["mensaje"] = $exception->getLine() . " " . $exception->getMessage() . "El pedido con el folio: " . $_POST['folio'] . " no es valido";
        echo json_encode($resultado);
    }
?>