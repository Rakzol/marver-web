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

        $repartidor_seguido = json_decode($_GET['repartidor'],true);

        $preparada = $conexion->prepare('SELECT latitud, longitud FROM posiciones WHERE usuario = :repartidor ORDER BY fecha DESC;');
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

                $distancia = distancia($repartidor_pasado['lat'], $repartidor_pasado['lon'], $repartidor['latitud'], $repartidor['longitud']);
                if( $distancia > 30 ){

                    $resultado['repartidores'][] = array(
                        "repartidor" => $repartidor['usuario'],
                        "tipo" => "polilinea",
                        "polilinea" => polilinea_ors($repartidor_pasado['lon'], $repartidor_pasado['lat'], $repartidor['longitud'], $repartidor['latitud'])
                    );
                }else{
                    $coordenadas = polilinea_ors($repartidor['longitud'], $repartidor['latitud'], $repartidor['longitud'], $repartidor['latitud'])[0];

                    $resultado['repartidores'][] = array(
                        "repartidor" => $repartidor['usuario'],
                        "tipo" => "cercano",
                        "latitud" => $coordenadas[1],
                        "longitud" => $coordenadas[0]
                    );
                }
            }else{
                $coordenadas = polilinea_ors($repartidor['longitud'], $repartidor['latitud'], $repartidor['longitud'], $repartidor['latitud'])[0];

                $resultado['repartidores'][] = array(
                    "repartidor" => $repartidor['usuario'],
                    "tipo" => "nuevo",
                    "nombre" => $repartidor['Nombre'],
                    "latitud" => $coordenadas[1],
                    "longitud" => $coordenadas[0]
                );  
            }
        }

        //echo json_encode($resultado);

        $preparada = $conexion->prepare('SELECT TOP 1 id, ruta FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $repartidor_seguido['id']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($rutas_repartidores) == 0 ){

            if( count($posiciones_repartidor) > 0 ){
                $distancia = distancia($repartidor_seguido['lat'], $repartidor_seguido['lon'], $posiciones_repartidor[0]['latitud'], $posiciones_repartidor[0]['longitud']);
                if( $distancia > 30 ){
    
                    $resultado['repartidor'] = array(
                        "repartidor" => $repartidor_seguido['id'],
                        "tipo" => "polilinea",
                        "polilinea" => polilinea_ors($repartidor_seguido['lon'], $repartidor_seguido['lat'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'])
                    );
                }else{
                    $coordenadas = polilinea_ors($posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'], $posiciones_repartidor[0]['longitud'], $posiciones_repartidor[0]['latitud'])[0];
    
                    $resultado['repartidor'] = array(
                        "repartidor" => $repartidor_seguido['id'],
                        "tipo" => "cercano",
                        "latitud" => $coordenadas[1],
                        "longitud" => $coordenadas[0]
                    );
                }
            }

            echo json_encode($resultado);
            exit();
        }
        $ruta_repartidor = $rutas_repartidores[0];

        $resultado['ruta'] = json_decode($ruta_repartidor['ruta'],true);

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

        if( $resultado['ruta']['routes'][0]['optimizedIntermediateWaypointIndex'][0] == -1 ){
            if( $resultado['pedidos'][0]['status'] == 4 ){
                $leg = $resultado['ruta']['routes'][0]['legs'][0];
            }
        }else{
            $indice_leg = 0;
            foreach( $resultado['ruta']['routes'][0]['optimizedIntermediateWaypointIndex'] as $indice_pedido ){
                if( $resultado['pedidos'][$indice_pedido]['status'] == 4 ){
                    $leg = $resultado['ruta']['routes'][0]['legs'][$indice_leg];
                    break;
                }
                $indice_leg += 1;
            }
        }

        if(!isset($leg)){
            $leg = $resultado['ruta']['routes'][0]['legs'][count($resultado['ruta']['routes'][0]['legs'])-1];
        }

        $resultado['leg'] = $leg;

        //echo json_encode($resultado);

        echo normalizarEncodedPolyline($leg['polyline']['encodedPolyline']);
        foreach( decodePolyline(normalizarEncodedPolyline($leg['polyline']['encodedPolyline'])) as $point ){
            echo '[' . $point[1] . ',' . $point[0] . '],';
        }
        
    }catch( Exception $exception ) {
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al calcular las rutas";
        echo json_encode($resultado);
    }

    function normalizarEncodedPolyline($encodedPolyline){
        $retorno = '';
        foreach( str_split($encodedPolyline) as $caracter){
            $retorno .= $caracter;
        }
        return $retorno;
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

        return json_decode($respuesta,true)['features'][0]['geometry']['coordinates'];
    }

    function decodePolyline($encoded)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = array();
        $lat = 0;
        $lng = 0;
    
        while ($index < $length) {
            // Decode latitude
            $sum = 0;
            $shift = 0;
            do {
                $char = ord(substr($encoded, $index++)) - 63;
                $sum |= ($char & 0x1f) << $shift;
                $shift += 5;
            } while ($char >= 0x20);
            $dlat = (($sum & 1) ? ~($sum >> 1) : ($sum >> 1));
            $lat += $dlat;
    
            // Decode longitude
            $sum = 0;
            $shift = 0;
            do {
                $char = ord(substr($encoded, $index++)) - 63;
                $sum |= ($char & 0x1f) << $shift;
                $shift += 5;
            } while ($char >= 0x20);
            $dlng = (($sum & 1) ? ~($sum >> 1) : ($sum >> 1));
            $lng += $dlng;
    
            $points[] = array($lat * 1e-5, $lng * 1e-5);
        }
    
        return $points;
    }

?>