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

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', $_POST['fecha']);
        $preparada->bindValue(':dia_final', $_POST['fecha']);
        $preparada->execute();

        $segundos_fin = 300;

        $resultados = [];
        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor AND fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final)");
            $preparada->bindValue(':repartidor', $repartidor['Clave']);
            $preparada->bindValue(':dia_inicial', $_POST['fecha']);
            $preparada->bindValue(':dia_final', $_POST['fecha']);
            $preparada->execute();

            $posicion_mala = [];
            $posicion_buena = [];
            $ultima_posicion = [];
            $velocidad_maxima = 0;
            $id_maxima = 0;

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            foreach( $posiciones as $posicion ){

                if(!$posicion_mala){
                    if( $posicion['velocidad'] >= $_POST['velocidad_limite'] ){
                        $posicion_mala = $posicion;
                        $ultima_posicion = $posicion;
                        $velocidad_maxima = $posicion['velocidad'];
                        $id_maxima = $posicion['id'];
                    }
                }else{
                    if(!$posicion_buena){
                        if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($ultima_posicion['fecha']))->getTimestamp() <= 60 ){
                            $ultima_posicion = $posicion;
                            if($posicion['velocidad'] > $velocidad_maxima){
                                $velocidad_maxima = $posicion['velocidad'];
                                $id_maxima = $posicion['id'];
                            }
                            if( $posicion['velocidad'] < $_POST['velocidad_limite'] ){
                                $posicion_buena = $posicion;
                            }
                        }else{
                            $tiempo = (new DateTime($ultima_posicion['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                            // if( $tiempo >= $_POST['tiempo_limite'] ){
                                $resultados[] = [$repartidor,'Velocidad',$tiempo,$velocidad_maxima,$posicion_mala,$ultima_posicion,$id_maxima];
                            // }

                            $posicion_mala = [];
                            $posicion_buena = [];
                            $ultima_posicion = [];
                            $velocidad_maxima = 0;
                            $id_maxima = 0;

                            if( $posicion['velocidad'] >= $_POST['velocidad_limite'] ){
                                $posicion_mala = $posicion;
                                $ultima_posicion = $posicion;
                                $velocidad_maxima = $posicion['velocidad'];
                                $id_maxima = $posicion['id'];
                            }
                        }
                    }else{
                        if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($ultima_posicion['fecha']))->getTimestamp() <= 60 ){
                            $ultima_posicion = $posicion;
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
                                    $ultima_posicion = [];
                                    $velocidad_maxima = 0;
                                    $id_maxima = 0;
                                }
                            }
                        }else{
                            $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                            // if( $tiempo >= $_POST['tiempo_limite'] ){
                                $resultados[] = [$repartidor,'Velocidad',$tiempo,$velocidad_maxima,$posicion_mala,$posicion_buena,$id_maxima];
                            // }

                            $posicion_mala = [];
                            $posicion_buena = [];
                            $ultima_posicion = [];
                            $velocidad_maxima = 0;
                            $id_maxima = 0;

                            if( $posicion['velocidad'] >= $_POST['velocidad_limite'] ){
                                $posicion_mala = $posicion;
                                $ultima_posicion = $posicion;
                                $velocidad_maxima = $posicion['velocidad'];
                                $id_maxima = $posicion['id'];
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

        $preparada = $conexion->prepare("SELECT Clave, Nombre FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = posiciones.usuario WHERE fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) AND 6371 * 2 * ASIN( SQRT( POWER(SIN(RADIANS((25.794137 - latitud) / 2)), 2) + COS(RADIANS(25.794137)) * COS(RADIANS(latitud)) * POWER(SIN(RADIANS((-108.986085 - longitud) / 2)), 2) ) ) >= 0.055 GROUP BY Clave, Nombre");
        $preparada->bindValue(':dia_inicial', $_POST['fecha']);
        $preparada->bindValue(':dia_final', $_POST['fecha']);
        $preparada->execute();

        $velocidad_parada = 2.22;
        $segundos_fin = 3;

        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor AND fecha >= :dia_inicial AND fecha < DATEADD(DAY, 1, :dia_final) AND 6371 * 2 * ASIN( SQRT( POWER(SIN(RADIANS((25.794137 - latitud) / 2)), 2) + COS(RADIANS(25.794137)) * COS(RADIANS(latitud)) * POWER(SIN(RADIANS((-108.986085 - longitud) / 2)), 2) ) ) >= 0.055");
            $preparada->bindValue(':repartidor', $repartidor['Clave']);
            $preparada->bindValue(':dia_inicial', $_POST['fecha']);
            $preparada->bindValue(':dia_final', $_POST['fecha']);
            $preparada->execute();

            $posicion_mala = [];
            $posicion_buena = [];
            $ultima_posicion = [];

            $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);
            foreach( $posiciones as $posicion ){

                if(!$posicion_mala){
                    if( $posicion['velocidad'] <= $velocidad_parada ){
                        $posicion_mala = $posicion;
                        $ultima_posicion = $posicion;
                    }
                }else{
                    if(!$posicion_buena){
                        if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($ultima_posicion['fecha']))->getTimestamp() <= 60 ){
                            $ultima_posicion = $posicion;
                            if( $posicion['velocidad'] > $velocidad_parada ){
                                $posicion_buena = $posicion;
                            }
                        }else{
                            $tiempo = (new DateTime($ultima_posicion['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                            if( $tiempo >= $_POST['tiempo_limite'] ){
                                $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,$ultima_posicion];
                            }

                            $posicion_mala = [];
                            $posicion_buena = [];
                            $ultima_posicion = [];

                            if( $posicion['velocidad'] <= $velocidad_parada ){
                                $posicion_mala = $posicion;
                                $ultima_posicion = $posicion;
                            }
                        }
                    }else{
                        if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($ultima_posicion['fecha']))->getTimestamp() <= 60 ){
                            $ultima_posicion = $posicion;
                            if( $posicion['velocidad'] <= $velocidad_parada ){
                                $posicion_buena = [];
                            }else{
                                if( (new DateTime($posicion['fecha']))->getTimestamp() - (new DateTime($posicion_buena['fecha']))->getTimestamp() >= $segundos_fin ){
                                    $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                                    if( $tiempo >= $_POST['tiempo_limite'] ){
                                        $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,$posicion_buena];
                                    }
                                    $posicion_mala = [];
                                    $posicion_buena = [];
                                    $ultima_posicion = [];
                                }
                            }
                        }else{
                            $tiempo = (new DateTime($posicion_buena['fecha']))->getTimestamp() - (new DateTime($posicion_mala['fecha']))->getTimestamp();
                            if( $tiempo >= $_POST['tiempo_limite'] ){
                                $resultados[] = [$repartidor,'Tiempo',$tiempo,0,$posicion_mala,$posicion_buena];
                            }

                            $posicion_mala = [];
                            $posicion_buena = [];
                            $ultima_posicion = [];

                            if( $posicion['velocidad'] <= $velocidad_parada ){
                                $posicion_mala = $posicion;
                                $ultima_posicion = $posicion;
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