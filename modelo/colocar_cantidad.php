<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_sucursal']->prepare("UPDATE carrito SET cantidad = :cantidad WHERE usuario = :usuario AND producto = :producto");
        $preparada->bindValue(':cantidad', $_POST['cantidad']);
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->bindValue(':producto', $_POST['codigo']);
        $preparada->execute();
        
    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>