<?php
    try{
        //Iniciamos la sesion para poder borrar los datos
        session_start();
        //Borramos los datos de la sesion del archivo del servidor (un se puede acceder desde $_SESSION aun que se borren del archivo, siguen en cache)
        //session_destroy();

        //Iniciamos una nueva sesion (Tiene la misma ID de la session destruida y por lo tanto la misma a la que hace referencia la cookie de sesion)
        //session_start();
        //Hacemos que el ID de la sesion actual cambie por uno nuevo y al mismo tiempo se borre el archivo y cambie la ID en la cookie
        //session_regenerate_id(true);

        header('Content-Type: application/json');

        //// Inicio de sesion
        $conexion = new PDO('sqlsrv:Server=10.10.10.83;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        
        $preparada = $conexion->prepare('SELECT correo FROM usuarios WHERE correo = :correo');
        $preparada->bindValue(':correo', $_POST['correo']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            echo '{"correo": false, "contraseña": false}';
            exit();
        }

        $preparada = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo AND contraseña = :contrasena');
        $preparada->bindValue(':correo', $_POST['correo']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($usuarios) == 0 ){
            echo '{"correo": true, "contraseña": false}';
            exit();
        }
        
        $_SESSION['usuario'] = $usuarios[0]['id'];

        echo '{"correo": true, "contraseña": true}';
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>