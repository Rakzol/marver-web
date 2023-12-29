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

        $preparada = $conexion->prepare("SELECT usuario, Nombre, latitud, longitud, velocidad FROM ( SELECT usuario, Nombre, latitud, longitud, velocidad, ROW_NUMBER() OVER ( PARTITION BY usuario ORDER BY fecha DESC) AS fila FROM posiciones INNER JOIN Vendedores ON Vendedores.Clave = usuario AND Vendedores.Extra1 = 'REPARTIDOR' ) AS ordenado WHERE fila = 1 ORDER BY Nombre");
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>