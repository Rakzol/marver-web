<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_principal']->prepare("EXEC consultar_refacciones @marca = :marca, @año = :anio, @modelo = :modelo, @motor = :motor, @sistema = :sistema, @subsistema = :subsistema, @producto = :producto");
        $preparada->bindValue(":marca", $_POST["marca"]);
        $preparada->bindValue(":anio", $_POST["año"]);
        $preparada->bindValue(":modelo", $_POST["modelo"]);
        $preparada->bindValue(":motor", $_POST["motor"]);
        $preparada->bindValue(":sistema", $_POST["sistema"]);
        $preparada->bindValue(":subsistema", $_POST["subsistema"]);
        $preparada->bindValue(":producto", $_POST["producto"]);
        $preparada->execute();

        $retorno['refacciones'] = $preparada->fetchAll(PDO::FETCH_ASSOC);
        $retorno['cliente'] = $datos['cliente'];
        $retorno['descuentos'] = $datos['descuentos'];

        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>