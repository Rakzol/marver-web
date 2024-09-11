<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        /* Verificamos que el vendedor exista */
        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("
            SELECT PedidosCliente.Folio, EnvioPedidoCliente.Responsable
            FROM PedidosCliente
            LEFT JOIN EnvioPedidoCliente
            ON EnvioPedidoCliente.Pedido = PedidosCliente.Folio AND ( EnvioPedidoCliente.Extra2 IS NULL OR EnvioPedidoCliente.Extra2 = 'E' )
            WHERE PedidosCliente.Folio = :folio
        ");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no existe";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        /*
            Si tiene una ruta iniciada, se recalcula y actualiza la ruta pata todos los pedidos y se finaliza, despues se crea una ruta nueva donde se asigna
            el pedido escaneado y todos los anteriroes donde el Extra2 de EnvioPedidoCliente sea NULL (Lo estaba entregando) y del Responsable de este repartidor
        */
        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        $rutas_iniciadas = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($rutas_iniciadas) > 0 ){
            $ruta_iniciada = $rutas_iniciadas[0]['id'];

            $preparada = $conexion->prepare("
                SELECT pr.id, pr.folio, cp.latitud, cp.longitud FROM pedidos_repartidores pr
                INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio 
                INNER JOIN clientes_posiciones cp ON cp.clave = pc.Cliente
                WHERE pr.ruta_repartidor = :ruta_repartidor ORDER BY pr.folio;
            ");
            $preparada->bindValue(':ruta_repartidor', $ruta_iniciada);
            $preparada->execute();
            $pedidos_repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC);

            $json_envio['origin'] = array(
                'location' => array(
                    'latLng' => array(
                        'latitude' => 25.7941814,
                        'longitude' => -108.9858957
                    )
                )
            );

            foreach($pedidos_repartidor as $pedido_repartidor){
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
                $resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            if (curl_errno($curl)) {
                $resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
                $resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }

            curl_close($curl);

            $rutas = json_decode( $respuesta, true);  
            if(!isset($rutas['routes'][0]['optimizedIntermediateWaypointIndex'])){
                $resultado["status"] = 2;
                $resultado["mensaje"] = "Error con las rustas optimizadas de google maps ";
                echo json_encode($resultado);
                exit();
            }

            $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_fin = GETDATE() WHERE id = :id');
            $preparada->bindValue(':ruta', $respuesta);
            $preparada->bindValue(':id', $ruta_iniciada);
            $preparada->execute();

            $preparada = $conexion->prepare('SELECT fecha_inicio FROM rutas_repartidores WHERE id = :id;');
            $preparada->bindValue(':id', $ruta_iniciada);
            $preparada->execute(); 

            $fecha = DateTime::createFromFormat('Y-m-d H:i:s.u', $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['fecha_inicio']);

            $indice_leg = 0;
            foreach( $rutas['routes'][0]['optimizedIntermediateWaypointIndex'] as $indice_pedido ){
    
                if( $indice_pedido == -1 ){
    
                    $segundos = intval( substr($rutas['routes'][0]['legs'][0]['duration'], 0, -1) );
                    $fecha->modify('+' . $segundos . ' seconds');

                    $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                    $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s'));
                    $preparada->bindValue(':id', $pedidos_repartidor[0]['id']);
                    $preparada->execute();
    
                }else{
    
                    $segundos = intval( substr($rutas['routes'][0]['legs'][$indice_leg]['duration'], 0, -1) ) + ( $indice_leg > 0 ? 180 : 0 );
                    $fecha->modify('+' . $segundos . ' seconds');

                    $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                    $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s'));
                    $preparada->bindValue(':id', $pedidos_repartidor[$indice_pedido]['id']);
                    $preparada->execute();

                    $indice_leg++;
                }
    
            }

            /*  Consultamos todos los envio EnvioPedidoCliente que tenga el Extra2 en NULL para ponerlo como T de que se transfirio a otra ruta para ese pedido
                y lo agregamos de nuevo con otra id en Extra1 despues de agregar el que sera su id en pedidos_repartidores :D */
            $preparada = $conexion->prepare("
                SELECT Pedido
                FROM EnvioPedidoCliente
                WHERE Responsable = :repartidor AND Extra2 IS NULL
            ");
            $preparada->bindValue(':repartidor', $_POST['clave']);
            $preparada->execute();
            $pedidos_pendientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

            if( count($pedidos_pendientes) > 0 ){

                $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor) VALUES (:repartidor)');
                $preparada->bindValue(':repartidor', $_POST['clave']);
                $preparada->execute();
                $nueva_ruta = $conexion->lastInsertId();

                foreach($pedidos_pendientes as $pedido_pendiente){
                    $preparada = $conexion->prepare("
                        UPDATE EnvioPedidoCliente
                        SET Extra2 = 'T'
                        WHERE Responsable = :repartidor AND Extra2 IS NULL AND Pedido = :pedido
                    ");
                    $preparada->bindValue(':repartidor', $_POST['clave']);
                    $preparada->bindValue(':pedido', $pedido_pendiente['Pedido']);
                    $preparada->execute();

                    $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores VALUES (:ruta_repartidor,:folio, NULL, NULL)');
                    $preparada->bindValue(':ruta_repartidor', $nueva_ruta);
                    $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
                    $preparada->execute();
                    $id_pedido_repartidor = $conexion->lastInsertId();

                    $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, Extra1) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), :id_pedido_repartidor )");
                    $preparada->bindValue(':folio', $pedido_pendiente['Pedido']);
                    $preparada->bindValue(':responsable', $_POST['clave']);
                    $preparada->bindValue(':id_pedido_repartidor', $id_pedido_repartidor);
                    $preparada->execute();
                }
            }
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if(count($rutas_repartidores) == 0){
            $preparada = $conexion->prepare('INSERT INTO rutas_repartidores (repartidor) VALUES (:repartidor)');
            $preparada->bindValue(':repartidor', $_POST['clave']);
            $preparada->execute();

            $id_ruta_reparto = $conexion->lastInsertId();
        }else{
            $id_ruta_reparto = $rutas_repartidores[0]['id'];
        }

        $preparada = $conexion->prepare('INSERT INTO pedidos_repartidores VALUES (:ruta_repartidor,:folio,NULL, NULL)');
        $preparada->bindValue(':ruta_repartidor', $id_ruta_reparto);
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();
        $id_pedido_nuevo = $conexion->lastInsertId();

        $preparada = $conexion->prepare("INSERT INTO EnvioPedidoCliente (Pedido, Responsable, Fecha, HoraEnvio, Extra1) VALUES (:folio, :responsable, FORMAT(GETDATE(), 'yyyy-MM-dd'), REPLACE( REPLACE( FORMAT(GETDATE(), 'hh:mm:ss tt'), 'PM', 'p. m.' ), 'AM', 'a. m.' ), :id_pedido_nuevo )");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':responsable', $_POST['clave']);
        $preparada->bindValue(':id_pedido_nuevo', $id_pedido_nuevo);
        $preparada->execute();

        $resultado["status"] = 0;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " se asigno correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 5;
        $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no es valido";
        echo json_encode($resultado);
    }
?>