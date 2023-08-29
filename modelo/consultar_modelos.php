<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_principal']->prepare("EXEC consultar_modelos @marca = :marca, @año = :anio");
        $preparada->bindValue(":marca", $_POST["marca"]);
        $preparada->bindValue(":anio", $_POST["año"]);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>