<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL');
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if(count($rutas_repartidores) == 0){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "No tiene rutas en curso -> " . $_POST['clave'] . " -> " . http_build_query($_POST);
            echo json_encode($resultado);
            exit();
        }else{
            $id_ruta_reparto = $rutas_repartidores[0]['id'];
        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = $id_ruta_reparto;
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 69;
        $resultado["mensaje"] = "Error al obtener la ruta del repartidor";
        echo json_encode($resultado);
    }
?>