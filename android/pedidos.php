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

        $preparada = $conexion->prepare("
            SELECT
            CONVERT(VARCHAR, EnvioPedidoCliente.Fecha) + ' ' + EnvioPedidoCliente.HoraEnvio AS fecha,
            CASE 
                WHEN PedidosCliente.Tipocomprobante = 1 THEN 'FACTURA'
                WHEN PedidosCliente.Tipocomprobante = 2 THEN 'RECIBO'
                WHEN PedidosCliente.Tipocomprobante = 3 THEN 'PREVENTA'
                ELSE 'SIN TIPO'
            END AS comprobante,
            PedidosCliente.FolioComprobante AS folio,
            CONVERT(VARCHAR, Clientes.Clave) + ' ' + Clientes.Razon_Social AS cliente,
            EnvioPedidoCliente.Responsable AS vendedor,
            PedidosCliente.CodigosFacturado AS codigos,
            PedidosCliente.UnidadesFacturado AS piezas,
            PedidosCliente.TotalFacturado AS total
            FROM
            EnvioPedidoCliente
            INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido
            INNER JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            WHERE
            Fecha = CONVERT(DATE, GETDATE())
            AND HoraLlegada IS NULL
            AND Responsable = :vendedor
            ORDER BY CONVERT(DATETIME, REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, Fecha) , ' ', HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) ) DESC;
        ");
        $preparada->bindValue(':vendedor', $_POST['usuario']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>