<?php

    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cliente, FolioComprobante FROM PedidosCliente WHERE Folio = :folio");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        if($pedido['Cliente'] != $datos['cliente']['Clave'] && !is_null($pedido['FolioComprobante']) ){
            header('HTTP/1.1 403');
            exit();
        }

        $archivo = 'C:/Sistema Marver/Facturas/XML/A_' . str_pad((string)$pedido['FolioComprobante'], 10, '0', STR_PAD_LEFT) . '.XML';

        if(!file_exists($archivo)){
            header('HTTP/1.1 500 El archivo no existe');
            exit();
        }

        header('Content-Disposition: attachment; filename=' . basename($archivo));
        header('Content-Type: application/xml');
        header('Content-Length: ' . filesize($archivo));
        readfile($archivo);

    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>