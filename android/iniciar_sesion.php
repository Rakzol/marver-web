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
        
        $preparada = $conexion->prepare('SELECT Nombre FROM Vendedores WHERE Clave = :clave');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) == 0 ){
            $resultado['usuario'] = false;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT Nombre FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $registros = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($registros) == 0 ){
            $resultado['usuario'] = true;
            $resultado['contraseña'] = false;
            echo json_encode($resultado);
            exit();
        }

        $resultado['usuario'] = true;
        $resultado['contraseña'] = true;
        $resultado['nombre'] = $registros[0]['Nombre'];
        echo json_encode($resultado);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>