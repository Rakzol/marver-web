<?php
    try{
        require_once 'geometria/SphericalUtil.php';
        require_once 'geometria/PolyUtil.php';
        require_once 'geometria/MathUtil.php';

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

        $repartidor_seguido = json_decode($_GET['repartidor'],true);

        $preparada = $conexion->prepare('SELECT latitud, longitud, velocidad FROM posiciones WHERE usuario = :repartidor ORDER BY fecha DESC;');
        $preparada->bindValue(':repartidor', $repartidor_seguido['id']);
        $preparada->execute();

        $posiciones_repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $preparada = $conexion->prepare('
            SELECT id, usuario, Nombre, latitud, longitud, velocidad, fecha
            FROM (
                SELECT id, usuario, Nombre, latitud, longitud, velocidad, fecha,
                    ROW_NUMBER() OVER (PARTITION BY usuario ORDER BY fecha DESC) AS indice
                FROM posiciones
                INNER JOIN Vendedores ON Vendedores.Clave = usuario
                WHERE usuario != :repartidor
            ) AS posiciones
            WHERE indice = 1;
        ');
        $preparada->bindValue(':repartidor', $repartidor_seguido['id']);
        $preparada->execute();

        $repartidores_pasados = json_decode($_GET['repartidores'],true);

        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){
            if(isset($repartidores_pasados[$repartidor['usuario']])){
                $repartidor_pasado = $repartidores_pasados[$repartidor['usuario']];

                $distancia = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => $repartidor_pasado['lat'], 'lng' => $repartidor_pasado['lon'] ], [ 'lat' => $repartidor['latitud'], 'lng' => $repartidor['longitud'] ]);
                if( $distancia > 20 ){

                    $resultado['repartidores'][] = array(
                        "id" => $repartidor['usuario'],
                        "nombre" => $repartidor['Nombre'],
                        "tipo" => "camino",
                        "color" => "#00000000",
                        "polilinea" => polilinea_ors($repartidor_pasado['lon'], $repartidor_pasado['lat'], $repartidor['longitud'], $repartidor['latitud'])['features'][0]['geometry']['coordinates']
                    );
                }else{
                    $coordenadas = polilinea_ors($repartidor['longitud'], $repartidor['latitud'], $repartidor['longitud'], $repartidor['latitud'])['features'][0]['geometry']['coordinates'][0];

                    $resultado['repartidores'][] = array(
                        "id" => $repartidor['usuario'],
                        "nombre" => $repartidor['Nombre'],
                        "tipo" => "cercano",
                        "color" => "#00000000",
                        "polilinea" => array(
                            array($repartidor_pasado['lon'], $repartidor_pasado['lat']),
                            array($coordenadas[0], $coordenadas[1])
                        )
                    );
                }
            }else{
                $coordenadas = polilinea_ors($repartidor['longitud'], $repartidor['latitud'], $repartidor['longitud'], $repartidor['latitud'])['features'][0]['geometry']['coordinates'][0];

                $resultado['repartidores'][] = array(
                    "id" => $repartidor['usuario'],
                    "nombre" => $repartidor['Nombre'],
                    "tipo" => "nuevo",
                    "color" => "#00000000",
                    "polilinea" => array(
                        array($coordenadas[0], $coordenadas[1]),
                        array($coordenadas[0], $coordenadas[1])
                    )
                );  
            }
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id, ruta FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $repartidor_seguido['id']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($rutas_repartidores) == 0 ){

            if( count($posiciones_repartidor) > 0 ){
                $distancia = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => $repartidor_seguido['lat'], 'lng' => $repartidor_seguido['lon'] ], [ 'lat' => $posiciones_repartidor[0]['latitud'], 'lng' => $posiciones_repartidor[0]['longitud'] ] );
                if( $distancia > 20 ){
    
                    $resultado['repartidor'] = array(
                        "id" => $repartidor_seguido['id'],
                        "nombre" => $repartidor_seguido['nombre'],
                        "tipo" => "camino",
                        "color" => "#00000000",
                        "polilinea" => polilinea_ors($repartidor_seguido['lon'], $repartidor_seguido['lat'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'])['features'][0]['geometry']['coordinates']
                    );
                }else{
                    $coordenadas = polilinea_ors($posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'])['features'][0]['geometry']['coordinates'][0];
    
                    $resultado['repartidor'] = array(
                        "id" => $repartidor_seguido['id'],
                        "nombre" => $repartidor_seguido['nombre'],
                        "tipo" => "cercano",
                        "color" => "#00000000",
                        "polilinea" => array(
                            array($repartidor_seguido['lon'], $repartidor_seguido['lat']),
                            array($coordenadas[0], $coordenadas[1])
                        )
                    );
                }
            }

            echo json_encode($resultado);
            exit();
        }
        $ruta_repartidor = $rutas_repartidores[0];

        $rutas = json_decode( str_replace('\\', '\\\\', $ruta_repartidor['ruta']), true);

        for( $c = 0; $c < count($rutas['routes'][0]['legs']); $c++ ){
            $decodesPolylines = \GeometryLibrary\PolyUtil::decode2($rutas['routes'][0]['legs'][$c]['polyline']['encodedPolyline']);
            $rutas['routes'][0]['legs'][$c]['color'] = '#000000';
            $rutas['routes'][0]['legs'][$c]['polyline']['decodedPolyline'] = $decodesPolylines[0];
            $rutas['routes'][0]['legs'][$c]['polyline']['polilinea'] = $decodesPolylines[1];
        }

        $resultado['ruta'] = $rutas['routes'][0];

        $preparada = $conexion->prepare("
            SELECT
            pedidos_repartidores.folio AS pedido,
            REPLACE( REPLACE( CONCAT( CONVERT(VARCHAR, EnvioPedidoCliente.Fecha) , ' ', EnvioPedidoCliente.HoraEnvio ), 'p. m.', 'PM' ), 'a. m.', 'AM' ) AS fecha,
            PedidosCliente.Tipocomprobante AS comprobante,
            PedidosCliente.FolioComprobante AS folio,
            Ventas.Status AS status,
            Clientes.Clave AS cliente_clave,
            Clientes.Razon_Social AS cliente_nombre,
            PedidosCliente.CodigosFacturado AS codigos,
            PedidosCliente.UnidadesFacturado AS piezas,
            PedidosCliente.TotalFacturado AS total,
            clientes_posiciones.latitud AS latitud,
            clientes_posiciones.longitud AS longitud,
            clientes_posiciones.numero_exterior AS numero_exterior,
            clientes_posiciones.numero_interior AS numero_interior,
            clientes_posiciones.observaciones AS observaciones,
            clientes_posiciones.calle AS calle,
            MoviemientosVenta.Importe * -1 AS feria
            FROM
            pedidos_repartidores
            INNER JOIN EnvioPedidoCliente ON EnvioPedidoCliente.Pedido = pedidos_repartidores.folio
            INNER JOIN PedidosCliente ON PedidosCliente.Folio = pedidos_repartidores.folio
            INNER JOIN Clientes ON Clientes.Clave = PedidosCliente.Cliente
            INNER JOIN Ventas ON Ventas.Folio = PedidosCliente.FolioComprobante AND Ventas.TipoComprobante = PedidosCliente.Tipocomprobante
            INNER JOIN clientes_posiciones ON clientes_posiciones.clave = PedidosCliente.Cliente
            LEFT JOIN MoviemientosVenta ON MoviemientosVenta.Folio = PedidosCliente.FolioComprobante AND MoviemientosVenta.TipoComprobante = 11 AND MoviemientosVenta.Importe < 0
            WHERE
            pedidos_repartidores.ruta_repartidor = :ruta_repartidor
            ORDER BY pedidos_repartidores.folio
        ");
        $preparada->bindValue(':ruta_repartidor', $ruta_repartidor['id']);
        $preparada->execute();

        $pedidos_repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $resultado['pedidos'] = $pedidos_repartidor;

        if( $resultado['ruta']['optimizedIntermediateWaypointIndex'][0] == -1 ){
            if( $resultado['pedidos'][0]['status'] == 4 ){
                $resultado['ruta']['legs'][0]['color'] = "#6495ED";
                $leg = $resultado['ruta']['legs'][0];
            }
        }else{
            $indice_leg = 0;
            foreach( $resultado['ruta']['optimizedIntermediateWaypointIndex'] as $indice_pedido ){
                if( $resultado['pedidos'][$indice_pedido]['status'] == 4 ){
                    $resultado['ruta']['legs'][$indice_leg]['color'] = "#6495ED";
                    $leg = $resultado['ruta']['legs'][$indice_leg];
                    break;
                }
                $indice_leg += 1;
            }
        }

        if(!isset($leg)){
            $resultado['ruta']['legs'][count($resultado['ruta']['legs'])-1]['color'] = "#6495ED";
            $leg = $resultado['ruta']['legs'][count($resultado['ruta']['legs'])-1];
        }

        $resultado['leg'] = $leg;

        $distancia = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => $repartidor_seguido['lat'], 'lng' => $repartidor_seguido['lon'] ], [ 'lat' => $leg['endLocation']['latLng']['latitude'], 'lng' => $leg['endLocation']['latLng']['longitude'] ] );
        if( $distancia > 20 ){

            $distancia = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => $repartidor_seguido['lat'], 'lng' => $repartidor_seguido['lon'] ], [ 'lat' => $posiciones_repartidor[0]['latitud'], 'lng' => $posiciones_repartidor[0]['longitud'] ] );
            if( $distancia > 20 ){
    
                $resultado['repartidor'] = array(
                    "id" => $repartidor_seguido['id'],
                    "nombre" => $repartidor_seguido['nombre'],
                    "tipo" => "camino",
                    "polilinea" => polilinea_ors($repartidor_seguido['lon'], $repartidor_seguido['lat'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'] )['features'][0]['geometry']['coordinates']
                );
            }else{
                $coordenadas = polilinea_ors($posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'])['features'][0]['geometry']['coordinates'][0];
    
                $resultado['repartidor'] = array(
                    "id" => $repartidor_seguido['id'],
                    "nombre" => $repartidor_seguido['nombre'],
                    "tipo" => "cercano",
                    "polilinea" => array(
                        array($repartidor_seguido['lon'], $repartidor_seguido['lat']),
                        array($coordenadas[0], $coordenadas[1])
                    )
                );
            }

            $menor_distancia = INF;
            if ( ! \GeometryLibrary\PolyUtil::isLocationOnPath(
                ['lat' => $posiciones_repartidor[0]['latitud'], 'lng' => $posiciones_repartidor[0]['longitud']],
                $leg['polyline']['decodedPolyline'],
                20
            )){
                foreach( $leg['polyline']['decodedPolyline'] as $decodedPoint ){
                    $ors_calculada = polilinea_ors($posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'], $decodedPoint['lng'], $decodedPoint['lat']);
    
                    if( $ors_calculada['features'][0]['properties']['segments'][0]['distance'] < $menor_distancia ){
                        $menor_distancia = $ors_calculada['features'][0]['properties']['segments'][0]['distance'];
                        $resultado['incorporacion'] = $ors_calculada['features'][0]['geometry']['coordinates'];
                    }
                }
            }

        }else{
            $coordenadas = polilinea_ors($leg['endLocation']['latLng']['longitude'], $leg['endLocation']['latLng']['latitude'], $leg['endLocation']['latLng']['longitude'], $leg['endLocation']['latLng']['latitude'])['features'][0]['geometry']['coordinates'][0];

            $resultado['repartidor'] = array(
                "id" => $repartidor_seguido['id'],
                "nombre" => $repartidor_seguido['nombre'],
                "tipo" => "llego",
                "polilinea" => array(
                    array($repartidor_seguido['lon'], $repartidor_seguido['lat']),
                    array($coordenadas[0], $coordenadas[1])
                )
            );
        }
        
        for( $c = 0; $c < count($resultado['ruta']['legs']); $c++ ){
            unset($resultado['ruta']['legs'][$c]['polyline']['decodedPolyline']);
        }

        echo json_encode($resultado);
        
    }catch( Exception $exception ) {
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al calcular las rutas";
        echo json_encode($resultado);
    }

    function polilinea_ors($lon1, $lat1, $lon2, $lat2){
        $url = 'http://10.10.10.130:8082/ors/v2/directions/driving-car';

        $curl = curl_init();

        $parametros = array(
            'api_key' => '5b3ce3597851110001cf6248199545457ba045d184173db169aebd0c',
            'start' => $lon1 . ',' . $lat1,
            'end' => $lon2 . ',' . $lat2
        );

        $cabecera = array(
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8'
        );

        curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($parametros));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecera);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $respuesta = curl_exec($curl);

        if ($respuesta == false) {
            $resultado["status"] = 5;
            $resultado["mensaje"] = "Error con ors " . curl_error($curl);
            echo json_encode($resultado);
            exit();
        }

        curl_close($curl);

        return json_decode($respuesta,true);
    }

?>