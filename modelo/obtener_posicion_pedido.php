<?php
    try{
        require_once('inicializar_datos.php');

        //Intentamos agregar una unidad extra al producto en carrito del usuario actual
        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT latitud, longitud FROM clientes_posiciones WHERE clave = :clave");
        $preparada->bindValue(':clave', $datos['usuario']['cliente']);
        $preparada->execute();

        $posiciones = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($posiciones) > 0 ){
            $resultado["latitud"] = $posiciones[0]["latitud"];
            $resultado["longitud"] = $posiciones[0]["longitud"];
        }else{
            $resultado["latitud"] = "no";
            $resultado["longitud"] = "no";
        }

        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>