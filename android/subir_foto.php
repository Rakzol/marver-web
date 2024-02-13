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
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $nombre = explode(".", $_POST['nombre'])[0];

        $preparada = $conexion->prepare("SELECT Responsable FROM EnvioPedidoCliente INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido WHERE PedidosCliente.FolioComprobante = :folio AND PedidosCliente.Tipocomprobante = :comprobante;");
        $preparada->bindValue(':folio', explode("c", $nombre )[0] );
        $preparada->bindValue(':comprobante', explode("c", $nombre )[1] );
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $nombre . " no esta asignado";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != $usuarios[0]['Clave'] ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $nombre . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        if(!file_put_contents( 'fotos/' . $nombre , base64_decode($_POST['foto']) )){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "No se pudo almacenar la foto: " . $nombre;
            echo json_encode($resultado);
            exit();
        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $nombre . " se entrego correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 4;
        $resultado["mensaje"] = "El pedido con el folio: " . $nombre . " no es valido";
        echo json_encode($resultado);
    }
?>