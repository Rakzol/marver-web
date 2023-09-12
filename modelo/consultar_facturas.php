<?php
    try{
        require_once('inicializar_datos.php');

        //PedidosCliente.Cliente = :clienteTemp no es necesario para esta consulta y solo se agrega para solucionar un problema donde se repiten los PedidosCliente.FolioComprobante
        //para diferentes clientes, con esto solo mostrara los del cliente actual, la verdadera solucion seria arreglar y quitar la repeticion entre el PedidosCliente.FolioComprobante
        //removiendo las duplicidades de codigos en toda la tabla, ya que si se repite un PedidosCliente.FolioComprobante para el mismo cliente no servira de nada el PedidosCliente.Cliente = :clienteTemp
        //Al igual que validar que el status no sea CA osea Cancelado que por lo mismo creo que se repiten folios reutilizandos los CA Cancelados, creo . . . :D
        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Pagos.Fecha, Pagos.FechaVencimiento, Pagos.Importe, Pagos.Abono, Pagos.Folio AS FolioComprobante, PedidosCliente.Folio AS Folio FROM Pagos INNER JOIN PedidosCliente ON PedidosCliente.FolioComprobante = Pagos.Folio AND PedidosCliente.Cliente = :clienteTemp AND PedidosCliente.Status != 'CA' WHERE Pagos.Cliente = :cliente AND Pagos.Saldado = 0 ORDER BY Pagos.Fecha");
        $preparada->bindValue(':clienteTemp', $datos['cliente']['Clave']);
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();
        $resultados[] = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT SUM(Importe) FROM Pagos WHERE Cliente = :cliente AND Saldado = 0");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();
        $resultados[] = $preparada->fetchColumn();

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT SUM(Abono) FROM Pagos WHERE Cliente = :cliente AND Saldado = 0");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();
        $resultados[] = $preparada->fetchColumn();

        echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>