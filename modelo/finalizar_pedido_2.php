<?php
        require_once('inicializar_datos.php');

        $refacciones = [
            [
                'producto' => '3009012-GROB',
                'cantidad' => 2
            ],
            [
                'producto' => 'K-9802-STL',
                'cantidad' => 2
            ],
            [
                'producto' => 'LC-1-3/8',
                'cantidad' => 4
            ],
            [
                'producto' => 'LM-35.0',
                'cantidad' => 4
            ],
        ];

        var_dump($refacciones);

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

        $pedido_cliente['descuento'] = 0;
        $pedido_cliente['subtotal'] = 0;
        $pedido_cliente['codigos_pedidos'] = 0;
        $pedido_cliente['unidades_pedidas'] = 0;

        foreach( $refacciones_producto as $refaccion_producto ){
            $pedido_cliente_detalle['codigo'] = $refaccion_producto['producto']['Codigo'];
            $pedido_cliente_detalle['cantidad'] = $refaccion_producto['refaccion']['cantidad'];
            $pedido_cliente_detalle['costo'] = $refaccion_producto['producto']['Costo'];
            $pedido_cliente_detalle['precio'] = $pedido_cliente_detalle['costo'] * ( 1 + ( ( $datos['cliente']['Utilidad'] > 0 ? $datos['cliente']['Utilidad'] : $refaccion_producto['producto']['Utilidades'] ) * 0.01) );
            $pedido_cliente_detalle['importe'] = $pedido_cliente_detalle['precio'] * $pedido_cliente_detalle['cantidad'];

            //Descuentos
            $pedido_cliente_detalle['descuento'] = $datos['cliente']['DescuentoUniversal'];

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Sistema'] == $refaccion_producto['producto']['Sistema'] ){
                    $pedido_cliente_detalle['descuento'] =
                        (1-(
                            (1-$pedido_cliente_detalle['descuento']*0.01) *
                            (1-$descuento['DescuentoSistema']*0.01)
                        ))*100;
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Subsistema'] == $refaccion_producto['producto']['Subsistema'] ){
                    $pedido_cliente_detalle['descuento'] =
                        (1-(
                            (1-$pedido_cliente_detalle['descuento']*0.01) *
                            (1-$descuento['DescuentoSubsistema']*0.01)
                        ))*100;
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Producto'] == $refaccion_producto['producto']['Producto'] ){
                    $pedido_cliente_detalle['descuento'] =
                        (1-(
                            (1-$pedido_cliente_detalle['descuento']*0.01) *
                            (1-$descuento['DescuentoProducto']*0.01)
                        ))*100;
                    break;
                }
            }

            foreach( $datos['descuentos'] as $descuento ){
                if( $descuento['Fabricante'] == $refaccion_producto['producto']['Fabricante'] ){
                    $pedido_cliente_detalle['descuento'] =
                        (1-(
                            (1-$pedido_cliente_detalle['descuento']*0.01) *
                            (1-$descuento['DescuentoFabricante']*0.01)
                        ))*100;
                    break;
                }
            }

            $pedido_cliente['descuento'] += $pedido_cliente_detalle['importe'] * ( $pedido_cliente_detalle['descuento'] * 0.01 );
            $pedido_cliente['subtotal'] += $pedido_cliente_detalle['importe'] - ( $pedido_cliente_detalle['importe'] * ( $pedido_cliente_detalle['descuento'] * 0.01 ) );

            $pedido_cliente['codigos_pedidos'] += 1;
            $pedido_cliente['unidades_pedidas'] += $refaccion_producto['refaccion']['cantidad'];

            $pedido_cliente['detalles'][] = $pedido_cliente_detalle;
        }

        $pedido_cliente['iva'] = $pedido_cliente['subtotal'] * 0.16;
        $pedido_cliente['total'] = $pedido_cliente['subtotal'] + $pedido_cliente['iva'];


        //$preparada = $datos['conexion_base_sucursal']->prepare("INSERT INTO PedidosCliente (Folio, FechaPedido, Numerodecontrol, HoraPedido, Cliente, Vendedor, CodigosPedido, UnidadesPedido, DescuentosPedido, SubtotalPedido, IvaPedido, TotalPedido, NombreCliente, Status, Extra1, Extra2, FormaPago, Tipocomprobante, MEntrega, Observacion) VALUES (:Folio, CAST(GETDATE() AS Date), :Numerodecontrol, REPLACE(REPLACE(FORMAT(GETDATE(), 'hh:mm:ss tt'), 'AM', 'a. m.'), 'PM', 'p. m.'), :Cliente, :Vendedor, :CodigosPedido, :UnidadesPedido, CAST(:DescuentosPedido AS NUMERIC(18,2)), CAST(:SubtotalPedido AS NUMERIC(18,2)), CAST(:IvaPedido AS NUMERIC(18,2)), CAST(:TotalPedido AS NUMERIC(18,2)), :NombreCliente, :Status, :Extra1, :Extra2, :FormaPago, :Tipocomprobante, :MEntrega, :Observacion)");
        /*$preparada->bindValue(':Folio',  1);
        $preparada->bindValue(':Numerodecontrol', '1');
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
        $preparada->bindValue(':Extra1', 'WEB');*/

        foreach( $pedido_cliente['detalles'] as $detalle ){
            var_dump($detalle);
            //$preparada = $datos['conexion_base_sucursal']->prepare('INSERT INTO PedidoClientesDetalle (Folio, CodigoArticulo, CantidadPedida, CantidadSurtida, CantidadFacturada, PrecioPedido, PrecioSurtida, PrecioFacturada, ImportePedida, ImporteSurtida, ImporteFacturada, DescuentoPedida, CostoPedida, Extra2, Extra3) VALUES (:Folio, :CodigoArticulo, :CantidadPedida, 0, 0, CAST(:PrecioPedido AS NUMERIC(18,2)), 0, 0, CAST(:ImportePedida AS NUMERIC(18,2)), 0, 0, CAST(:DescuentoPedida AS NUMERIC(18,2)), CAST(:CostoPedida AS NUMERIC(18,2)), :Extra2, :Extra3)');
            /*$preparada->bindValue(':Folio', 1);
            $preparada->bindValue(':CodigoArticulo', $detalle['codigo']);
            $preparada->bindValue(':CantidadPedida', $detalle['cantidad']);
            $preparada->bindValue(':PrecioPedido', $detalle['precio']);
            $preparada->bindValue(':ImportePedida', $detalle['importe']);
            $preparada->bindValue(':DescuentoPedida', $detalle['descuento']);
            $preparada->bindValue(':CostoPedida', $detalle['costo']);*/

        }


?>