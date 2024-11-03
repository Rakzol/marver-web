<?php
    try{
        //velocidades que las velocidad sea mayor a la de correr 6.5
        //puede existir uan diferencia entre velocidades de 30 segundos para seguira contando

        session_start();

        header('Content-Type: application/json');

        if(!isset($_SESSION['usuario_mapa'])){
            echo '[]';
            exit();
        }

        function distancia($lat1, $lon1, $lat2, $lon2) {
            // Radio de la Tierra en kilómetros
            $earthRadius = 6371;
        
            // Convierte las coordenadas de grados a radianes
            $lat1 = deg2rad($lat1);
            $lon1 = deg2rad($lon1);
            $lat2 = deg2rad($lat2);
            $lon2 = deg2rad($lon2);
        
            // Diferencias de coordenadas
            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
        
            // Fórmula de Haversine
            $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
            // Distancia en kilómetros
            $distance = $earthRadius * $c;
        
            return $distance;
        }

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', $_POST['fecha']);
        $preparada->bindValue(':dia_final', $_POST['fecha']);
        $preparada->execute();

        $resultados = [];
        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){

            $preparada = $conexion->prepare("SELECT id, latitud, longitud, velocidad, fecha FROM posiciones WHERE usuario = :repartidor AND fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final)");
            $preparada->bindValue(':repartidor', $repartidor['Clave']);
            $preparada->bindValue(':dia_inicial', $_POST['fecha']);
            $preparada->bindValue(':dia_final', $_POST['fecha']);
            $preparada->execute();

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            $distancia_total = 0;
            $velocidad_maxima = 0;
            $fechaMaxima = "";
            $indice = 0;

            while($indice < count($posiciones) ){

                if($posiciones[$indice]['velocidad'] > $velocidad_maxima){
                    $velocidad_maxima = $posiciones[$indice]['velocidad'];
                    $fechaMaxima = $posiciones[$indice]['fecha'];
                }
                if( $indice + 1 < count($posiciones) ){
                    $distancia_total += distancia($posiciones[$indice]['latitud'], $posiciones[$indice]['longitud'],$posiciones[$indice + 1]['latitud'], $posiciones[$indice + 1]['longitud']);
                }

                $indice++;
            }

            $resultados[] = [$repartidor['Clave'], $repartidor['Nombre'], $distancia_total, $velocidad_maxima, strtotime($fechaMaxima)];
        }

        echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>