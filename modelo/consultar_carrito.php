<?php
    try{
        require_once('inicializar_datos.php');

        // $preparada = $datos['conexion_catalogo_principal']->prepare(
        //     'SELECT pdr.Producto, pdr.Existencia, pdr.Codigo, pdr.Sistema, pdr.Subsistema, pdr.Fabricante, pdr.Costo, pdr.Utilidades, pdr.Iva, car.cantidad, rfc.clave, rfc.fabricante AS fabricante_refaccion, rfc.imagen FROM carrito car ' .
        //     'LEFT JOIN ' . $datos['linked_server'] . $datos['base_sucursal'] . '.dbo.ProductosIgualados pdi ON pdi.CodigoSistema = car.producto AND pdi.CodigoSistema != pdi.Codigo ' .
        //     'LEFT JOIN refacciones rfc ON (rfc.clave = car.producto OR rfc.clave = pdi.Codigo) ' .
        //     'INNER JOIN ' . $datos['linked_server'] . $datos['base_sucursal'] . '.dbo.Producto pdr ON pdr.Codigo = car.producto ' .
        //     'WHERE car.usuario = :usuario ' .
        //     'GROUP BY pdr.Producto, pdr.Existencia, pdr.Codigo, pdr.Sistema, pdr.Subsistema, pdr.Fabricante, pdr.Costo, pdr.Utilidades, pdr.Iva, car.cantidad, rfc.clave, rfc.fabricante, rfc.imagen'
        // );

        $preparada = $datos['conexion_catalogo_sucursal']->prepare(
            'SELECT pdr.Producto, pdr.Existencia, pdr.Codigo, pdr.Sistema, pdr.Subsistema, pdr.Fabricante, pdr.Costo, pdr.Utilidades, pdr.Iva, car.cantidad FROM carrito car ' .
            'INNER JOIN ' . $datos['base_sucursal'] . '.dbo.Producto pdr ON pdr.Codigo = car.producto ' .
            'WHERE car.usuario = :usuario ' .
            'GROUP BY pdr.Producto, pdr.Existencia, pdr.Codigo, pdr.Sistema, pdr.Subsistema, pdr.Fabricante, pdr.Costo, pdr.Utilidades, pdr.Iva, car.cantidad'
        );
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->execute();

        $retorno['productos'] = $preparada->fetchAll(PDO::FETCH_ASSOC);
        $retorno['cliente'] = $datos['cliente'];
        $retorno['descuentos'] = $datos['descuentos'];

        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>