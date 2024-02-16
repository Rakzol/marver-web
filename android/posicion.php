<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Nombre = :usuario AND Contraseña = :contrasena');
        $preparada->bindValue(':usuario', $_POST['u']);
        $preparada->bindValue(':contrasena', $_POST['c']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('INSERT INTO posiciones VALUES( :usuario, :latitud, :longitud, :velocidad, GETDATE() )');
        $preparada->bindValue(':usuario', $usuarios[0]['Clave']);
        $preparada->bindValue(':latitud', $_POST['la']);
        $preparada->bindValue(':longitud', $_POST['ln']);
        $preparada->bindValue(':velocidad', $_POST['v']);
        $preparada->execute();

    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>