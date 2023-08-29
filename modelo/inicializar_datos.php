<?php
    
    ini_set("memory_limit","512M");
    ini_set('max_execution_time', '0');

    session_start();

    if(!isset($_SESSION['usuario'])){
        throw new Exception('No se inicio sesión.');
    }

    header('Content-Type: application/json');

    $datos['conexion_catalogo_principal'] = new PDO('sqlsrv:Server=10.10.10.83;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
    $datos['conexion_catalogo_principal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $datos['conexion_base_principal'] = new PDO('sqlsrv:Server=10.10.10.83;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
    $datos['conexion_base_principal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $preparada = $datos['conexion_catalogo_principal']->prepare('SELECT * FROM usuarios WHERE id = :id');
    $preparada->bindValue(':id', $_SESSION['usuario']);
    $preparada->execute();

    $datos['usuario'] = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    switch( $datos['usuario']['sucursal'] ){
        case 'MOCHIS':
            $datos['base_sucursal'] = 'Mochis';

            $datos['conexion_catalogo_sucursal'] = new PDO('sqlsrv:Server=10.10.10.83;Database=CatalagoLM;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_catalogo_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
    
            $datos['conexion_base_sucursal'] = new PDO('sqlsrv:Server=10.10.10.83;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_base_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        break;
        case 'GUASAVE':
            $datos['base_sucursal'] = 'Guasave';

            $datos['conexion_catalogo_sucursal'] = new PDO('sqlsrv:Server=12.12.12.254;Database=CatalogoGuasave;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_catalogo_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

            $datos['conexion_base_sucursal'] = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_base_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        break;
        case 'HIGUERA':
            $datos['base_sucursal'] = 'Higuera';

            $datos['conexion_catalogo_sucursal'] = new PDO('sqlsrv:Server=11.11.11.52;Database=CatalogoHiguera;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_catalogo_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

            $datos['conexion_base_sucursal'] = new PDO('sqlsrv:Server=11.11.11.52;Database=Higuera;TrustServerCertificate=true','MARITE','2505M$RITE');
            $datos['conexion_base_sucursal']->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);
        break;
    }

    $preparada = $datos['conexion_base_sucursal']->prepare('SELECT * FROM Clientes WHERE Clave = :cliente');
    $preparada->bindValue(':cliente', $datos['usuario']['cliente']);
    $preparada->execute();

    $datos['cliente'] = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    $preparada = $datos['conexion_base_sucursal']->prepare('SELECT * FROM DescuentosCliente WHERE Cliente = :cliente');
    $preparada->bindValue(':cliente', $datos['usuario']['cliente']);
    $preparada->execute();

    $datos['descuentos'] = $preparada->fetchAll(PDO::FETCH_ASSOC);
?>