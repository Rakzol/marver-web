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

        $preparada = $conexion->prepare("SELECT usuario FROM posiciones WHERE fecha >= :dia_inicial AND fecha < :dia_final GROUP BY usuario");
        $preparada->bindValue(':dia_inicial', '2010-12-28');
        $preparada->bindValue(':dia_final', '2050-12-28');
        $preparada->execute();

        foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $repartidor ){
            print_r($repartidor);

            $preparada = $conexion->prepare("SELECT * FROM posiciones WHERE usuario = :repartidor");
            $preparada->bindValue(':repartidor', $repartidor['usuario']);
            $preparada->execute();

            foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $posicion ){
                if( $posicion['id'] % 10000 == 0 ){
                    print_r($posicion);
                }
            }

        }

        // echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>