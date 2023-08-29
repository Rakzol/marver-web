<?php
    try{
        require_once('inicializar_datos.php');
        
        $retorno['cliente'] = $datos['cliente'];
        $retorno['descuentos'] = $datos['descuentos'];

        $contador_letras = 0;
        for ($ca = 0; $ca < strlen($_POST["palabras"]); $ca++) {
          if ($_POST["palabras"][$ca] != " ") {
            $contador_letras++;
          }
        }
        if($contador_letras <= 1){
            $retorno['refacciones'] = [];
            echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
            exit();
        }

        $txt_palabras_busqueda = $_POST["palabras"];

        $comando =
        "SELECT Producto.Codigo, Producto.Descripcion, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante, refacciones.imagen, refacciones.fabricante AS fabricante_refaccion, refacciones.clave FROM " .
        "Mochis.dbo.Producto " .
        "LEFT JOIN Mochis.dbo.ProductosIgualados ON ProductosIgualados.CodigoSistema = Producto.Codigo AND ProductosIgualados.CodigoSistema != ProductosIgualados.Codigo " .
        "LEFT JOIN refacciones ON refacciones.Clave = ProductosIgualados.Codigo ";

        $comando_palabras = "";
        foreach ( explode(" ", $txt_palabras_busqueda) as $palabra )
        {
            $comando_palabras .= "( (refacciones.clave LIKE '%" . $palabra . "%') OR (refacciones.marca LIKE '%" . $palabra . "%') OR (refacciones.año LIKE '%" . $palabra . "%') OR (refacciones.modelo LIKE '%" . $palabra . "%') OR (refacciones.motor LIKE '%" . $palabra . "%') OR " .
                                "(Producto.Producto LIKE '%" . $palabra . "%') OR (Producto.Sistema LIKE '%" . $palabra . "%') OR (Producto.Subsistema LIKE '%" . $palabra . "%') OR " .
                                "(Producto.Codigo LIKE '%" . $palabra . "%') OR (Producto.Descripcion LIKE '%" . $palabra . "%') OR (Producto.Fabricante LIKE '%" . $palabra . "%') ) AND ";
        }

        $comando .= strlen($comando_palabras) > 0 ? " WHERE " . substr($comando_palabras, 0, -5) : "";

        $comando .= " UNION ";

        $comando_2 =
        "SELECT Producto.Codigo, Producto.Descripcion, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante, refacciones.imagen, refacciones.fabricante AS fabricante_refaccion, refacciones.clave FROM " .
        "Mochis.dbo.Producto " .
        "LEFT JOIN refacciones ON refacciones.Clave = Producto.Codigo ";

        $comando_palabras_2 = "";
        foreach (explode(" ", $txt_palabras_busqueda) as $palabra)
        {
            $comando_palabras_2 .= "( (refacciones.clave LIKE '%" . $palabra . "%') OR (refacciones.marca LIKE '%" . $palabra . "%') OR (refacciones.año LIKE '%" . $palabra . "%') OR (refacciones.modelo LIKE '%" . $palabra . "%') OR (refacciones.motor LIKE '%" . $palabra . "%') OR " .
                                "(Producto.Producto LIKE '%" . $palabra . "%') OR (Producto.Sistema LIKE '%" . $palabra . "%') OR (Producto.Subsistema LIKE '%" . $palabra . "%') OR " .
                                "(Producto.Codigo LIKE '%" . $palabra . "%') OR (Producto.Descripcion LIKE '%" . $palabra . "%') OR (Producto.Fabricante LIKE '%" . $palabra . "%') ) AND ";
        }

        $comando_2 .= strlen($comando_palabras_2) > 0 ? " WHERE " . substr($comando_palabras_2, 0, -5) : "";

        $comando .= $comando_2;

        $comando .= " GROUP BY Producto.Codigo, Producto.Descripcion, Producto.Sistema, Producto.Subsistema, Producto.Producto, Producto.Fabricante, refacciones.imagen, refacciones.fabricante, refacciones.clave ORDER BY Producto.Producto";

        $preparada = $datos['conexion_catalogo_principal']->prepare($comando);
        $preparada->execute();

        $retorno['refacciones'] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>