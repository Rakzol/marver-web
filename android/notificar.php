<?php

    try{
        header('Content-Type: application/json');

        switch($_POST["sucursal"]){
            case "Mochis":
                $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
            case "Guasave":
                $conexion = new PDO('sqlsrv:Server=12.12.12.254;Database=Guasave;TrustServerCertificate=true','MARITE','2505M$RITE');
                break;
        }
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        /* Verificamos que exista el vendedor */
        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['clave']);
        $preparada->bindValue(':contrasena', $_POST['contraseña']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            $resultado["mensaje"] = "El vendedor no existe";
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("
            SELECT
                PedidosCliente.Vendedor,
                PedidosCliente.Cliente
            FROM
                PedidosCliente
                INNER JOIN EnvioPedidoCliente
                    ON EnvioPedidoCliente.Pedido = PedidosCliente.Folio
                    AND EnvioPedidoCliente.Extra2 = 'ENTREGADO'
                    AND EnvioPedidoCliente.Responsable = :responsable
            WHERE
                PedidosCliente.FolioComprobante = :folio AND
                PedidosCliente.Tipocomprobante = :comprobante;
        ");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':comprobante', $_POST['comprobante']);
        $preparada->bindValue(':responsable', $_POST['clave']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        if( $_POST['comprobante'] > 0 ){
            $preparada = $conexion->prepare("SELECT Razon_Social AS Nombre, Celular FROM Clientes WHERE Clave = :cliente");
            $preparada->bindValue(':cliente', $pedido['Cliente']);
            $preparada->execute();
            $cliente = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
        }else{
            $preparada = $conexion->prepare("SELECT nombre AS Nombre, celular AS Celular FROM ubicaciones_especiales WHERE clave = :cliente");
            $preparada->bindValue(':cliente', $pedido['Cliente']);
            $preparada->execute();
            $cliente = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
        }

        $preparada = $conexion->prepare("SELECT Clave, Nombre, Celular FROM Vendedores WHERE Clave = :vendedor");
        $preparada->bindValue(':vendedor', $pedido['Vendedor']);
        $preparada->execute();
        $vendedor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        $preparada = $conexion->prepare("SELECT Clave, Nombre, Celular FROM Vendedores WHERE Clave = :repartidor");
        $preparada->bindValue(':repartidor', $_POST['clave']);
        $preparada->execute();
        $repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        notificar($cliente['Celular'], $cliente, $vendedor, $repartidor, true);
        notificar($vendedor['Celular'], $cliente, $vendedor, $repartidor, false);
        notificar($repartidor['Celular'], $cliente, $vendedor, $repartidor, false);

        switch($_POST["sucursal"]){
            case "Mochis":
                $preparada = $conexion->prepare("SELECT Celular FROM Vendedores WHERE Clave = 3");
                $preparada->execute();
                $jesus = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
        
                $preparada = $conexion->prepare("SELECT Celular FROM Vendedores WHERE Clave = 13");
                $preparada->execute();
                $papas = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
        
                $preparada = $conexion->prepare("SELECT Celular FROM Vendedores WHERE Clave = 32");
                $preparada->execute();
                $guillermo = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
        
                notificar($jesus['Celular'], $cliente, $vendedor, $repartidor, false);
                notificar($papas['Celular'], $cliente, $vendedor, $repartidor, false);
                notificar($guillermo['Celular'], $cliente, $vendedor, $repartidor, false);
                break;
            case "Guasave":
                break;
        }

        $resultado["status"] = 0;
        $resultado["mensaje"] = "Cliente notificado";
        echo json_encode($resultado);
    }
    catch(Exception $ex){
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al notificar al cliente" . $ex->getMessage();
        echo json_encode($resultado);
    }

    function notificar($celular, $cliente, $vendedor, $repartidor, $validar){
        if( !$celular ){
            if($validar){
                $resultado["status"] = 4;
                $resultado["mensaje"] = "Error al notificar, el Cliente no tiene numero celular";
                echo json_encode($resultado);
                exit();
            }
            return;
        }

        $Bearer = "EAAVE5rJaMKwBO980nYpnZCI4PiJVcssTkhplxFLNvvyUVdFvwqd5m5JMPbLZCA6XxWpNANrd9QoRNPsk6WhQZBhfvcFsps5a1Bp7PHWSkhycZCwb31GH2BkupUPySiyi0ZA1gE9mdL0SZBPWEJonpZAVkoZCjPg2XZCgU6dLZAzgP1UcGKaiUelN8s9jCDoZBi0FtKq";
        //URL A DONDE SE MANDARA EL MENSAJE
        $url = 'https://graph.facebook.com/v20.0/426242453898687/messages';

        //CONFIGURACION DEL MENSAJE
        $mensaje =
                '{
                "messaging_product": "whatsapp", 
                "to": "52'. $celular .'", 
                "type": "template", 
                "template": 
                {
                    "name": "envio_camion_administrador",
                    "language":{ "code": "es_MX" } ,
                    "components": [
                            {
                                "type": "body",
                                "parameters": [
                                {
                                    "type": "text",
                                    "text": "' . $cliente['Nombre'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $_POST['llegada'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $_POST['camion'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $_POST['folio'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . ( $_POST['comprobante'] == 1 ? 'Factura' : ( $_POST['comprobante'] == 2 ? 'Recibo' : ( $_POST['comprobante'] == 5 ? 'Preventa' : 'Especial' ) ) ) . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $vendedor['Clave'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $vendedor['Nombre'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $repartidor['Clave'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $repartidor['Nombre'] . '"
                                }]
                            }]
                    } 
                }';


        $header = array("Authorization: Bearer " . $Bearer, "Content-Type: application/json");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            if($validar){
                $resultado["status"] = 6;
                $resultado["mensaje"] = "Error al notificar " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            return;
        }

        if( !$response ){
            if($validar){
                $resultado["status"] = 6;
                $resultado["mensaje"] = "Error al notificar " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            return;
        }

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if( $status_code != 200 ){
            if($validar){
                $resultado["status"] = 6;
                $resultado["mensaje"] = "Error al notificar " . curl_error($curl);
                echo json_encode($resultado);
                exit();
            }
            return;
        }

        curl_close($curl);
    }
?>