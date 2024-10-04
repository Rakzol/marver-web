<?php
try {
    require_once 'geometria/SphericalUtil.php';
    require_once 'geometria/PolyUtil.php';
    require_once 'geometria/MathUtil.php';

    header('Content-Type: application/json');

    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true', 'MARITE', '2505M$RITE');
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
    $preparada->bindValue(':clave', $_POST['u']);
    $preparada->bindValue(':contrasena', $_POST['c']);
    $preparada->execute();

    $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) == 0) {
        /*$resultado["status"] = 1;
        echo json_encode($resultado);*/
        exit();
    }

    $preparada = $conexion->prepare('INSERT INTO posiciones VALUES( :usuario, :latitud, :longitud, :velocidad, GETDATE() )');
    $preparada->bindValue(':usuario', $_POST['u']);
    $preparada->bindValue(':latitud', $_POST['la']);
    $preparada->bindValue(':longitud', $_POST['ln']);
    $preparada->bindValue(':velocidad', $_POST['v']);
    $preparada->execute();

    $distancia_marver = \GeometryLibrary\SphericalUtil::computeDistanceBetween(['lat' => 25.794285, 'lng' => -108.985924], ['lat' => $_POST['la'], 'lng' => $_POST['ln']]);
    /* Verificamos si esta en el perimetro de marver para finalizar la ruta, siempre que tenga todo en status valido para la entrega */
    if ($distancia_marver <= 15) {
        /* Verificamos que no tenga pedidos sin llegar a una resolicion de entrega */
        $preparada = $conexion->prepare("
            SELECT Responsable
            FROM EnvioPedidoCliente
            WHERE Responsable = :repartidor AND Extra2 IN ( 'PENDIENTE', 'EN RUTA' )
        ");
        $preparada->bindValue(':repartidor', $_POST['u']);
        $preparada->execute();
        $pedidos_pendientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

        /* Si ya todo se entrego de alguna manera se procedera a finalizar la ruta actual */
        if (count($pedidos_pendientes) == 0) {
            $preparada = $conexion->prepare('UPDATE rutas_repartidores SET fecha_fin = GETDATE(), fecha_actualizacion = GETDATE(), fecha_llegada_eficiencia = DATEDIFF(SECOND, fecha_llegada_estimada, GETDATE()) WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
            $preparada->bindValue(':repartidor', $_POST['u']);
            $preparada->execute();
        }
    } else if ($distancia_marver >= 150) {
        /* INICAIR RUTA */
        header('HTTP/1.1 1');
        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
        $preparada->bindValue(':repartidor', $_POST['c']);
        $preparada->execute();
        header('HTTP/1.1 11');

        $rutas_iniciables = $preparada->fetchAll(PDO::FETCH_ASSOC);
        header('HTTP/1.1 11'.count($rutas_iniciables));
        if( count($rutas_iniciables) > 0 ){
            header('HTTP/1.1 2');
            $ruta_iniciable = $rutas_iniciables[0]['id'];

            $preparada = $conexion->prepare("
                SELECT
                pr.id,
                CASE WHEN pc.Tipocomprobante != 3
                    THEN cn.latitud
                    ELSE ce.latitud
                END AS Latitud,
                CASE WHEN pc.Tipocomprobante != 3
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

            $json_envio['travelMode'] = "TWO_WHEELER";
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
                /*$resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);*/
                header('HTTP/1.1 3');
                exit();
            }
            if ($respuesta == false) {
                /*$resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);*/
                header('HTTP/1.1 4');
                exit();
            }
            if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
                /*$resultado["status"] = 2;
                $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
                echo json_encode($resultado);*/
                header('HTTP/1.1 5');
                exit();
            }

            curl_close($curl);

            $rutas = json_decode( $respuesta, true);  
            if(!isset($rutas['routes'][0]['optimizedIntermediateWaypointIndex'])){
                /*$resultado["status"] = 2;
                $resultado["mensaje"] = "Error con las rustas optimizadas de google maps ";
                echo json_encode($resultado);*/
                header('HTTP/1.1 6');
                exit();
            }

            $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_inicio = GETDATE(), fecha_actualizacion = GETDATE() WHERE id = :id;');
            $preparada->bindValue(':ruta', $respuesta);
            $preparada->bindValue(':id', $ruta_iniciable);
            $preparada->execute();
            
            $preparada = $conexion->prepare('SELECT fecha_inicio FROM rutas_repartidores WHERE id = :id;');
            $preparada->bindValue(':id', $ruta_iniciable);
            $preparada->execute(); 
            $fecha_inicio = $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['fecha_inicio'];

            $indice_leg = -1;
            $segundos_estimados_sumatoria = 0;
            $metros_estimados_sumatoria = 0;
            foreach( $rutas['routes'][0]['optimizedIntermediateWaypointIndex'] as $indice_pedido ){

                $indice_leg++;
                if( $indice_pedido == -1 ){
                    $indice_pedido = 0;
                }

                $segundos_estimados = intval( substr($rutas['routes'][0]['legs'][$indice_leg]['duration'], 0, -1) ) + ( $indice_leg > 0 ? 180 : 0 );
                $segundos_estimados_sumatoria += $segundos_estimados;
                $fecha_llegada_estimada = DateTime::createFromFormat('Y-m-d H:i:s.u', $fecha_inicio);
                $fecha_llegada_estimada->modify('+' . $segundos_estimados_sumatoria . ' seconds');
                $metros_estimados = $rutas['routes'][0]['legs'][$indice_leg]['distanceMeters'];
                $metros_estimados_sumatoria += $metros_estimados;

                $preparada = $conexion->prepare('
                    UPDATE pedidos_repartidores SET
                    indice = :indice,
                    fecha_llegada_estimada = :fecha_llegada_estimada,
                    segundos_estimados = :segundos_estimados,
                    segundos_estimados_sumatoria = :segundos_estimados_sumatoria,
                    metros_estimados = :metros_estimados,
                    metros_estimados_sumatoria = :metros_estimados_sumatoria,
                    polylinea_codificada = :polylinea_codificada
                    WHERE id = :id');
                $preparada->bindValue(':indice', $indice_leg);
                $preparada->bindValue(':fecha_llegada_estimada', $fecha_llegada_estimada->format('Y-m-d H:i:s'));
                $preparada->bindValue(':segundos_estimados', $segundos_estimados);
                $preparada->bindValue(':segundos_estimados_sumatoria', $segundos_estimados_sumatoria);
                $preparada->bindValue(':metros_estimados', $metros_estimados);
                $preparada->bindValue(':metros_estimados_sumatoria', $metros_estimados_sumatoria);
                $preparada->bindValue(':polylinea_codificada', $rutas['routes'][0]['legs'][$indice_leg]['polyline']['encodedPolyline']);
                $preparada->bindValue(':id', $pedidos_repartidor[$indice_pedido]['id']);
                $preparada->execute();
    
            }

            $indice_leg++;

            $segundos_estimados = intval( substr($rutas['routes'][0]['legs'][$indice_leg]['duration'], 0, -1) ) + ( $indice_leg > 0 ? 180 : 0 );
            $segundos_estimados_sumatoria += $segundos_estimados;
            $fecha_llegada_estimada = DateTime::createFromFormat('Y-m-d H:i:s.u', $fecha_inicio);
            $fecha_llegada_estimada->modify('+' . $segundos_estimados_sumatoria . ' seconds');
            $metros_estimados = $rutas['routes'][0]['legs'][$indice_leg]['distanceMeters'];
            $metros_estimados_sumatoria += $metros_estimados;

            $preparada = $conexion->prepare('
                UPDATE rutas_repartidores SET
                fecha_llegada_estimada = :fecha_llegada_estimada,
                segundos_estimados = :segundos_estimados,
                segundos_estimados_sumatoria = :segundos_estimados_sumatoria,
                metros_estimados = :metros_estimados,
                metros_estimados_sumatoria = :metros_estimados_sumatoria,
                polylinea_codificada = :polylinea_codificada
                WHERE id = :id;');
            $preparada->bindValue(':fecha_llegada_estimada', $fecha_llegada_estimada->format('Y-m-d H:i:s'));
            $preparada->bindValue(':segundos_estimados', $segundos_estimados);
            $preparada->bindValue(':segundos_estimados_sumatoria', $segundos_estimados_sumatoria);
            $preparada->bindValue(':metros_estimados', $metros_estimados);
            $preparada->bindValue(':metros_estimados_sumatoria', $metros_estimados_sumatoria);
            $preparada->bindValue(':polylinea_codificada', $rutas['routes'][0]['legs'][$indice_leg]['polyline']['encodedPolyline']);
            $preparada->bindValue(':id', $ruta_iniciable);
            $preparada->execute();

            /* Colocar la llegada estimada */

            header('HTTP/1.1 7');
        }
        /* INICIAR RUTA */
    }
} catch (Exception $exception) {
    //header('HTTP/1.1 500 ' . $exception->getMessage());
}
