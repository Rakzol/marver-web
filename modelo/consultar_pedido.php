<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cliente, FechaPedido, HoraPedido, TotalPedido, Status, FolioComprobante, MEntrega FROM PedidosCliente WHERE Folio = :folio");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $retorno['pedido'] = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        if($retorno['pedido']['Cliente'] != $datos['cliente']['Clave']){
            header('HTTP/1.1 403');
            exit();
        }

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Observaciones FROM EnvioPedidoCliente WHERE Pedido = :folio");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $envios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($envios) ){
            $retorno['Observaciones'] = $envios[0]['Observaciones'];
            if( $retorno['Observaciones'] == NULL ){
                $retorno['Observaciones'] = '';
            }    
        }else{
            $retorno['Observaciones'] = '';
        }

        $preparada = $datos['conexion_base_sucursal']->prepare(
            'SELECT pcd.CodigoArticulo, pcd.CantidadPedida, pcd.PrecioPedido, pcd.ImportePedida, pcd.DescuentoPedida FROM PedidoClientesDetalle pcd ' .
            'WHERE pcd.Folio = :folio ' .
            'GROUP BY pcd.CodigoArticulo, pcd.CantidadPedida, pcd.PrecioPedido, pcd.ImportePedida, pcd.DescuentoPedida'
        );
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $retorno['productos'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>