<?php
    try{
        header('Content-Type: application/json');

        //// Inicio de sesion
        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('INSERT INTO posicion VALUES( GETDATE(), :latitud, :longitud )');
        $preparada->bindValue(':latitud', $_POST['latitud']);
        $preparada->bindValue(':longitud', $_POST['longitud']);
        $preparada->execute();

        echo '{"latitud": ' . $_POST['latitud'] . ', "longitud": ' . $_POST['longitud'] . '}';
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>