<?php
    try{
        require_once('inicializar_datos.php');

        //Intentamos agregar una unidad extra al producto en carrito del usuario actual
        $preparada = $datos['conexion_catalogo_sucursal']->prepare("
        IF EXISTS (SELECT 1 FROM clientes_posiciones WHERE clave = :clave)
        BEGIN
            UPDATE clientes_posiciones
            SET latitud = :latitud,
                longitud = :longitud
            WHERE clave = :clave;
        END
        ELSE
        BEGIN
            INSERT INTO clientes_posiciones (clave, latitud, longitud) VALUES (:clave, :latitud, :longitud);
        END");
        $preparada->bindValue(':clave', $datos['usuario']['cliente']);
        $preparada->bindValue(':latitud', $_POST['latitud']);
        $preparada->bindValue(':longitud', $_POST['longitud']);
        $preparada->execute();

        $resultado["actualizada"] = true;
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ){
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>