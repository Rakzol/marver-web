<?php
    try{

        switch( $_GET['sucursal'] ){
            case 'mochis':
                $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
            break;
            case 'guasave':
                $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
            break;
            case 'higuera':
                $conexion = new PDO('sqlsrv:Server=11.11.11.52;Database=Higuera;TrustServerCertificate=true','MARITE','2505M$RITE');
            break;
        }        

        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT Producto.* FROM PedidoDetalle INNER JOIN Producto ON Producto.Codigo = PedidoDetalle.Codigo WHERE Folio = :folio ORDER BY Codigo ASC");
        $preparada->bindValue(":folio", $_GET["folio"]);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>