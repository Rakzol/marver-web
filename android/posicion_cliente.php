<?php
    try{
        session_start();

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare("SELECT latitud, longitud FROM clientes_posiciones WHERE clave = :clave");
        $preparada->bindValue(':clave', $_POST['c']);
        $preparada->execute();

        $clientes = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($clientes) > 0 ){
            $cliente["latitud"] = $clientes[0]["latitud"];
            $cliente["longitud"] = $clientes[0]["longitud"];
        }else{
            $cliente["latitud"] = "no";
            $cliente["longitud"] = "no";
        }

        echo json_encode( $cliente, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>