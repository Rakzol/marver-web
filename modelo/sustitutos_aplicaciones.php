<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_principal']->prepare(
            "SELECT Sustitutos.Codigosustituto, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante FROM Sustitutos " .
            "INNER JOIN Producto ON Sustitutos.Codigosustituto = Producto.Codigo " .
            "WHERE Sustitutos.Codigo = :codigo " .
            "GROUP BY Sustitutos.Codigosustituto, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante ORDER BY Sustitutos.Codigosustituto, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante"
        );
        $preparada->bindValue(":codigo", $_POST["codigo"]);
        $preparada->execute();

        $retorno['sustitutos'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $preparada = $datos['conexion_catalogo_principal']->prepare("SELECT marca, año, modelo, motor, informacion FROM refacciones WHERE Clave = :clave GROUP BY marca, año, modelo, motor, informacion ORDER BY marca, año, modelo, motor, informacion");
        $preparada->bindValue(":clave", $_POST["clave"]);
        $preparada->execute();

        $retorno['aplicaciones'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>