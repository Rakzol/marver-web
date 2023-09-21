<?php
    // ob_start();
    require_once('modelo/inicializar_datos.php');
    header("Content-Type: text/html");
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
            <h3>PREVENTA</h3>
            <?php date_default_timezone_set('America/Mexico_City'); ?>
            <h3 class="linea" >Fecha: </h3><p class="linea" ><?php echo date('d/m/Y') ?></p>
            <div>
                <h3 class="linea" >Hora: </h3><p class="linea" ><?php echo date('h:i:s A') ?></p>
            </div>
            <h3 class="linea me-10" >Serie A </h3><h3 class="linea" >Folio <?php echo $_GET['folio'] ?></h3>
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
            <?php
                $preparada = $datos['conexion_base_sucursal']->prepare("SELECT TOP 1 Cajero, Vendedor, Fecha, Hora FROM Preventa WHERE Folio = :folio");
                $preparada->bindValue(':folio', $_GET['folio']);
                $preparada->execute();

                $datos_preventa = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];
            ?>
            <h3 class="linea" >Vendedor: </h3><p class="linea" ><?php echo $datos_preventa['Vendedor'] ?></p>
            <div>
                <h3 class="linea" >Cajero: </h3><p class="linea" ><?php echo $datos_preventa['Cajero'] ?></p>
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
        
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cantidad, Unidad, CodigoArticulo, Producto + ' ' + Descripcion AS Descripcion, Descuento, Precio FROM PreventaDetalle INNER JOIN Producto ON Codigo = CodigoArticulo WHERE Folio = :folio ORDER BY Precio DESC");
            $preparada->bindValue(':folio', $_GET['folio']);
            $preparada->execute();

            $preventas_positivas = [];
            $preventas_negativas = [];
            foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $preventa ){
                if( $preventa['Precio'] > 0 ){
                    if( isset($preventas_positivas[$preventa['CodigoArticulo']]) ){
                        $preventas_positivas[$preventa['CodigoArticulo']]['Cantidad'] += $preventa['Cantidad'];
                    }else{
                        $preventas_positivas[$preventa['CodigoArticulo']] = $preventa;
                    }
                }else if( $preventa['Precio'] < 0 ){
                    if( isset($preventas_negativas[$preventa['CodigoArticulo']]) ){
                        //Cuando el precio es negativo las cantidades pueden ser positivas o negativas, las forzamos a negativas
                        $cantidad_negativa = $preventa['Cantidad'] < 0 ? $preventa['Cantidad'] : $preventa['Cantidad'] * -1;
                        $preventas_negativas[$preventa['CodigoArticulo']]['Cantidad'] += $cantidad_negativa;
                    }else{
                        //Cuando el precio es negativo las cantidades pueden ser positivas o negativas, las forzamos a negativas
                        $preventa['Cantidad'] = $preventa['Cantidad'] < 0 ? $preventa['Cantidad'] : $preventa['Cantidad'] * -1;
                        $preventas_negativas[$preventa['CodigoArticulo']] = $preventa;
                    }
                }
            }

            foreach( $preventas_negativas as $preventa ){
                if( isset($preventas_positivas[$preventa['CodigoArticulo']]) ){
                    $preventas_positivas[$preventa['CodigoArticulo']]['Cantidad'] += $preventa['Cantidad'];
                    if($preventas_positivas[$preventa['CodigoArticulo']]['Cantidad'] <= 0){
                        unset($preventas_positivas[$preventa['CodigoArticulo']]);
                    }
                }
            }

            $codigos = 0;
            $piezas = 0;
            $descuentos = 0;
            $importes = 0;
            foreach( $preventas_positivas as $preventa ){
                $codigos++;
                $piezas += $preventa["Cantidad"];

                $importe = $preventa["Cantidad"] * $preventa["Precio"];
                $importes += $importe;

                $descuentos += ( $preventa["Descuento"] * 0.01 ) * $importe;
                echo "<tr>". 
                        "<td>" . $preventa["Cantidad"] . "</td>".
                        "<td class='corrido' >" . $preventa["Unidad"] . "</td>".
                        "<td class='corrido' >" . $preventa["CodigoArticulo"] . "</td>".
                        "<td>" . $preventa["Descripcion"] . "</td>".
                        "<td>" . $preventa["Descuento"] . "</td>".
                        "<td class=\"dinero\" >" . number_format($preventa["Precio"], 2, '.', ',') . "</td>".
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
            <h3 class="linea" >Total de piezas: </h3><p class="linea me-10" ><?php echo $piezas ?></p>
            <h3 class="linea" >Condiciones: </h3><p class="linea" >CREDITO</p>
            <div>
                <h3 class="linea" >Condiciones de pago: </h3><p class="linea me-10" ></p>
                <h3 class="linea" >Metodo de pago: </h3><p class="linea me-10" >PPD Pago en parcialidades o diferido</p>
                <h3 class="linea" >Cuenta: </h3><p class="linea" ></p>
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

    <div class="contenedor p-15" >
        <h3 class="linea" >Bueno por: <?php echo number_format($total, 2, '.', ',') ?></h3>
        <h3 class="linea flotar-derecha" >Pagaré No. 0.00</h3>
    </div>

    <p class="p-15" >
        Debe(mos) y pagare(mos) incondicionalmente por este pagaré a la orden de MARIO ALBERTO VERDUZCO COTA en LOS MOCHIS el día <?php echo $datos_preventa['Fecha'] . ' ' . $datos_preventa['Hora'] ?>
        la cantidad de (<?php echo $total_texto ?> /100 M.N.) valor recibidoa mi(nuestra) entera satisfaccion este pagaré forma parte de una serie
        numeral del 1 al 0 y todos estan sujetos a la condicion de que al no pagarse cualquiera de ellos a su vencimiento serán exigibles todos los que le sigan en numero, ademas de
        <br>
        Nombre: <?php echo $datos['cliente']['Razon_Social'] ?>
        <br>
        Direccion: <?php echo
                    $datos['cliente']['Domicilio'] . " " .
                    $datos['cliente']['Num_Exterior']  . ", " .
                    $datos['cliente']['Num_interior']  . " " .
                    $datos['cliente']['Colonia']  . " " .
                    $datos['cliente']['Municipio']  . " " .
                    $datos['cliente']['Estado']  . ", " .
                    $datos['cliente']['Ciudad']  . " " .
                    $datos['cliente']['Pais']  . " C.P. " .
                    $datos['cliente']['Codigo_Postal']
                ?>
    </p>

    <h3 class="linea flotar-derecha p-15" >Acepto(amos)</h3>

    <h3 class="texto-centrado p-15" >Esta es una presentación impresa del Comprobante Fiscal Digital</h3>
</body>

</html>
<?php
    // $html = ob_get_clean();

    // require_once 'dompdf/autoload.inc.php';

    // use Dompdf\Dompdf;
    // use Dompdf\Options;

    // $options = new Options();
    // // $options->set('isRemoteEnabled', true);
    // $options->set('chroot', __DIR__);
    // // $options->set('tempDir', 'tamporaldir');
    // // $options->set('isHtml5ParserEnabled', true);
    // // $options->set('isPhpEnabled', true);
    // // $options->set('debugPng', true);
    // // $options->set('debugCss', true);

    // $dompdf = new Dompdf($options);

    // $dompdf->loadHtml($html);

    // $dompdf->render();

    // $dompdf->stream("estado de cuenta.pdf", array("Attachment" => false));
    // //file_put_contents('filename.pdf', $dompdf->output());
?>