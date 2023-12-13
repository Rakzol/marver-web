<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        
        $preparada = $conexion->prepare('SELECT Nombre FROM Vendedores WHERE Nombre = :usuario');
        $preparada->bindValue(':usuario', $_POST['usuario']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            $resultado['usuario'] = false;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT Contraseña FROM Vendedores WHERE Nombre = :usuario AND Contraseña = :contrasena');
        $preparada->bindValue(':usuario', $_POST['usuario']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            $resultado['usuario'] = true;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $resultado['usuario'] = true;
        $resultado['contraseña'] = true;
        echo json_encode($resultado);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>