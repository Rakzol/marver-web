<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Pagos.FechaVencimiento, Pagos.Importe, Pagos.Abono, PedidosCliente.Folio AS Folio FROM Pagos INNER JOIN PedidosCliente ON PedidosCliente.FolioComprobante = Pagos.Folio WHERE Pagos.Cliente = :cliente AND Pagos.Saldado = 0 ORDER BY Pagos.Fecha");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();
        $resultados[] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT SUM(Pagos.Importe) - SUM(Pagos.Abono) WHERE Pagos.Cliente = :cliente AND Pagos.Saldado = 0");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();
        $resultados[] = $preparada->fetchColumn();

        echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>