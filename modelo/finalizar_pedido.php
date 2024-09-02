<?php

try {
    require_once('inicializar_datos.php');

    date_default_timezone_set('America/Mazatlan');

    // Consulta los productos en el carrito del usuario
    $preparada = $datos['conexion_catalogo_sucursal']->prepare("SELECT producto, cantidad FROM carrito WHERE usuario = :usuario");
    $preparada->bindValue(':usuario', $datos['usuario']['id']);
    $preparada->execute();
    $refacciones = $preparada->fetchAll(PDO::FETCH_ASSOC);

    if (count($refacciones) == 0) {
        header("Location: http://localhost?error=no_products");
        exit();
    }   

    // Inicializa arrays
    $retorno['productos_insuficientes'] = [];
    $refacciones_producto = [];

    // Verifica la disponibilidad de los productos
    foreach ($refacciones as $refaccion) {
        $preparada = $datos['conexion_base_sucursal']->prepare("
            SELECT Codigo, Costo, Existencia, Descripcion, Fabricante, Localizacion 
            FROM Producto 
            WHERE Codigo = :producto
        ");
        $preparada->bindValue(':producto', $refaccion['producto']);
        $preparada->execute();
        $producto = $preparada->fetch(PDO::FETCH_ASSOC);

        if ($producto === false || !isset($producto['Codigo'])) {
            throw new Exception('Producto no encontrado o clave Codigo no existe: ' . $refaccion['producto']);
        }

        $refacciones_producto[] = ['refaccion' => $refaccion, 'producto' => $producto];

        if ($producto['Existencia'] < $refaccion['cantidad']) {
            $producto_insuficiente = [
                'producto' => $refaccion['producto'],
                'existencias' => $producto['Existencia']
            ];
            $retorno['productos_insuficientes'][] = $producto_insuficiente;
        }
    }

    if (count($retorno['productos_insuficientes']) > 0) {
        header("Location: http://localhost?error=insufficient_products");
        exit();
    }

    $tipos_de_compra = ($datos['cliente']['Credito'] == 1) ? ['1' => 'contado', '2' => 'credito'] : ['1' => 'contado'];

    $formas_de_pago = [];
    $tipos_de_comprobante = [];
    switch ($_POST['tipo_de_compra']) {
        case '1':
            $formas_de_pago = [
                '01' => 'efectivo',
                '02' => 'cheque nominativo',
                '03' => 'transferencia electronica',
                '04' => 'tarjeta de credito',
                '28' => 'tarjeta de debito'
            ];
            $tipos_de_comprobante = [
                '1' => 'factura',
                '2' => 'recibo'
            ];
            break;
        case '2':
            $formas_de_pago = ['99' => 'credito'];
            $tipos_de_comprobante = ['1' => 'factura'];
            if ($datos['cliente']['Preventa'] == 1) {
                $tipos_de_comprobante['3'] = 'preventa';
            }
            break;
        default:
            throw new Exception('Información del pedido inválida.');
    }

    $MEntregas = [
        'MOTO' => 'MOTO',
        'CAMIONETA' => 'CAMIONETA',
        'PERSONAL' => 'PERSONAL'
    ];

    if (
        !array_key_exists($_POST['forma_de_pago'], $formas_de_pago) ||
        !array_key_exists($_POST['tipo_de_comprobante'], $tipos_de_comprobante) ||
        !array_key_exists($_POST['tipo_de_compra'], $tipos_de_compra) ||
        !array_key_exists($_POST['MEntrega'], $MEntregas)
    ) {
        header("Location: http://localhost?error=invalid_payment_methods");
        exit();
    }

    $pedido_cliente = [
        'descuento' => 0,
        'subtotal' => 0,
        'codigos_pedidos' => 0,
        'unidades_pedidas' => 0,
        'detalles' => []
    ];

    foreach ($refacciones_producto as $refaccion_producto) {
        $producto = $refaccion_producto['producto'];
        $refaccion = $refaccion_producto['refaccion'];

        $precio = number_format(
            $producto['Costo'] * (1 + number_format(
                ($datos['cliente']['Utilidad'] > 0 ? $datos['cliente']['Utilidad'] : $producto['Utilidades']) * 0.01,
                2,
                '.',
                ''
            )),
            2,
            '.',
            ''
        );
        $importe = number_format(
            $precio * $refaccion['cantidad'],
            2,
            '.',
            ''
        );

        $pedido_cliente_detalle = [
            'codigoarticulo' => $producto['Codigo'],
            'cantidadpedida' => $refaccion['cantidad'],
            'costopedida' => $producto['Costo'],
            'precio' => $precio,
            'importe' => $importe,
            'descuento' => $datos['cliente']['DescuentoUniversal'],
            'descripcion' => $producto['Descripcion'],
            'fabricante' => $producto['Fabricante'],
            'localizacion' => $producto['Localizacion']
        ];

        foreach (['Sistema', 'Subsistema', 'Producto', 'Fabricante'] as $campo) {
            foreach ($datos['descuentos'] as $descuento) {
                if (isset($descuento[$campo]) && $descuento[$campo] == $producto[$campo]) {
                    $pedido_cliente_detalle['descuento'] = number_format(
                        (1 - number_format(
                            (1 - number_format($pedido_cliente_detalle['descuento'] * 0.01, 2, '.', '')) *
                                (1 - number_format($descuento["Descuento{$campo}"] * 0.01, 2, '.', '')),
                            2,
                            '.',
                            ''
                        )) * 100,
                        2,
                        '.',
                        ''
                    );
                    break;
                }
            }
        }

        $pedido_cliente['descuento'] += number_format(
            $pedido_cliente_detalle['importe'] * (number_format($pedido_cliente_detalle['descuento'] * 0.01, 2, '.', '')),
            2,
            '.',
            ''
        );
        $pedido_cliente['subtotal'] += $pedido_cliente_detalle['importe'] - $pedido_cliente['descuento'];
        $pedido_cliente['codigos_pedidos'] += 1;
        $pedido_cliente['unidades_pedidas'] += $refaccion['cantidad'];
        $pedido_cliente['detalles'][] = $pedido_cliente_detalle;
    }

    $pedido_cliente['iva'] = number_format($pedido_cliente['subtotal'] * 0.16, 2, '.', '');
    $pedido_cliente['total'] = $pedido_cliente['subtotal'] + $pedido_cliente['iva'];

    // Ordena los productos por localización
    usort($pedido_cliente['detalles'], function ($a, $b) {
        return strcmp($a['localizacion'], $b['localizacion']);
    });

    $preparada = $datos['conexion_base_sucursal']->prepare('SELECT TOP 1 Folio FROM PedidosCliente ORDER BY Folio DESC');
    $preparada->execute();
    $ultimos_pedidos = $preparada->fetch(PDO::FETCH_ASSOC);
    $ultimo_folio = $ultimos_pedidos ? $ultimos_pedidos['Folio'] : 0;

    $preparada = $datos['conexion_base_sucursal']->prepare('
        INSERT INTO PedidosCliente (
            Folio,
            FechaPedido,
            HoraPedido,
            SubTotalPedido,
            DescuentosPedido,
            IvaPedido,
            TotalPedido,
            NumerodeControl,
            FormaPago,
            TipoComprobante,
            MEntrega,
            Unidades,
            Status
        ) VALUES (
            :folio,
            GETDATE(),
            CONVERT(VARCHAR(8), GETDATE(), 108),
            :subtotal,
            :descuento,
            :iva,
            :total,
            :tipo_de_compra,
            :forma_de_pago,
            :tipo_de_comprobante,
            :mentrega,
            :unidades_pedidas,
            1
        )
    ');

    $pedido_insercion = [
        ':folio' => $ultimo_folio + 1,
        ':subtotal' => $pedido_cliente['subtotal'],
        ':descuento' => $pedido_cliente['descuento'],
        ':iva' => $pedido_cliente['iva'],
        ':total' => $pedido_cliente['total'],
        ':tipo_de_compra' => $_POST['tipo_de_compra'],
        ':forma_de_pago' => $_POST['forma_de_pago'],
        ':tipo_de_comprobante' => $_POST['tipo_de_comprobante'],
        ':mentrega' => $_POST['MEntrega'],
        ':unidades_pedidas' => $pedido_cliente['unidades_pedidas']
    ];

    $preparada->execute($pedido_insercion);

    // Obtener las observaciones del formulario
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';

    // Función para generar el código de barras en formato ESC/POS
    function generarCodigoDeBarras($texto)
    {
        $codigo = "*$texto*"; // Formato simple de código de barras (Code 128)
        
        // Comandos ESC/POS para imprimir código de barras
        $barra = "\x1B\x40"; // Inicializa la impresora
        $barra .= "\x1D\x6B\x49"; // Comando para código de barras Code 128
        $barra .= chr(strlen($texto) + 2); // Longitud del texto
        $barra .= $texto; // Texto del código de barras
        $barra .= "\x00"; // Terminador de código de barras

        return $barra;
    }

    // Genera el contenido del ticket
    $ticket = '';
    $ticket .= "==========================================\n";
    $ticket .= str_pad("       MARVER REFACCIONES       ", 40, " ", STR_PAD_BOTH) . "\n\n";
    $ticket .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $ticket .= "Folio: " . str_pad($ultimo_folio + 1, 6, " ", STR_PAD_LEFT) . "\n";
    $ticket .= "Tipo de Compra: " . str_pad($tipos_de_compra[$_POST['tipo_de_compra']], 12) . "\n";
    $ticket .= "Forma de Pago: " . str_pad($formas_de_pago[$_POST['forma_de_pago']], 22) . "\n";
    $ticket .= "Metodo de Entrega: " . str_pad($MEntregas[$_POST['MEntrega']], 15) . "\n";
    $ticket .= "------------------------------------------\n";

    foreach ($pedido_cliente['detalles'] as $detalle) {
        // Usa caracteres para simular negrita
        $ticket .= "\x1B\x21\x08";
        $ticket .= str_pad("Codigo: " . $detalle['codigoarticulo'], 12) . " ";
        $ticket .= str_pad("Localizacion: " . $detalle['localizacion'], 12) . "\n";
        $ticket .= str_pad("Fabricante: " . $detalle['fabricante'], 40) . "\n"; // Añadir línea para el fabricante
        $ticket .= "\x1B\x21\x00"; // Esc/POS: restablece el estilo de fuente
        $ticket .= str_pad($detalle['descripcion'], 40) . "\n";
        $ticket .= str_pad("Cant: " . $detalle['cantidadpedida'], 16);
        $ticket .= " Importe: $" . str_pad($detalle['importe'], 8, " ", STR_PAD_LEFT) . "\n";
        $ticket .= "------------------------------------------\n";
    }
    $ticket .= "Subtotal: $" . str_pad($pedido_cliente['subtotal'], 14, " ", STR_PAD_LEFT) . "\n";
    $ticket .= "Descuento: $" . str_pad($pedido_cliente['descuento'], 14, " ", STR_PAD_LEFT) . "\n";
    $ticket .= "IVA: $" . str_pad($pedido_cliente['iva'], 18, " ", STR_PAD_LEFT) . "\n";
    $ticket .= "Total: $" . str_pad($pedido_cliente['total'], 14, " ", STR_PAD_LEFT) . "\n";
    $ticket .= "------------------------------------------\n";

    // Agrega las observaciones al final del ticket
    if ($observaciones) {
        $ticket .= "Observaciones:\n";
        $ticket .= str_pad($observaciones, 40) . "\n";
    }

    // Espacio en blanco para el corte del ticket
    $ticket .= "\n\n\n\n\n\n\n\n\n\n\n\n\n";

    function imprimirTicket($ticket, $ip, $puerto) {
        $conexion = fsockopen($ip, $puerto, $errno, $errstr, 30);
        if (!$conexion) {
            throw new Exception("No se pudo conectar a la impresora: $errstr ($errno)");
        }
        fwrite($conexion, $ticket);
        fclose($conexion);
    }

    imprimirTicket($ticket, "10.10.10.104", 9100);

    // Redirige al usuario a la página de éxito
    header("Location: http://localhost?success=true");

} catch (Exception $e) {
    // Redirige a la página de error con el mensaje de excepción
    header("Location: http://localhost?error=" . urlencode($e->getMessage()));
}
?>
