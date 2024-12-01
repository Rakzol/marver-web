<?php
    try{
        header('Content-Type: application/json');

        switch($_POST["sucursal"]){
            case "Mochis":
                $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
            case "Guasave":
                $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
        }
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $nombre = explode(".", $_POST['nombre'])[0];

        /*$sin_espacios = str_replace(" ", "", $_POST['foto']);
        $sin_salto = str_replace("\n", "", $sin_espacios);
        $sin_reseteo = str_replace("\r", "", $sin_salto);*/
        if(!file_put_contents( 'fotos/' . $_POST["sucursal"] . '/' . $_POST['nombre'] , base64_decode(str_replace(" ", "+", $_POST['foto'])) )){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "No se pudo almacenar la foto: " . $nombre;
            echo json_encode($resultado);
            exit();
        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = "La foto para el Pedido: " . $nombre . " se subio correctamente";
        $resultado["eliminar"] = 1;
        echo json_encode($resultado);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 4;
        $resultado["mensaje"] = "El pedido con el folio: " . $nombre . " no es valido";
        echo json_encode($resultado);
    }
?>