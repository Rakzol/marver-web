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
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        $rutas_iniciables = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($rutas_iniciables) > 0 ){
            $ruta_iniciable = $rutas_iniciables[0]['id'];

            $preparada = $conexion->prepare("
                SELECT
                pr.id,
                CASE WHEN pc.FolioComprobante > 0
                    THEN cn.latitud
                    ELSE ce.latitud
                END AS Latitud,
                CASE WHEN pc.FolioComprobante > 0
                    THEN cn.longitud
                    ELSE ce.longitud
                END AS Longitud
                FROM pedidos_repartidores pr
                INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio 
                LEFT JOIN clientes_posiciones cn
                ON cn.clave = pc.Cliente
                LEFT JOIN ubicaciones_especiales ce
                ON ce.clave = pc.Cliente
                WHERE pr.ruta_repartidor = :ruta_repartidor ORDER BY pr.folio;
            ");
            $preparada->bindValue(':ruta_repartidor', $ruta_iniciable);
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
                if( $pedido_repartidor['Latitud'] != 0 && $pedido_repartidor['Longitud'] != 0 ){
                    $intermediarios[] = array(
                        'location' => array(
                            'latLng' => array(
                                'latitude' => $pedido_repartidor['Latitud'],
                                'longitude' => $pedido_repartidor['Longitud']
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

            /* Coloar la llegada estimada */

            if (curl_errno($curl)) {
                $resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            if ($respuesta == false) {
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

            $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_inicio = GETDATE() WHERE id = :id;');
            $preparada->bindValue(':ruta', $respuesta);
            $preparada->bindValue(':id', $ruta_iniciable);
            $preparada->execute();
            
            $preparada = $conexion->prepare('SELECT fecha_inicio FROM rutas_repartidores WHERE id = :id;');
            $preparada->bindValue(':id', $ruta_iniciable);
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
            /* Colocar la llegada estimada */

        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = "Ruta iniciada correctamente";
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al inicializar la ruta";
        echo json_encode($resultado);
    }
?>