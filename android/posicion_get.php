<?php
    try{
        require_once 'geometria/SphericalUtil.php';
        require_once 'geometria/PolyUtil.php';
        require_once 'geometria/MathUtil.php';

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_GET['u']);
        $preparada->bindValue(':contrasena', $_GET['c']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('INSERT INTO posiciones VALUES( :usuario, :latitud, :longitud, :velocidad, GETDATE() )');
        $preparada->bindValue(':usuario', $_GET['u']);
        $preparada->bindValue(':latitud', $_GET['la']);
        $preparada->bindValue(':longitud', $_GET['ln']);
        $preparada->bindValue(':velocidad', $_GET['v']);
        $preparada->execute();

        $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => 25.794285, 'lng' => -108.985924 ], [ 'lat' => $_GET['la'], 'lng' => $_GET['ln'] ] );
        /* Verificamos si esta en el perimetro de marver para finalizar la ruta, siempre que tenga todo en status valido para la entrega */
        if( $distancia_marver <= 15 ){
            /* Verificamos que tenga alguna ruta sin finalizar */
            $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
            $preparada->bindValue(':repartidor', $_GET['u']);
            $preparada->execute();

            $rutas_iniciadas = $preparada->fetchAll(PDO::FETCH_ASSOC);
            if( count($rutas_iniciadas) > 0 ){
                $ruta_iniciada = $rutas_iniciadas[0]['id'];
    
                /* Consultamos todos los pedios que no se han terminado de alguna manera en la ruta actual */
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
    
                /* Si ya todo se entrego de alguna manera se procedera a finalizar la ruta actual */
                if( count($pedidos_pendientes) == 0 ){
    
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
                    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
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

                    $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_fin = GETDATE() WHERE id = :id;');
                    $preparada->bindValue(':ruta', $respuesta);
                    $preparada->bindValue(':id', $ruta_iniciada);
                    $preparada->execute();

                    $preparada = $conexion->prepare('SELECT fecha_inicio FROM rutas_repartidores WHERE id = :id;');
                    $preparada->bindValue(':id', $ruta_iniciada);
                    $preparada->execute();    

                    $fecha = DateTime::createFromFormat('Y-m-d H:i:s.u', $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['fecha']);

                    $indice_leg = 0;
                    foreach( $rutas['routes'][0]['optimizedIntermediateWaypointIndex'] as $indice_pedido ){
            
                        if( $indice_pedido == -1 ){
            
                            $segundos = intval( substr($rutas['routes'][0]['legs'][0]['duration'], 0, -1) );
                            $fecha->modify('+' . $segundos . ' seconds');
        
                            $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                            $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s.u'));
                            $preparada->bindValue(':id', $pedidos_repartidor[0]['id']);
                            $preparada->execute();
            
                        }else{
            
                            $segundos = intval( substr($rutas['routes'][0]['legs'][$indice_leg]['duration'], 0, -1) );
                            $fecha->modify('+' . $segundos . ' seconds');
        
                            $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                            $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s.u'));
                            $preparada->bindValue(':id', $pedidos_repartidor[$indice_pedido]['id']);
                            $preparada->execute();
        
                            $indice_leg++;
                        }
            
                    }
                }

            }

        }
        /* Verificamos si esta fuera de marver el repartidor para iniciarle la ruta si no la tiene iniciada */
        else if( $distancia_marver >= 150 ){

            $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL AND fecha_fin IS NULL');
            $preparada->bindValue(':repartidor', $_GET['u']);
            $preparada->execute();
    
            $rutas_iniciadas = $preparada->fetchAll(PDO::FETCH_ASSOC);
            if( count($rutas_iniciadas) > 0 ){
                $ruta_reparto = $rutas_iniciadas[0]['id'];
    
                $preparada = $conexion->prepare("
                    SELECT pr.id, pr.folio, cp.latitud, cp.longitud FROM pedidos_repartidores pr
                    INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio 
                    INNER JOIN clientes_posiciones cp ON cp.clave = pc.Cliente
                    WHERE pr.ruta_repartidor = :ruta_repartidor ORDER BY pr.folio;
                ");
                $preparada->bindValue(':ruta_repartidor', $ruta_reparto);
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
                if( count($intermediarios) > 0 ){
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
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
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
                $preparada->bindValue(':id', $ruta_reparto);
                $preparada->execute();
    
                $preparada = $conexion->prepare('SELECT fecha_inicio FROM rutas_repartidores WHERE id = :id;');
                $preparada->bindValue(':id', $ruta_reparto);
                $preparada->execute();

                $fecha = DateTime::createFromFormat('Y-m-d H:i:s.u', $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['fecha']);

                $indice_leg = 0;
                foreach( $rutas['routes'][0]['optimizedIntermediateWaypointIndex'] as $indice_pedido ){
        
                    if( $indice_pedido == -1 ){
        
                        $segundos = intval( substr($rutas['routes'][0]['legs'][0]['duration'], 0, -1) );
                        $fecha->modify('+' . $segundos . ' seconds');
    
                        $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                        $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s.u'));
                        $preparada->bindValue(':id', $pedidos_repartidor[0]['id']);
                        $preparada->execute();
        
                    }else{
        
                        $segundos = intval( substr($rutas['routes'][0]['legs'][$indice_leg]['duration'], 0, -1) );
                        $fecha->modify('+' . $segundos . ' seconds');
    
                        $preparada = $conexion->prepare('UPDATE pedidos_repartidores SET llegada_estimada = :llegada_estimada WHERE id = :id');
                        $preparada->bindValue(':llegada_estimada', $fecha->format('Y-m-d H:i:s.u'));
                        $preparada->bindValue(':id', $pedidos_repartidor[$indice_pedido]['id']);
                        $preparada->execute();
    
                        $indice_leg++;
                    }
        
                }

            }

        }

    }catch( Exception $exception ) {
        echo $exception->getMessage();
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>