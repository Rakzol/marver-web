<?php
    ob_start();
    require_once('modelo/inicializar_datos.php');
    header("Content-Type: text/html");

    $preparada = $datos['conexion_base_sucursal']->prepare("SELECT TOP 1 Cajero, Vendedor, Fecha, Hora, Cliente FROM Ventas WHERE Folio = :folio");
    $preparada->bindValue(':folio', $_GET['folio']);
    $preparada->execute();

    $datos_venta = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    // if($datos['cliente']['Clave'] != $datos_venta['Cliente']){
    //     header("Location: https://www.marverrefacciones.mx/index.php");
    //     exit();
    // }
?>
<!DOCTYPE html>
<html lang='es'>

<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Catalogo</title>

    <link href="img/logo_solo.png" rel="icon">

    <link href="css/estado_fuentes.css" rel="stylesheet">


<style>
        .dinero {
            color: green;
        }

        .dinero::before {
            content: '$';
        }

        table {
            width: 100%;
        }

        table>*>* {
            padding: 5px;
        }

    body, p, h3{
        margin: 0;
    }

    *{
        font-family:"Open Sans",sans-serif;
        font-size: 12px;
    }

    .texto-centrado{
        text-align: center;
    }

    .contenedor > *{
        display: inline-block;
    }

    .margen-auto{
        margin: auto;
    }

    .contenedor-central{
        margin: 0px 20px 0px 20px;
    }

    .aliniacion-vertical{
        vertical-align: top;
    }

    th{
        text-align: inherit;
    }

    .linea{
        display: inline;
    }

    .me-10{
        margin-right: 10px;
    }

    .me-30{
        margin-right: 30px;
    }

    .corrido{
        white-space: nowrap;
    }

    th, td{
        padding: 8px;
    }

    .flotar-derecha{
        float: right;
    }

    .p-15{
        padding: 15px;
    }
</style>

</head>

