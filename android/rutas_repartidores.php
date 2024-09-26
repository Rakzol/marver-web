<?php
    try{
        require_once 'geometria/SphericalUtil.php';
        require_once 'geometria/PolyUtil.php';
        require_once 'geometria/MathUtil.php';

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        if( isset($_POST['web']) ){
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
        }

        if(isset($_POST['repartidor'])){

            $preparada = $conexion->prepare('
                SELECT TOP 1
                    id AS id,
                    fecha_actualizacion AS fechaActualizacion,
                    fecha_inicio AS fechaInicio,
                    fecha_llegada_estimada AS fechaLlegadaEstimada,
                    fecha_fin AS fechaFin,
                    fecha_llegada_eficiencia AS fechaLlegadaEficiencia,
                    polylinea_codificada AS polylineaCodificada,
                    segundos_estimados_sumatoria AS segundosEstimadosSumatoria,
                    metros_estimados_sumatoria AS metrosEstimadosSumatoria,
                    25.7941814 AS latitud,
                    -108.9858957 AS longitud
                FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL
                ORDER BY fecha_inicio DESC
            ');
            $preparada->bindValue(':repartidor', $_POST['repartidor']);
            $preparada->execute();
            $marver = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

            $actualizar = true;
            if( isset($_POST['id']) && isset($_POST['fechaActualizacion']) ){
                $actualizar = ($marver['id'] != $_POST['id'] || $marver['fechaActualizacion'] != $_POST['fechaActualizacion'] );
            }

            if($actualizar){
                $resultado['marver'] = $marver;

                $preparada = $conexion->prepare("
                    SELECT
                        pr.indice AS indice,
                        pr.polylinea_codificada AS polylineaCodificada,
                        pr.fecha_llegada_estimada AS fechaLlegadaEstimada,
                        pr.fecha_llegada AS fechaLlegada,
                        pr.fecha_llegada_eficiencia AS fechaLlegadaEficiencia,
                        en.Extra1 AS status,
                        REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, en.Fecha) , ' ', en.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fechaAsignacion,
                        pc.folio AS pedido,
                        pc.FolioComprobante AS folioComprobante,
                        pc.Tipocomprobante AS tipoComprobante,
                        pc.Observacion AS observacionesPedido,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN cl.Clave
                            ELSE ue.clave
                        END AS clienteClave,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN cl.Razon_Social
                            ELSE ue.nombre
                        END AS clienteNombre,
                        en.Responsable AS repartidor,
                        pc.CodigosFacturado AS codigos,
                        pc.UnidadesFacturado AS piezas,
                        pc.TotalFacturado AS total,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.latitud
                            ELSE ue.latitud
                        END AS latitud,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.longitud
                            ELSE ue.longitud
                        END AS longitud,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.calle
                            ELSE ue.calle
                        END AS calle,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.codigo_postal
                            ELSE ue.codigo_postal
                        END AS codigoPostal,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.numero_exterior
                            ELSE ue.numero_exterior
                        END AS numeroExterior,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.numero_interior
                            ELSE ue.numero_interior
                        END AS numeroInterior,
                        CASE WHEN pc.FolioComprobante > 0
                            THEN un.observaciones
                            ELSE ue.observaciones
                        END AS observacionesUbicacion,
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
                    WHERE pr.ruta_repartidor = :ruta_iniciada
                    ORDER BY pr.indice;
                ");
                $preparada->bindValue(':ruta_iniciada', $resultado['marver']['id']);
                $preparada->execute();
                $resultado['pedidos'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

            }
        }
        
        $resultado["status"] = 0;
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        $resultado["status"] = 1;
        $resultado["mensaje"] = "Error al calcular las rutas";
        echo json_encode($resultado);
    }

?>