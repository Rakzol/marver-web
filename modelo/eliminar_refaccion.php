<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_sucursal']->prepare("DELETE FROM carrito WHERE usuario = :usuario AND producto = :producto");
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->bindValue(':producto', $_POST['producto']);
        $preparada->execute();

    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>