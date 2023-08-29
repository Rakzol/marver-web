<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Folio, FechaPedido, HoraPedido, TotalPedido, Status FROM PedidosCliente WHERE Cliente = :cliente ORDER BY Folio DESC");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>