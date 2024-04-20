<?php
    try{
        session_start();

        require_once 'geometria/SphericalUtil.php';
        require_once 'geometria/PolyUtil.php';
        require_once 'geometria/MathUtil.php';

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT latitud, longitud FROM clientes_posiciones WHERE clave = :clave");
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->execute();

        $clientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($clientes) > 0 ){

            $distancia = \GeometryLibrary\SphericalUtil::computeDistanceBetween( [ 'lat' => $_POST['lat'], 'lng' => $_POST['lon'] ], [ 'lat' => $clientes[0]['latitud'], 'lng' => $clientes[0]['longitud'] ] );
            if( $distancia > 20 ){
                $ors_calculada = polilinea_ors($_POST['lon'], $_POST['lat'], $decodedPoint['lng'], $decodedPoint['lat']);
                
                $resultado["distancia"] = $ors_calculada['features'][0]['properties']['segments'][0]['distance'];
                $resultado["tiempo"] = $ors_calculada['features'][0]['properties']['segments'][0]['duration'];
                $resultado["polilineas"] = $ors_calculada['features'][0]['geometry']['coordinates'];
                $resultado["color"] = "#6495ED";
            }else{
                $resultado["distancia"] = 0;
                $resultado["tiempo"] = 0;
                $resultado["polilineas"] = [ [$clientes[0]['longitud'], $clientes[0]['latitud']] , [$clientes[0]['longitud'], $clientes[0]['latitud']] ];
                $resultado["color"] = "#6495ED";
            }
        }else{
            $resultado["polilineas"][] = [$_POST['lon'],$_POST['lat']];
        }

        echo json_encode( $resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
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