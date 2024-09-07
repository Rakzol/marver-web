<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        /* Verificamos que el vendedor exista */
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

        $preparada = $conexion->prepare("SELECT PedidosCliente.Folio, EnvioPedidoCliente.Responsable FROM PedidosCliente LEFT JOIN EnvioPedidoCliente ON EnvioPedidoCliente.Pedido = PedidosCliente.Folio WHERE PedidosCliente.Folio = :folio");
        $preparada->bindValue(':folio', $_GET['folio']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " no existe";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != NULL ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_GET['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        /*
            Si tiene una ruta iniciada, se recalcula y actualiza la ruta pata todos los pedidos y se finaliza, despues se crea una ruta nueva donde se asigna
            el pedido escaneado y todos los anteriroes donde el status de venta y preventa sea diferente de 2, 5 y 18
        */
        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_GET['clave']);
        $preparada->execute();

        $rutas_iniciadas = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($rutas_iniciadas) > 0 ){
            $ruta_iniciada = $rutas_iniciadas[0]['id'];

            $preparada = $conexion->prepare("
                SELECT pr.folio, cp.latitud, cp.longitud FROM pedidos_repartidores pr
                INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio 
                INNER JOIN clientes_posiciones cp ON cp.clave = pc.Cliente
                WHERE pr.ruta_repartidor = :ruta_repartidor ORDER BY pr.folio;
            ");
            $preparada->bindValue(':ruta_repartidor', $ruta_iniciada);
            $preparada->execute();
            $pedidos_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);

            $json_envio['origin'] = array(
                'location' => array(
                    'latLng' => array(
                        'latitude' => 25.7941814,
                        'longitude' => -108.9858957
                    )
                )
            );

            foreach($pedidos_repartidores as $pedido_repartidor){
                if( $pedido_repartidor['latitud'] != 0 && $pedido_repartidor['longitud'] != 0 ){
                    $intermediarios[] = array(
                        'location' => array(
                            'latLng' => array(
                                'latitude' => $pedido_repartidor['latitud'],
                                'longitude' => $pedido_repartidor['longitud']
                            )
                        )
                    );
                }
            }
            if( isset($intermediarios) ){
                $json_envio['intermediates'] = $intermediarios;
            }

            $json_envio['destination'] = array(
                'location' => array(
                    'latLng' => array(
                        'latitude' => 25.7941814,
                        'longitude' => -108.9858957
                    )
                )
            );

            $json_envio['routingPreference'] = "TRAFFIC_AWARE";
            $json_envio['optimizeWaypointOrder'] = true;

            $curl = curl_init('https://routes.googleapis.com/directions/v2:computeRoutes');
            $cabecera = array(
                'Content-Type: application/json',
                'X-Goog-Api-Key: AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo',
                'X-Goog-FieldMask: routes.legs.duration,routes.legs.distanceMeters,routes.optimizedIntermediateWaypointIndex,routes.legs.polyline.encodedPolyline'
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_envio));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecera);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $respuesta = curl_exec($curl);

            if ($respuesta == false) {
                $resultado["status"] = 4;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }

            curl_close($curl);

            $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_fin = GETDATE() WHERE id = :id');
            $preparada->bindValue(':ruta', $respuesta);
            $preparada->bindValue(':id', $ruta_iniciada);
            $preparada->execute();

            /* Consultamos todos los pedidos de ventas y preventas donde el status no sea 2, 5 y 18 de los pedidos de la ruta que acabamos de finalizar */
            $preparada = $conexion->prepare("
                SELECT pr.folio FROM pedidos_repartidores pr
                INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio
                INNER JOIN Ventas ve ON ve.Folio = pc.FolioComprobante AND ve.TipoComprobante = pc.Tipocomprobante
                WHERE pr.ruta_repartidor = :ruta_repartidor_1 AND ve.Status NOT IN (2, 5, 18)
                UNION ALL
                SELECT pr.folio FROM pedidos_repartidores pr
                INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio
                INNER JOIN Preventa ve ON ve.Folio = pc.FolioComprobante AND ve.TipoComprobante = pc.Tipocomprobante
                WHERE pr.ruta_repartidor = :ruta_repartidor_2 AND ve.Status NOT IN (2, 5, 18)
                ORDER BY pr.folio;
            ");
            $preparada->bindValue(':ruta_repartidor_1', $ruta_iniciada);
            $preparada->bindValue(':ruta_repartidor_2', $ruta_iniciada);
            $preparada->execute();
            $pedidos_pendientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

            if( count($pedidos_pendientes) > 0 ){

                $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor) VALUES (:repartidor)');
                $preparada->bindValue(':repartidor', $_GET['clave']);
                $preparada->execute();
                $nueva_ruta = $conexion->lastInsertId();

                foreach($pedidos_pendientes as $pedido_pendiente){
                    $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores VALUES (:ruta_repartidor,:folio, NULL)');
                    $preparada->bindValue(':ruta_repartidor', $nueva_ruta);
                    $preparada->bindValue(':folio', $pedido_pendiente['folio']);
                    $preparada->execute();
                }
            }

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

        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ) )");
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