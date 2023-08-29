<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_principal']->prepare('SELECT rfc.clave, rfc.fabricante, rfc.imagen FROM refacciones rfc ' .
            'LEFT JOIN Mochis.dbo.ProductosIgualados pdi ON pdi.CodigoSistema = :codigo_1 AND pdi.CodigoSistema != pdi.Codigo ' .
            'WHERE rfc.clave = :codigo_2 OR rfc.clave = pdi.Codigo');
        $preparada->bindValue(':codigo_1', $_POST['codigo']);
        $preparada->bindValue(':codigo_2', $_POST['codigo']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>