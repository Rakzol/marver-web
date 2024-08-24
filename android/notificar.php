<?php

    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
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

        $preparada = $conexion->prepare("SELECT EnvioPedidoCliente.Responsable, EnvioPedidoCliente.Pedido, PedidosCliente.Cliente, PedidosCliente.Vendedor FROM EnvioPedidoCliente INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido WHERE PedidosCliente.FolioComprobante = :folio AND PedidosCliente.Tipocomprobante = :comprobante;");
        $preparada->bindValue(':folio', $_POST['folio']);
        $preparada->bindValue(':comprobante', $_POST['comprobante']);
        $preparada->execute();

        $pedido = $preparada->fetchAll(PDO::FETCH_ASSOC);
        if( count($pedido) == 0 ){
            $resultado["status"] = 2;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " no esta asignado";
            echo json_encode($resultado);
            exit();
        }

        if( $pedido[0]['Responsable'] != $_POST['clave'] ){
            $resultado["status"] = 3;
            $resultado["mensaje"] = "El pedido con el folio: " . $_POST['folio'] . " ya esta asignado al repartidor: " . $pedido[0]['Responsable'];
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare("SELECT Razon_Social, Celular FROM Clientes WHERE Clave = :cliente");
        $preparada->bindValue(':cliente', $pedido[0]['Cliente']);
        $preparada->execute();
        $cliente = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        $preparada = $conexion->prepare("SELECT Clave, Nombre, Celular FROM Vendedores WHERE Clave = :vendedor");
        $preparada->bindValue(':vendedor', $pedido[0]['Vendedor']);
        $preparada->execute();
        $vendedor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        $preparada = $conexion->prepare("SELECT Clave, Nombre, Celular FROM Vendedores WHERE Clave = :repartidor");
        $preparada->bindValue(':repartidor', $pedido[0]['Responsable']);
        $preparada->execute();
        $repartidor = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

        notificar($cliente['Celular'], $cliente, $vendedor, $repartidor);
        notificar($vendedor['Celular'], $cliente, $vendedor, $repartidor);
        notificar($repartidor['Celular'], $cliente, $vendedor, $repartidor);
        notificar('6681134724', $cliente, $vendedor, $repartidor);

        $resultado["status"] = 0;
        $resultado["mensaje"] = "Cliente notificado";
        echo json_encode($resultado);
    }
    catch(Exception $ex){
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al notificar al cliente" . $ex->getMessage();
        echo json_encode($resultado);
    }

    function notificar($celular, $cliente, $vendedor, $repartidor){
        if( is_null($celular) ){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "Error al notificar, no tiene numero celular";
            echo json_encode($resultado);
            exit();
        }
        if( $celular == '' ){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "Error al notificar, no tiene numero celular";
            echo json_encode($resultado);
            exit();
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
                                    "text": "' . $cliente['Razon_Social'] . '"
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
                                    "text": "' . ( $_POST['comprobante'] == 1 ? 'Factura' : ( $_POST['comprobante'] == 2 ? 'Recibo' : ( $_POST['comprobante'] == 5 ? 'Preventa' : '' ) ) ) . '"
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

        $response = json_decode(curl_exec($curl), true);

        if( $response == false ){
            $resultado["status"] = 4;
            $resultado["mensaje"] = "Error al notificar";
            echo json_encode($resultado);
            exit();
        }

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if( $status_code != 200 ){
            $resultado["status"] = 5;
            $resultado["mensaje"] = "Error al notificar";
            echo json_encode($resultado);
            exit();
        }

        curl_close($curl);
    }
?>