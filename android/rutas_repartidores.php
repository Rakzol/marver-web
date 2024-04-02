<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

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

        if(!isset($_GET['repartidor'])){
            $_GET['repartidor'] = 0;
        }

        $preparada = $conexion->prepare('
            SELECT id, usuario, latitud, longitud, velocidad, fecha
            FROM (
                SELECT id, usuario, latitud, longitud, velocidad, fecha,
                    ROW_NUMBER() OVER (PARTITION BY usuario ORDER BY fecha DESC) AS indice
                FROM posiciones WHERE usuario != :repartidor
            ) AS posiciones
            WHERE indice = 1;
        ');
        $preparada->bindValue(':repartidor', $_GET['repartidor']);
        $preparada->execute();

        $repartidores_pasados = json_decode($_GET['repartidores'],true);
        print_r($repartidores_pasados);
        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){
            if(isset($repartidores_pasados[$repartidor['usuario']])){
                echo 'sipi ' . $repartidor['usuario'] . '<br>';
            }else{
                echo 'nope ' . $repartidor['usuario'] . '<br>';
            }
        }

        /*$preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_GET['repartidor']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($rutas_repartidores) == 0 ){

        }
        $ruta_repartidor = $rutas_repartidores[0];

        $preparada = $conexion->prepare("
            SELECT pr.folio, cp.latitud, cp.longitud FROM pedidos_repartidores pr
            INNER JOIN PedidosCliente pc ON pc.Folio = pr.folio 
            INNER JOIN clientes_posiciones cp ON cp.clave = pc.Cliente
            WHERE pr.ruta_repartidor = :ruta_repartidor ORDER BY pr.folio;
        ");
        $preparada->bindValue(':ruta_repartidor', $ruta_repartidor['id']);
        $preparada->execute();

        $pedidos_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedidos_repartidores) == 0 ){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "Ningun cliente tiene su ubicacion en el mapa";
            echo json_encode($resultado);
            exit();
        }

        $json_envio['origin'] = array(
            'location' => array(
                'latLng' => array(
                    'latitude' => 25.7942362,
                    'longitude' => -108.9858341
                )
            )
        );

        foreach($pedidos_repartidores as $pedido_repartidor){
            $intermediarios[] = array(
                'location' => array(
                    'latLng' => array(
                        'latitude' => $pedido_repartidor['latitud'],
                        'longitude' => $pedido_repartidor['longitud']
                    )
                )
            );
        }
        $json_envio['intermediates'] = $intermediarios;

        $json_envio['destination'] = array(
            'location' => array(
                'latLng' => array(
                    'latitude' => 25.7942362,
                    'longitude' => -108.9858341
                )
            )
        );

        $json_envio['optimizeWaypointOrder'] = true;

        $curl = curl_init('https://routes.googleapis.com/directions/v2:computeRoutes');
        $cabecera = array(
            'Content-Type: application/json',
            'X-Goog-Api-Key: AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo',
            'X-Goog-FieldMask: routes.duration,routes.distanceMeters,routes.legs.distanceMeters,routes.optimizedIntermediateWaypointIndex,routes.legs.duration,routes.legs.polyline.encodedPolyline,routes.legs.startLocation,routes.legs.endLocation'
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_envio));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecera);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $respuesta = curl_exec($curl);

        if ($respuesta == false) {
            $resultado["status"] = 5;
            $resultado["mensaje"] = "Error con google maps " . curl_error($curl);
            echo json_encode($resultado);
            exit();
        }

        curl_close($curl);

        $preparada = $conexion->prepare('UPDATE rutas_repartidores SET ruta = :ruta, fecha_inicio = GETDATE() WHERE id = :id');
        $preparada->bindValue(':ruta', $respuesta);
        $preparada->bindValue(':id', $ruta_repartidor['id']);
        $preparada->execute();

        $resultado["status"] = 0;
        $resultado["mensaje"] = "Ruta iniciada correctamente";
        echo $respuesta;*/
    }catch( Exception $exception ) {
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al calcular las rutas";
        echo json_encode($resultado);
    }

    function distancia($lat1, $lon1, $lat2, $lon2) {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $earthRadius = 6371000;
    
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1) * cos($lat2) * sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
    
        return $distance;
    }

?>