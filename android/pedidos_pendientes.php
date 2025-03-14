<?php
    try{
        header('Content-Type: application/json');

        switch($_POST["sucursal"]){
            case "Mochis":
                $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
            case "Guasave":
                $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
        }
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
            EnvioPedidoCliente.Pedido AS pedido,
            EnvioPedidoCliente.Extra1 AS pedidoRepartidor,
            PedidosCliente.Observacion AS observacionesPedido,
            PedidosCliente.Tipocomprobante AS tipoComprobante,
            PedidosCliente.FolioComprobante AS folioComprobante,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN Clientes.Clave
                ELSE ce.clave
            END AS clienteClave,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN Clientes.Razon_Social
                ELSE ce.nombre
            END AS clienteNombre,
            EnvioPedidoCliente.Responsable AS repartidor,
            PedidosCliente.CodigosFacturado AS codigos,
            PedidosCliente.UnidadesFacturado AS piezas,
            PedidosCliente.TotalFacturado AS total,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.latitud
                ELSE ce.latitud
            END AS latitud,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.longitud
                ELSE ce.longitud
            END AS longitud,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.calle
                ELSE ce.calle
            END AS calle,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.colonia
                ELSE ce.colonia
            END AS colonia,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.codigo_postal
                ELSE ce.codigo_postal
            END AS codigoPostal,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.numero_exterior
                ELSE ce.numero_exterior
            END AS numeroExterior,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.numero_interior
                ELSE ce.numero_interior
            END AS numeroInterior,
            CASE WHEN PedidosCliente.Tipocomprobante != 3
                THEN cn.observaciones
                ELSE ce.observaciones
            END AS observacionesUbicacion,
			NULL AS feria
            FROM
            EnvioPedidoCliente
            INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido
            LEFT JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            LEFT JOIN clientes_posiciones cn
            ON cn.clave = PedidosCliente.Cliente
            LEFT JOIN ubicaciones_especiales ce
            ON ce.clave = PedidosCliente.Cliente
            WHERE
            EnvioPedidoCliente.Fecha = CONVERT(DATE, GETDATE()) AND EnvioPedidoCliente.Extra2 = 'PENDIENTE'
            AND EnvioPedidoCliente.Responsable = :repartidor
            ORDER BY EnvioPedidoCliente.Pedido DESC
        ");

        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>