<body>

    <div class="contenedor texto-centrado" >
        <img class="aliniacion-vertical" src="img/logo.png" width="150">
        <div class="aliniacion-vertical contenedor-central" >
            <h3>MARIO ALBERTO VERDUZCO COTA</h3>
            <h3>VECM880923NI1</h3>
            <p>SANTOS DEGOLLADO 451 CENTRO LOS MOCHIS</p>
            <p>SINALOA MEXICO CP.81200</p>
            <p>TEL.8123595</p>
            <h3>Lugar de expedición</h3>
            <p>81200</p>
            <h3>Regimen</h3>
            <p>612</p>
        </div>
        <div class="aliniacion-vertical" >
            <h3>VENTA</h3>
            <h3 class="linea" >Fecha: </h3><p class="linea" ><?php echo $datos_venta['Fecha'] ?></p>
            <div>
                <h3 class="linea" >Hora: </h3><p class="linea" ><?php echo $datos_venta['Hora'] ?></p>
            </div>
            <h3 class="linea me-10" >Serie VEN </h3><h3 class="linea" >Folio <?php echo $_GET['folio'] ?></h3>
        </div>
    </div>

    <div class="contenedor texto-centrado" >
        <div class="aliniacion-vertical me-30" >
                <h3><?php echo $datos['cliente']['RFC'] ?></h3>
                <p><?php echo $datos['cliente']['Clave'] . " " . $datos['cliente']['Razon_Social'] ?></p>
                <P><?php echo
                    $datos['cliente']['Domicilio'] . " " .
                    $datos['cliente']['Num_Exterior']  . ", " .
                    $datos['cliente']['Num_interior']  . " " .
                    $datos['cliente']['Colonia']  . " " .
                    $datos['cliente']['Municipio']  . " " .
                    $datos['cliente']['Estado']  . ", " .
                    $datos['cliente']['Ciudad']  . " " .
                    $datos['cliente']['Pais']  . " C.P. " .
                    $datos['cliente']['Codigo_Postal']
                ?></P>
            </div>
        <div class="aliniacion-vertical" >
            <h3 class="linea" >Vendedor: </h3><p class="linea" ><?php echo $datos_venta['Vendedor'] ?></p>
            <div>
                <h3 class="linea" >Cajero: </h3><p class="linea" ><?php echo $datos_venta['Cajero'] ?></p>
            </div>
        </div>
    </div>

    <table class="margen-auto" >
      <thead>
        <tr>
          <th>Cantidad</th>
          <th>Unidad</th>
          <th>Codigo</th>
          <th>Descripcion</th>
          <th>%Descuento</th>
          <th class="corrido" >Precio U.</th>
          <th>Importe</th>
        </tr>
      </thead>
      <tbody>
        <?php
        
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cantidad, Unidad, CodigoArticulo, Producto + ' ' + Descripcion AS Descripcion, Descuento, Precio FROM VentaDetalle INNER JOIN Producto ON Codigo = CodigoArticulo WHERE Folio = :folio ORDER BY Precio DESC");
            $preparada->bindValue(':folio', $_GET['folio']);
            $preparada->execute();

            $ventas_positivas = [];
            $ventas_negativas = [];
            foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $venta ){
                if( $venta['Precio'] > 0 ){
                    if( isset($ventas_positivas[$venta['CodigoArticulo']]) ){
                        $ventas_positivas[$venta['CodigoArticulo']]['Cantidad'] += $venta['Cantidad'];
                    }else{
                        $ventas_positivas[$venta['CodigoArticulo']] = $venta;
                    }
                }else if( $venta['Precio'] < 0 ){
                    if( isset($ventas_negativas[$venta['CodigoArticulo']]) ){
                        //Cuando el precio es negativo las cantidades pueden ser positivas o negativas, las forzamos a negativas
                        $cantidad_negativa = $venta['Cantidad'] < 0 ? $venta['Cantidad'] : $venta['Cantidad'] * -1;
                        $ventas_negativas[$venta['CodigoArticulo']]['Cantidad'] += $cantidad_negativa;
                    }else{
                        //Cuando el precio es negativo las cantidades pueden ser positivas o negativas, las forzamos a negativas
                        $venta['Cantidad'] = $venta['Cantidad'] < 0 ? $venta['Cantidad'] : $venta['Cantidad'] * -1;
                        $ventas_negativas[$venta['CodigoArticulo']] = $venta;
                    }
                }
            }

            foreach( $ventas_negativas as $venta ){
                if( isset($ventas_positivas[$venta['CodigoArticulo']]) ){
                    $ventas_positivas[$venta['CodigoArticulo']]['Cantidad'] += $venta['Cantidad'];
                    if($ventas_positivas[$venta['CodigoArticulo']]['Cantidad'] <= 0){
                        unset($ventas_positivas[$venta['CodigoArticulo']]);
                    }
                }
            }

            $codigos = 0;
            $piezas = 0;
            $descuentos = 0;
            $importes = 0;
            foreach( $ventas_positivas as $venta ){
                $codigos++;
                $piezas += $venta["Cantidad"];

                $importe = $venta["Cantidad"] * $venta["Precio"];
                $importes += $importe;

                $descuentos += ( $venta["Descuento"] * 0.01 ) * $importe;
                echo "<tr>". 
                        "<td>" . $venta["Cantidad"] . "</td>".
                        "<td class='corrido' >" . $venta["Unidad"] . "</td>".
                        "<td class='corrido' >" . $venta["CodigoArticulo"] . "</td>".
                        "<td>" . $venta["Descripcion"] . "</td>".
                        "<td>" . $venta["Descuento"] . "</td>".
                        "<td class=\"dinero\" >" . number_format($venta["Precio"], 2, '.', ',') . "</td>".
                        "<td class=\"dinero\" >" . number_format($importe, 2, '.', ',') . "</td>".
                    "</tr>";
            }
            $subtotal = $importes - $descuentos;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;

            $total_texto = (new NumberFormatter("es", NumberFormatter::SPELLOUT))->format( floatval(number_format($total, 2, '.', '')) );
        ?>
      </tbody>
    </table>

    <div class="contenedor p-15" >

        <div class="aliniacion-vertical" >
            <h3 class="linea" >Total de codigos: </h3><p class="linea me-10" ><?php echo $codigos ?></p>
            <h3 class="linea" >Total de piezas: </h3><p class="linea" ><?php echo $piezas ?></p>
            <div>
                <?php
                    $preparada = $datos['conexion_base_sucursal']->prepare("SELECT TOP 1 FormaPago FROM PedidosCliente WHERE FolioComprobante = :folio");
                    $preparada->bindValue(':folio', $_GET['folio']);
                    $preparada->execute();

                    $metodo_pago = $preparada->fetchAll(PDO::FETCH_ASSOC)[0]['FormaPago'];

                    $formas_de_pago['01'] = 'efectivo';
                    $formas_de_pago['02'] = 'cheque nominativo';
                    $formas_de_pago['03'] = 'transferencia electronica';
                    $formas_de_pago['04'] = 'tarjeta de credito';
                    $formas_de_pago['28'] = 'tarjeta de débito';
                    $formas_de_pago['99'] = 'credito';
                    $formas_de_pago['99'] = 'credito';
                ?>
                <h3 class="linea" >Metodo de pago: </h3><p class="linea" ><?php echo ( isset($metodo_pago) ? ( isset($formas_de_pago[$metodo_pago]) ? $formas_de_pago[$metodo_pago] : '' ) : '' ) ?></p>
            </div>
            <h3 class="linea" >Importe con leta: </h3><p class="linea" >(<?php echo $total_texto ?> /100 M.N.)</p>
        </div>
        <div class="aliniacion-vertical flotar-derecha" >
            <h3 class="linea" >Descuento: </h3><p class="linea" ><?php echo number_format($descuentos, 2, '.', ',') ?></p>
            <div><h3 class="linea" >Subtotal: </h3><p class="linea" ><?php echo number_format($subtotal, 2, '.', ',') ?></p></div>
            <h3 class="linea" >Iva: </h3><p class="linea" ><?php echo number_format($iva, 2, '.', ',') ?></p>
            <div><h3 class="linea" >Total: </h3><p class="linea" ><?php echo number_format($total, 2, '.', ',') ?></p></div>
        </div>
    </div>

</body>

</html>
<?php
    $html = ob_get_clean();

    require_once 'dompdf/autoload.inc.php';

    use Dompdf\Dompdf;
    use Dompdf\Options;

    $options = new Options();
    // $options->set('isRemoteEnabled', true);
    $options->set('chroot', __DIR__);
    // $options->set('tempDir', 'tamporaldir');
    // $options->set('isHtml5ParserEnabled', true);
    // $options->set('isPhpEnabled', true);
    // $options->set('debugPng', true);
    // $options->set('debugCss', true);

    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);

    $dompdf->render();

    $dompdf->stream("recibo " . $_GET['folio'] . ".pdf", array("Attachment" => true));
    //file_put_contents('filename.pdf', $dompdf->output());
?>