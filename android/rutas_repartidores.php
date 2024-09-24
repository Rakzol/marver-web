<?php
    try{
        require_once 'geometria/SphericalUtil.php';
        require_once 'geometria/PolyUtil.php';
        require_once 'geometria/MathUtil.php';

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('
            SELECT c.usuario, v.Nombre, c.latitud, c.longitud, c.velocidad
            FROM (
                SELECT usuario, latitud, longitud, velocidad,
                    ROW_NUMBER() OVER (PARTITION BY usuario ORDER BY fecha DESC) AS indice
                FROM posiciones
            ) c
            INNER JOIN Vendedores v ON v.Clave = c.usuario
            WHERE c.indice = 1
			ORDER BY v.Nombre;
        ');
        $preparada->execute();
        $resultado['repartidores'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if(isset($POST['repartidor'])){
        /*
            USE Mochis;
            GO

            SELECT * FROM rutas_repartidores WHERE id = 3000;

            SELECT
                REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, en.Fecha) , ' ', en.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fechaAsignacion,
                pr.folio AS pedido,
                pc.FolioComprobante AS folioComprobante,
                pc.Tipocomprobante AS tipoComprobante,
                pc.Observacion AS observacionesPedido,
                CASE
                    WHEN en.Extra2 = 'PENDIENTE' THEN NULL
                    WHEN en.Extra2 = 'EN RUTA' THEN mv.Importe * -1
                    ELSE mv.Feria + (mv.Importe * -1)
                END AS feria
            FROM pedidos_repartidores pr
            INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio
            INNER JOIN EnvioPedidoCliente en ON en.Extra1 = pr.id
            LEFT JOIN Clientes cl ON cl.Clave = pc.Cliente
            LEFT JOIN clientes_posiciones un ON un.clave = pc.Cliente
            LEFT JOIN ubicaciones_especiales ue ON ue.clave = pc.Cliente
            LEFT JOIN MoviemientosVenta mv ON mv.Folio = pc.FolioComprobante AND mv.TipoComprobante = 11 AND mv.Importe < 0
            WHERE pr.ruta_repartidor = 3000;
        */
        }
        
        $resultado["status"] = 0;
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "Error al calcular las rutas";
        echo json_encode($resultado);
    }

?>