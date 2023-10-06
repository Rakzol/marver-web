<?php
    try{
        session_start();

        if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ){
            $ip_list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if( filter_var($ip, FILTER_VALIDATE_IP) ){
            $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
            $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
            
            $preparada = $conexion->prepare('INSERT INTO estadisticas VALUES (:usuario, :ip, :sesion, GETDATE(), :archivo);');
            $preparada->bindValue(':usuario', isset($_SESSION['usuario']) ? $_SESSION['usuario']: NULL );
            $preparada->bindValue(':ip', $ip);
            $preparada->bindValue(':sesion', session_id());
            $preparada->bindValue(':archivo', debug_backtrace()[0]['file']);
            $preparada->execute();
        }

    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>