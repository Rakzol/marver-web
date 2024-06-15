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
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("
            SELECT
            REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, EnvioPedidoCliente.Fecha) , ' ', EnvioPedidoCliente.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fecha,
            PedidosCliente.Tipocomprobante AS comprobante,
            PedidosCliente.FolioComprobante AS folio,
            Clientes.Clave AS cliente_clave,
            Clientes.Razon_Social AS cliente_nombre,
            EnvioPedidoCliente.Responsable AS vendedor,
            PedidosCliente.CodigosFacturado AS codigos,
            PedidosCliente.UnidadesFacturado AS piezas,
            PedidosCliente.TotalFacturado AS total,
            clientes_posiciones.latitud AS latitud,
			clientes_posiciones.longitud AS longitud,
			clientes_posiciones.numero_exterior AS numero_exterior,
			clientes_posiciones.numero_interior AS numero_interior,
			clientes_posiciones.observaciones AS observaciones,
			clientes_posiciones.calle AS calle,
            NULL feria
            FROM
            EnvioPedidoCliente
            INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido
            INNER JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            INNER JOIN Ventas ON Ventas.Folio = PedidosCliente.FolioComprobante AND Ventas.TipoComprobante = PedidosCliente.Tipocomprobante
            LEFT JOIN clientes_posiciones ON clientes_posiciones.clave = PedidosCliente.Cliente
            WHERE
            EnvioPedidoCliente.Fecha = CONVERT(DATE, GETDATE())
            AND Ventas.Status = 3
            AND Responsable = :vendedor

			UNION ALL

            SELECT
            REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, EnvioPedidoCliente.Fecha) , ' ', EnvioPedidoCliente.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fecha,
            PedidosCliente.Tipocomprobante AS comprobante,
            PedidosCliente.FolioComprobante AS folio,
            Clientes.Clave AS cliente_clave,
            Clientes.Razon_Social AS cliente_nombre,
            EnvioPedidoCliente.Responsable AS vendedor,
            PedidosCliente.CodigosFacturado AS codigos,
            PedidosCliente.UnidadesFacturado AS piezas,
            PedidosCliente.TotalFacturado AS total,
            clientes_posiciones.latitud AS latitud,
			clientes_posiciones.longitud AS longitud,
			clientes_posiciones.numero_exterior AS numero_exterior,
			clientes_posiciones.numero_interior AS numero_interior,
			clientes_posiciones.observaciones AS observaciones,
			clientes_posiciones.calle AS calle,
            NULL feria
            FROM
            EnvioPedidoCliente
            INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido
            INNER JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            INNER JOIN Ventas ON Ventas.Folio = PedidosCliente.FolioComprobante AND Ventas.TipoComprobante = PedidosCliente.Tipocomprobante
            LEFT JOIN clientes_posiciones ON clientes_posiciones.clave = PedidosCliente.Cliente
            WHERE
            EnvioPedidoCliente.Fecha = CONVERT(DATE, GETDATE())
            AND Ventas.Status = 3
            AND Responsable = :vendedor

            ORDER BY folio DESC
        ");
        //Fecha = CONVERT(DATE, DATEADD( DAY, -1, GETDATE() ) )
        $preparada->bindValue(':vendedor', $_POST['clave']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>