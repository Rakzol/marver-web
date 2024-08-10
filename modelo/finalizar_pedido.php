<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_catalogo_sucursal']->prepare("SELECT producto, cantidad FROM carrito WHERE usuario = :usuario");
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->execute();

        $refacciones = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($refacciones) == 0 ){
            $retorno['codigo'] = -1;
            echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Recorremos cada refaccion del carrito del usuario y verificamos que se tengan existencias suficientes
        // Si no regresamos un json con el error y finalizamos el codigo
        $retorno['productos_insuficientes'] = [];

        foreach( $refacciones as $refaccion ){
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT * FROM Producto WHERE Codigo = :producto");
            $preparada->bindValue(':producto', $refaccion['producto']);
            $preparada->execute();

            $refaccion_producto['refaccion'] = $refaccion;
            $producto = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

            if( $producto['Existencia'] < $refaccion['cantidad'] ){
                $producto_insuficiente['producto'] = $refaccion['producto'];
                $producto_insuficiente['existencias'] = $producto['Existencia'];
                $retorno['productos_insuficientes'][] = $producto_insuficiente;
            }

            $refaccion_producto['producto'] = $producto;

            $refacciones_producto[] = $refaccion_producto;
        }

        if( count($retorno['productos_insuficientes']) ){
            $retorno['codigo'] = 1;
            echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Validamos que el metodo de pago y el tipo de comprobante sean validos juntos
        switch( $datos['cliente']['Credito'] ){
            case 1:
                $tipos_de_compra['1'] = 'contado';
                $tipos_de_compra['2'] = 'creadito';
                break;
            case 0:
                $tipos_de_compra['1'] = 'contado';
                break;
        }

        switch ($_POST['tipo_de_compra']){
            case '1'://contado
                $formas_de_pago['01'] = 'efectivo';
                $formas_de_pago['02'] = 'cheque nominativo';
                $formas_de_pago['03'] = 'transferencia electronica';
                $formas_de_pago['04'] = 'tarjeta de credito';
                $formas_de_pago['28'] = 'tarjeta de débito';

                $tipos_de_comprobante['1'] = 'factura';
                $tipos_de_comprobante['2'] = 'recibo';
                break;
            case '2'://credito
                $formas_de_pago['99'] = 'credito';

                $tipos_de_comprobante['1'] = 'factura';
                if( $datos['cliente']['Preventa'] == 1 ){
                    $tipos_de_comprobante['3'] = 'preventa';
                }
                break;
            default:
                throw new Exception('Informacion del pedido invalida.');
                break;
        }

        $MEntregas['MOTO'] = 'MOTO';
        $MEntregas['CAMIONETA'] = 'CAMIONETA';
        $MEntregas['PERSONAL'] = 'PERSONAL';

        if(!array_key_exists($_POST['forma_de_pago'], $formas_de_pago) ||
            !array_key_exists($_POST['tipo_de_comprobante'], $tipos_de_comprobante) ||
            !array_key_exists($_POST['tipo_de_compra'], $tipos_de_compra) ||
            !array_key_exists($_POST['MEntrega'], $MEntregas) ){
            throw new Exception('Formas de pago invalidas.');
        }

        //
        $pedido_cliente['descuento'] = 0;
        $pedido_cliente['subtotal'] = 0;
        $pedido_cliente['codigos_pedidos'] = 0;
        $pedido_cliente['unidades_pedidas'] = 0;

        foreach( $refacciones_producto as $refaccion_producto ){
            $pedido_cliente_detalle['codigo'] = $refaccion_producto['producto']['Codigo'];
            $pedido_cliente_detalle['cantidad'] = $refaccion_producto['refaccion']['cantidad'];
            $pedido_cliente_detalle['costo'] = $refaccion_producto['producto']['Costo'];
            $pedido_cliente_detalle['precio'] =  number_format( $pedido_cliente_detalle['costo'] * ( 1 + number_format(( ( $datos['cliente']['Utilidad'] > 0 ? $datos['cliente']['Utilidad'] : $refaccion_producto['producto']['Utilidades'] ) * 0.01), 2, '.', '') ), 2, '.', '');
            $pedido_cliente_detalle['importe'] = number_format( $pedido_cliente_detalle['precio'] * $pedido_cliente_detalle['cantidad'], 2, '.', '' );

            //Descuentos

            // $pedido_cliente_detalle['descuento'] = $datos['cliente']['DescuentoUniversal'];
            // foreach( $datos['descuentos'] as $descuento ){
            //     if( $descuento['Sistema'] == $refaccion_producto['producto']['Sistema'] &&
            //         $descuento['Subsistema'] == $refaccion_producto['producto']['Subsistema'] &&
            //         $descuento['Producto'] == $refaccion_producto['producto']['Producto'] &&
            //         $descuento['Fabricante'] == $refaccion_producto['producto']['Fabricante'] ){
            //         $pedido_cliente_detalle['descuento'] =
            //             (1-(
            //                 (1-$pedido_cliente_detalle['descuento']*0.01) *
            //                 (1-$descuento['DescuentoSistema']*0.01) *
            //                 (1-$descuento['DescuentoSubsistema']*0.01) *
            //                 (1-$descuento['DescuentoProducto']*0.01) *
            //                 (1-$descuento['DescuentoFabricante']*0.01)
            //             ))*100;
            //     }
            // }

            $pedido_cliente_detalle['descuento'] = $datos['cliente']['DescuentoUniversal'];

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Sistema'] == $refaccion_producto['producto']['Sistema'] ){
                    $pedido_cliente_detalle['descuento'] =
                        number_format((1-(
                            number_format((1-number_format($pedido_cliente_detalle['descuento']*0.01, 2, '.', '')) *
                            (1-number_format($descuento['DescuentoSistema']*0.01, 2, '.', '')), 2, '.', '')
                        ))*100, 2, '.', '');
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Subsistema'] == $refaccion_producto['producto']['Subsistema'] ){
                    $pedido_cliente_detalle['descuento'] =
                        number_format((1-(
                            number_format((1-number_format($pedido_cliente_detalle['descuento']*0.01, 2, '.', '')) *
                            (1-number_format($descuento['DescuentoSubsistema']*0.01, 2, '.', '')), 2, '.', '')
                        ))*100, 2, '.', '');
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Producto'] == $refaccion_producto['producto']['Producto'] ){
                    $pedido_cliente_detalle['descuento'] =
                        number_format((1-(
                            number_format((1-number_format($pedido_cliente_detalle['descuento']*0.01, 2, '.', '')) *
                            (1-number_format($descuento['DescuentoProducto']*0.01, 2, '.', '')), 2, '.', '')
                        ))*100, 2, '.', '');
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Fabricante'] == $refaccion_producto['producto']['Fabricante'] ){
                    $pedido_cliente_detalle['descuento'] =
                        number_format((1-(
                            number_format((1-number_format($pedido_cliente_detalle['descuento']*0.01, 2, '.', '')) *
                            (1-number_format($descuento['DescuentoFabricante']*0.01, 2, '.', '')), 2, '.', '')
                        ))*100, 2, '.', '');
                    break;
                }
            }

            $pedido_cliente['descuento'] += number_format($pedido_cliente_detalle['importe'] * ( number_format($pedido_cliente_detalle['descuento'] * 0.01, 2, '.', '') ), 2, '.', '');
            $pedido_cliente['subtotal'] += $pedido_cliente_detalle['importe'] - ( number_format($pedido_cliente_detalle['importe'] * ( number_format($pedido_cliente_detalle['descuento'] * 0.01, 2, '.', '') ), 2, '.', '' ) );

            $pedido_cliente['codigos_pedidos'] += 1;
            $pedido_cliente['unidades_pedidas'] += $refaccion_producto['refaccion']['cantidad'];

            $pedido_cliente['detalles'][] = $pedido_cliente_detalle;
        }

        $pedido_cliente['iva'] = number_format( $pedido_cliente['subtotal'] * 0.16, 2, '.' , '' );
        $pedido_cliente['total'] = $pedido_cliente['subtotal'] + $pedido_cliente['iva'];



        //Una vez se verifico que los datos recividos son validos, verificamos si es compra por credito 

        //Y ahora si es el cliente 3 lo dejamos comprar sin limitarlo
        if( $_POST['tipo_de_compra'] == '2' &&  $datos['cliente']['Clave'] != 3 ){
            //Consultamos si tiene pagos no saldados que sobre pasaron la FechaVencimiento
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT COUNT(*) AS cantidad FROM Pagos WHERE Cliente = :cliente AND Saldado = 0 AND FechaVencimiento <= GETDATE()");
            $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
            $preparada->execute();

            $adeudos = $preparada->fetchAll(PDO::FETCH_ASSOC);

            //Verificamos si realmente tiene un pagos vencidos
            if( $adeudos[0]['cantidad'] > 0 ){
                $retorno['codigo'] = 3;
                echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
                exit();
            }

            //Consultamos su ultimo pago de credito que no este saldado y siga siendo valido para ver si lo que sigue debiendo lo deja con suficiente credito para pagar este pedido
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT SUM(Importe) - SUM(Abono) AS Debe FROM Pagos WHERE Cliente = :cliente AND Saldado = 0");
            $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
            $preparada->execute();

            $pagos = $preparada->fetchAll(PDO::FETCH_ASSOC);

            //Verificamos si realmente tiene un pago pendiente valido sin vencer
            if( count($pagos) ){
                //De ese pago pendiente calculamos cuanto le falta por pagar y se lo restamos a su limite de credito
                $debe = $pagos[0]['Debe'];
                $saldo = $datos['cliente']['LimiteCredito'] - $debe;

                if( $saldo < $pedido_cliente['total'] ){
                    $retorno['codigo'] = 2;
                    echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
                    exit();
                }
            }
        }

        //Calculamos el ultimo folio
        $preparada = $datos['conexion_base_sucursal']->prepare('SELECT TOP 1 Folio FROM PedidosCliente ORDER BY Folio DESC');
        $preparada->execute();
    
        $ultimos_pedidos = $preparada->fetchAll(PDO::FETCH_ASSOC);

        $ultimo_folio = 0;
        if( count($ultimos_pedidos) ){
            $ultimo_folio = $ultimos_pedidos[0]['Folio'];
        }

        $preparada = $datos['conexion_base_sucursal']->prepare("INSERT INTO PedidosCliente (Folio, FechaPedido, Numerodecontrol, HoraPedido, Cliente, Vendedor, CodigosPedido, UnidadesPedido, DescuentosPedido, SubtotalPedido, IvaPedido, TotalPedido, NombreCliente, Status, Extra1, Extra2, FormaPago, Tipocomprobante, MEntrega, Observacion) VALUES (:Folio, CAST(GETDATE() AS Date), :Numerodecontrol, REPLACE(REPLACE(FORMAT(GETDATE(), 'hh:mm:ss tt'), 'AM', 'a. m.'), 'PM', 'p. m.'), :Cliente, :Vendedor, :CodigosPedido, :UnidadesPedido, CAST(:DescuentosPedido AS NUMERIC(18,2)), CAST(:SubtotalPedido AS NUMERIC(18,2)), CAST(:IvaPedido AS NUMERIC(18,2)), CAST(:TotalPedido AS NUMERIC(18,2)), :NombreCliente, :Status, :Extra1, :Extra2, :FormaPago, :Tipocomprobante, :MEntrega, :Observacion)");
        $preparada->bindValue(':Folio', $ultimo_folio + 1);
        //$preparada->bindValue(':FechaPedido', '');
        $preparada->bindValue(':Numerodecontrol', '1');
        //$preparada->bindValue(':HoraPedido', '');
        $preparada->bindValue(':Cliente', $datos['cliente']['Clave']);
        $preparada->bindValue(':Vendedor', $datos['usuario']['vendedor']);
        $preparada->bindValue(':CodigosPedido', $pedido_cliente['codigos_pedidos']);
        $preparada->bindValue(':UnidadesPedido', $pedido_cliente['unidades_pedidas']);
        $preparada->bindValue(':DescuentosPedido', $pedido_cliente['descuento']);
        $preparada->bindValue(':SubtotalPedido', $pedido_cliente['subtotal']);
        $preparada->bindValue(':IvaPedido', $pedido_cliente['iva']);
        $preparada->bindValue(':TotalPedido', $pedido_cliente['total']);
        $preparada->bindValue(':NombreCliente', $datos['cliente']['Razon_Social']);
        $preparada->bindValue(':Status', 'C');
        $preparada->bindValue(':Extra1', 'WEB');
        $preparada->bindValue(':Extra2', $_POST['tipo_de_compra']);
        $preparada->bindValue(':FormaPago', $_POST['forma_de_pago']);
        $preparada->bindValue(':Tipocomprobante', $_POST['tipo_de_comprobante']);
        $preparada->bindValue(':MEntrega', $_POST['MEntrega']);
        $preparada->bindValue(':Observacion', $_POST['observaciones']);
        $preparada->execute();

        foreach( $pedido_cliente['detalles'] as $detalle ){
            $preparada = $datos['conexion_base_sucursal']->prepare('INSERT INTO PedidoClientesDetalle (Folio, CodigoArticulo, CantidadPedida, CantidadSurtida, CantidadFacturada, PrecioPedido, PrecioSurtida, PrecioFacturada, ImportePedida, ImporteSurtida, ImporteFacturada, DescuentoPedida, CostoPedida, Extra2, Extra3) VALUES (:Folio, :CodigoArticulo, :CantidadPedida, 0, 0, CAST(:PrecioPedido AS NUMERIC(18,2)), 0, 0, CAST(:ImportePedida AS NUMERIC(18,2)), 0, 0, CAST(:DescuentoPedida AS NUMERIC(18,2)), CAST(:CostoPedida AS NUMERIC(18,2)), :Extra2, :Extra3)');
            $preparada->bindValue(':Folio', $ultimo_folio + 1);
            $preparada->bindValue(':CodigoArticulo', $detalle['codigo']);
            $preparada->bindValue(':CantidadPedida', $detalle['cantidad']);
            $preparada->bindValue(':PrecioPedido', $detalle['precio']);
            $preparada->bindValue(':ImportePedida', $detalle['importe']);
            $preparada->bindValue(':DescuentoPedida', $detalle['descuento']);
            $preparada->bindValue(':CostoPedida', $detalle['costo']);
            $preparada->bindValue(':Extra2', $_POST['tipo_de_compra']);
            $preparada->bindValue(':Extra3', $_POST['tipo_de_comprobante']);
            $preparada->execute();
        }

        $preparada = $datos['conexion_catalogo_sucursal']->prepare('DELETE FROM carrito WHERE usuario = :usuario');
        $preparada->bindValue(':usuario', $datos['usuario']['id']);
        $preparada->execute();

        if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ){
            $ip_list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if( !filter_var($ip, FILTER_VALIDATE_IP) ){
            $ip = '0.0.0.0';
        }

        try{
            /*
            $url = "https://ipinfo.io/{$ip}?token=a39ff8f192d166";

            $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($curl);

            if ($response != false) {
                $data = json_decode($response, true);

                $location = explode(',', $data['loc']);

                $lat_api = $location[0];
                $lon_api = $location[1];
            }else{
                $lat_api = null;
                $lon_api = null;
            }

            curl_close($curl);
            */

        }catch(Exception $ex){
            $lat_api = null;
            $lon_api = null;
        }

        $preparada = $datos['conexion_catalogo_principal']->prepare('INSERT INTO ubicaciones_usuarios VALUES (:usuario, :ip, :lat_nav, :lon_nav, :lat_api, :lon_api, :precision, GETDATE(), :pedido)');
        $preparada->bindValue(':usuario', $datos['usuario']['id'] );
        $preparada->bindValue(':ip', $ip);
        $preparada->bindValue(':lat_nav', $_POST['lat_nav']);
        $preparada->bindValue(':lon_nav', $_POST['lon_nav']);
        $preparada->bindValue(':lat_api', $lat_api);
        $preparada->bindValue(':lon_api', $lon_api);
        $preparada->bindValue(':precision', $_POST['precision']);
        $preparada->bindValue(':pedido', $ultimo_folio + 1);
        $preparada->execute();

        $retorno['codigo'] = 0;
        $retorno['folio'] = $ultimo_folio + 1;
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>