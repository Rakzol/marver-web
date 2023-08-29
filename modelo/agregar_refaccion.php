<?php
    try{
        require_once('inicializar_datos.php');

        //Intentamos agregar una unidad extra al producto en carrito del usuario actual
        $preparada = $datos['conexion_catalogo_sucursal']->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario = :usuario AND producto = :producto");
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->bindValue(':producto', $_POST['codigo']);
        $preparada->execute();

        //Si no se agrego una unidad extra quiere decir que no se a registrado ninguna, entonces registramos una
        if( $preparada->rowCount() == 0 ){
            $preparada = $datos['conexion_catalogo_sucursal']->prepare("INSERT INTO carrito VALUES (:usuario, :producto, 1)");
            $preparada->bindValue(':usuario', $datos['usuario']['id']);
            $preparada->bindValue(':producto', $_POST['codigo']);
            $preparada->execute();
        }

        echo '{"agregada": true}';
    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>