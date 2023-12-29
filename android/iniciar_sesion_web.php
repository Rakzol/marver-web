<?php
    try{
        session_start();

        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        
        $preparada = $conexion->prepare('SELECT Perfil FROM Usuarios WHERE Nombre = :usuario');
        $preparada->bindValue(':usuario', $_POST['usuario']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            $resultado['usuario'] = false;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT Perfil FROM Usuarios WHERE Nombre = :usuario AND Contraseña = :contrasena');
        $preparada->bindValue(':usuario', $_POST['usuario']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $registros = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($registros) == 0 ){
            $resultado['usuario'] = true;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("SELECT Perfil FROM PerfilesDetalle WHERE Perfil = :perfil AND Modulo = 'Mapa' AND Opcion = 'Consultar Repartidores' AND Ver = 1 ");
        $preparada->bindValue(':perfil', $registros[0]['Perfil']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            $resultado['usuario'] = false;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $_SESSION['usuario_mapa'] = $_POST['usuario'];
        $_SESSION['contraseña_mapa'] = $_POST['contraseña'];
        $_SESSION['perfil_mapa'] = $registros[0]['Perfil'];

        $resultado['usuario'] = true;
        $resultado['contraseña'] = true;

        echo json_encode($resultado);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>