<?php
        require_once('inicializar_datos.php');

        header('Content-Type: text/html');

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
    
            echo ':Numerodecontrol -> ' . '1' . '</br>';
            echo ':Cliente -> ' . $datos['cliente']['Clave'] . '</br>';
            echo ':Vendedor -> ' . $datos['usuario']['vendedor'] . '</br>';
            echo ':CodigosPedido -> ' . $pedido_cliente['codigos_pedidos'] . '</br>';
            echo ':UnidadesPedido -> ' . $pedido_cliente['unidades_pedidas'] . '</br>';
            echo ':DescuentosPedido -> ' . $pedido_cliente['descuento'] . '</br>';
            echo ':SubtotalPedido -> ' . $pedido_cliente['subtotal'] . '</br>';
            echo ':IvaPedido -> ' . $pedido_cliente['iva'] . '</br>';
            echo ':TotalPedido -> ' . $pedido_cliente['total'] . '</br>';
            echo ':NombreCliente -> ' . $datos['cliente']['Razon_Social'] . '</br>';
            echo ':Status -> ' . 'C' . '</br>';
            echo ':Extra1 -> ' . 'WEB' . '</br>';
    
            foreach( $pedido_cliente['detalles'] as $detalle ){
                echo '::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::</br>';
                echo ':CodigoArticulo -> ' . $detalle['codigo'] . '</br>';
                echo ':CantidadPedida -> ' . $detalle['cantidad'] . '</br>';
                echo ':PrecioPedido -> ' . $detalle['precio'] . '</br>';
                echo ':ImportePedida -> ' . $detalle['importe'] . '</br>';
                echo ':DescuentoPedida -> ' . $detalle['descuento'] . '</br>';
                echo ':CostoPedida -> ' . $detalle['costo'] . '</br>';
            }

?>