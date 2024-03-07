<?php
    try{
        require_once('inicializar_datos.php');

        //Intentamos agregar una unidad extra al producto en carrito del usuario actual
        $preparada = $datos['conexion_base_sucursal']->prepare("
        IF EXISTS (SELECT 1 FROM clientes_posiciones WHERE clave = :clave_1)
        BEGIN
            UPDATE clientes_posiciones
            SET latitud = :latitud_1,
                longitud = :longitud_1
            WHERE clave = :clave_2;
        END
        ELSE
        BEGIN
            INSERT INTO clientes_posiciones (clave, latitud, longitud) VALUES (:clave_3, :latitud_2, :longitud_2);
        END");
        $preparada->bindValue(':clave_1', $datos['usuario']['cliente']);
        $preparada->bindValue(':clave_2', $datos['usuario']['cliente']);
        $preparada->bindValue(':clave_3', $datos['usuario']['cliente']);
        $preparada->bindValue(':latitud_1', $_POST['latitud']);
        $preparada->bindValue(':longitud_1', $_POST['longitud']);
        $preparada->bindValue(':latitud_2', $_POST['latitud']);
        $preparada->bindValue(':longitud_2', $_POST['longitud']);
        $preparada->execute();

        $resultado["actualizada"] = true;
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>