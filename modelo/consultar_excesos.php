<?php
    try{
        session_start();

        header('Content-Type: application/json');

        if(!isset($_SESSION['usuario_mapa'])){
            echo '[]';
            exit();
        }

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < :dia_final AND ( latitud > 25.7944994 OR latitud < 25.7941221 ) AND ( longitud > -108.9851520 OR longitud < -108.9866105 ) GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', '2010-12-28');
        $preparada->bindValue(':dia_final', '2050-12-28');
        $preparada->execute();

        $velocidad_limite = 5;
        $segundos_inicio = 600;
        $segundos_fin = 3;

        $resultados = [];
        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){
            print_r($repartidor);

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor AND ( latitud > 25.7944994 OR latitud < 25.7941221 ) AND ( longitud > -108.9851520 OR longitud < -108.9866105 )");
            $preparada->bindValue(':repartidor', $repartidor['Clave']); 
            $preparada->execute();

            $posicion_mala = [];
            $posicion_buena = [];

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            foreach( $posiciones as $posicion ){

                if(!$posicion_mala){
                    if( $posicion['velocidad'] <= $velocidad_limite ){
                        $posicion_mala = $posicion;
                    }
                }else{
                    if(!$posicion_buena){
                        if( $posicion['velocidad'] > $velocidad_limite ){
                            $posicion_buena = $posicion;
                        }
                    }else{
                        if( $posicion['velocidad'] <= $velocidad_limite ){
                            $posicion_buena = [];
                        }else{
                            if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($posicion_buena['fecha']))->getTimestamp() >= $segundos_fin ){
                                if( (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp() >= $segundos_inicio ){
                                    $resultados[] = [$posicion_mala,$posicion_buena];
                                }
                                $posicion_mala = [];
                                $posicion_buena = [];
                            }
                        }
                    }
                }
                
            }

            if($posicion_mala){
                if( (new DateTime(end($posiciones)['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp() >= $segundos_inicio ){
                    $resultados[] = [$posicion_mala,end($posiciones)];
                }
            }

        }

        echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>