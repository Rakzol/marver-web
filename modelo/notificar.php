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

        $preparada = $conexion->prepare("SELECT EnvioPedidoCliente.Responsable, EnvioPedidoCliente.Pedido, PedidosCliente.Cliente FROM EnvioPedidoCliente INNER JOIN PedidosCliente ON PedidosCliente.Folio = EnvioPedidoCliente.Pedido WHERE PedidosCliente.FolioComprobante = :folio AND PedidosCliente.Tipocomprobante = :comprobante;");
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

        $Bearer = "EAAVE5rJaMKwBO980nYpnZCI4PiJVcssTkhplxFLNvvyUVdFvwqd5m5JMPbLZCA6XxWpNANrd9QoRNPsk6WhQZBhfvcFsps5a1Bp7PHWSkhycZCwb31GH2BkupUPySiyi0ZA1gE9mdL0SZBPWEJonpZAVkoZCjPg2XZCgU6dLZAzgP1UcGKaiUelN8s9jCDoZBi0FtKq";
        //URL A DONDE SE MANDARA EL MENSAJE
        $url = 'https://graph.facebook.com/v20.0/426242453898687/messages';

        //CONFIGURACION DEL MENSAJE
        $mensaje =
                '{
                "messaging_product": "whatsapp", 
                "to": "52'. $cliente['Celular'] .'", 
                "type": "template", 
                "template": 
                {
                    "name": "envio_por_camion",
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
                                    "text": "' . $_POST['folio'] . '"
                                },
                                {
                                    "type": "text",
                                    "text": "' . $_POST['camion'] . '"
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
            $resultado["mensaje"] = "Error al notificar al cliente";
            echo json_encode($resultado);
        }

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if( $status_code == 200 ){
            $resultado["status"] = 5;
            $resultado["mensaje"] = "Error al notificar al cliente";
            echo json_encode($resultado);
        }

        curl_close($curl);

        $resultado["status"] = 0;
        $resultado["mensaje"] = "Cliente notificado";
        echo json_encode($resultado);
    }
    catch(Exception $ex){
        $resultado["status"] = 6;
        $resultado["mensaje"] = "Error al notificar al cliente";
        echo json_encode($resultado);
    }
?>