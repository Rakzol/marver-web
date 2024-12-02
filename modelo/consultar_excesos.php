<?php
    try{
        session_start();

        header('Content-Type: application/json');

        if(!isset($_SESSION['usuario_mapa'])){
            echo '[]';
            exit();
        }

        switch($_SESSION["sucursal"]){
            case "Mochis":
                $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
            case "Guasave":
                $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
        }
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

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

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', $_POST['fecha']);
        $preparada->bindValue(':dia_final', $_POST['fecha']);
        $preparada->execute();

        $segundos_fin = 60;

        $resultados = [];
        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor AND fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final)");
            $preparada->bindValue(':repartidor', $repartidor['Clave']);
            $preparada->bindValue(':dia_inicial', $_POST['fecha']);
            $preparada->bindValue(':dia_final', $_POST['fecha']);
            $preparada->execute();

            $posicion_mala = [];
            $posicion_buena = [];
            $velocidad_maxima = 0;
            $id_maxima = 0;

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            foreach( $posiciones as $posicion ){

                if(!$posicion_mala){
                    if( $posicion['velocidad'] >= $_POST['velocidad_limite'] ){
                        $posicion_mala = $posicion;
                        $velocidad_maxima = $posicion['velocidad'];
                        $id_maxima = $posicion['id'];
                    }
                }else{
                    if(!$posicion_buena){
                        if($posicion['velocidad'] > $velocidad_maxima){
                            $velocidad_maxima = $posicion['velocidad'];
                            $id_maxima = $posicion['id'];
                        }
                        if( $posicion['velocidad'] < $_POST['velocidad_limite'] ){
                            $posicion_buena = $posicion;
                        }
                    }else{
                        if($posicion['velocidad'] > $velocidad_maxima){
                            $velocidad_maxima = $posicion['velocidad'];
                            $id_maxima = $posicion['id'];
                        }
                        if( $posicion['velocidad'] >= $_POST['velocidad_limite'] ){
                            $posicion_buena = [];
                        }else{
                            if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($posicion_buena['fecha']))->getTimestamp() >= $segundos_fin ){
                                $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                                // if( $tiempo >= $_POST['tiempo_limite'] ){
                                    $resultados[] = [$repartidor,'Velocidad',$tiempo,$velocidad_maxima,$posicion_mala,$posicion_buena,$id_maxima];
                                // }
                                $posicion_mala = [];
                                $posicion_buena = [];
                                $velocidad_maxima = 0;
                                $id_maxima = 0;
                            }
                        }
                    }
                }
                
            }

            if($posicion_mala){
                if($posicion_buena){
                    $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                    // if( $tiempo >= $_POST['tiempo_limite'] ){
                        $resultados[] = [$repartidor,'Velocidad',$tiempo,$velocidad_maxima,$posicion_mala,$posicion_buena,$id_maxima];
                    // }
                }else{
                    $tiempo = (new DateTime(end($posiciones)['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                    // if( $tiempo >= $_POST['tiempo_limite'] ){
                        $resultados[] = [$repartidor,'Velocidad',$tiempo,$velocidad_maxima,$posicion_mala,end($posiciones),$id_maxima];
                    // }
                }
            }

        }

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', $_POST['fecha']);
        $preparada->bindValue(':dia_final', $_POST['fecha']);
        $preparada->execute();

        $velocidad_parada = 6.55;
        $segundos_fin = 5;

        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor AND fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final)");
            $preparada->bindValue(':repartidor', $repartidor['Clave']);
            $preparada->bindValue(':dia_inicial', $_POST['fecha']);
            $preparada->bindValue(':dia_final', $_POST['fecha']);
            $preparada->execute();

            $posicion_mala = [];
            $posicion_buena = [];

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            foreach( $posiciones as $posicion ){

                if(!$posicion_mala){
                    if( $posicion['velocidad'] <= $velocidad_parada && distancia(25.794137, -108.986085, $posicion['latitud'], $posicion['longitud']) > 0.055 ){
                        $posicion_mala = $posicion;
                    }
                }else{
                    if(!$posicion_buena){
                        if( $posicion['velocidad'] > $velocidad_parada || distancia(25.794137, -108.986085, $posicion['latitud'], $posicion['longitud']) <= 0.055 ){
                            $posicion_buena = $posicion;
                        }
                    }else{
                        if( $posicion['velocidad'] <= $velocidad_parada && distancia(25.794137, -108.986085, $posicion['latitud'], $posicion['longitud']) > 0.055 ){
                            $posicion_buena = [];
                        }else{
                            if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($posicion_buena['fecha']))->getTimestamp() >= $segundos_fin ){
                                $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                                if( $tiempo >= $_POST['tiempo_limite'] ){
                                    $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,$posicion_buena];
                                }
                                $posicion_mala = [];
                                $posicion_buena = [];
                            }
                        }
                    }
                }
                
            }

            if($posicion_mala){
                if($posicion_buena){
                    $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                    if( $tiempo >= $_POST['tiempo_limite'] ){
                        $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,$posicion_buena];
                    }
                }else{
                    $tiempo = (new DateTime(end($posiciones)['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                    if( $tiempo >= $_POST['tiempo_limite'] ){
                        $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,end($posiciones)];
                    }
                }
            }

        }

        echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>