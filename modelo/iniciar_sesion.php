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

        if( !isset($_POST['lat_nav']) || !isset($_POST['lon_nav']) ){
            echo '{"posicion": false}';
            exit();
        }

        //// Inicio de sesion
        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        
        $preparada = $conexion->prepare('SELECT correo FROM usuarios WHERE correo = :correo');
        $preparada->bindValue(':correo', $_POST['correo']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            echo '{"correo": false, "contraseña": false, "posicion": true}';
            exit();
        }

        $preparada = $conexion->prepare('SELECT id FROM usuarios WHERE correo = :correo AND contraseña = :contrasena');
        $preparada->bindValue(':correo', $_POST['correo']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($usuarios) == 0 ){
            echo '{"correo": true, "contraseña": false, "posicion": true}';
            exit();
        }
        
        $_SESSION['usuario'] = $usuarios[0]['id'];

        /********/
        if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ){
            $ip_list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if( !filter_var($ip, FILTER_VALIDATE_IP) ){
            $ip = '0.0.0.0';
        }

        echo filter_var($ip, FILTER_VALIDATE_IP);
        try{
            $url = "https://ipinfo.io/{$ip}?token=a39ff8f192d166";

            $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);

            if ($response != false) {
                $data = json_decode($response, true);

                $location = explode(',', $data['loc']);

                $lat_api = $location[0];
                $lon_api = $location[1];
            }else{
                $lat_api = null;
                $lon_api = null;
            }

            curl_close($curl);

        }catch(Exception $ex){
            $lat_api = null;
            $lon_api = null;
        }

        $preparada = $conexion->prepare('INSERT INTO ubicaciones_usuarios VALUES (:usuario, :ip, :lat_nav, :lon_nav, :lat_api, :lon_api, :precision, GETDATE(), :pedido)');
        $preparada->bindValue(':usuario', $_SESSION['usuario'] );
        $preparada->bindValue(':ip', $ip);
        $preparada->bindValue(':lat_nav', $_POST['lat_nav']);
        $preparada->bindValue(':lon_nav', $_POST['lon_nav']);
        $preparada->bindValue(':lat_api', $lat_api);
        $preparada->bindValue(':lon_api', $lon_api);
        $preparada->bindValue(':precision', $_POST['precision']);
        $preparada->bindValue(':pedido', null);
        $preparada->execute();

        /***********/

        echo '{"correo": true, "contraseña": true, "posicion": true}';
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>