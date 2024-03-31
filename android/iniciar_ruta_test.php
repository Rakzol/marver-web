<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_GET['clave']);
        $preparada->bindValue(':contrasena', $_GET['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NOT NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_GET['clave']);
        $preparada->execute();

        if( count($preparada->fetchAll(PDO::FETCH_ASSOC)) > 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "Tienes entregas en proceso";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('SELECT TOP 1 id FROM rutas_repartidores WHERE repartidor = :repartidor AND fecha_inicio IS NULL AND fecha_fin IS NULL');
        $preparada->bindValue(':repartidor', $_GET['clave']);
        $preparada->execute();

        $rutas_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($rutas_repartidores) == 0 ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "No tiene entregas pendientes de inicializacion";
            echo json_encode($resultado);
            exit();
        }
        $ruta_repartidor = $rutas_repartidores[0];

        $preparada = $conexion->prepare("SELECT * FROM pedidos_repartidores WHERE ruta_repartidor = :ruta_repartidor");
        $preparada->bindValue(':ruta_repartidor', $ruta_repartidor['id']);
        $preparada->execute();

        $pedidos_repartidores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        var_dump($pedidos_repartidores);

        // echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        // header('HTTP/1.1 500 ' . $exception->getMessage());

        $resultado["status"] = 5;
        $resultado["mensaje"] = "Error al inicializar la ruta";
        echo json_encode($resultado);
    }
?>