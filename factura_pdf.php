<?php
    ob_start();
    include "phpqrcode/qrlib.php";
    require_once('modelo/inicializar_datos.php');
    header("Content-Type: text/html");

    $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cajero, Vendedor, Cliente FROM Ventas WHERE Folio = :folio_comprobante AND TipoComprobante = 1");
    $preparada->bindValue(':folio_comprobante', $_GET['folio_comprobante']);
    $preparada->execute();

    $datos_venta = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    if($datos['cliente']['Clave'] != $datos_venta['Cliente']){
        header("Location: https://www.marverrefacciones.mx/index.php");
        exit();
    }

    $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Fecha, Hora, Serie FROM FacturaElectronica WHERE Folio = :folio_comprobante");
    $preparada->bindValue(':folio_comprobante', $_GET['folio_comprobante']);
    $preparada->execute();

    $datos_factura_electronica = $preparada->fetchAll(PDO::FETCH_ASSOC)[0];

    if( !file_exists('C:/Sistema Marver/Facturas/XML/' . $datos_factura_electronica['Serie'] . '_' . str_pad((string)$_GET['folio_comprobante'], 10, '0', STR_PAD_LEFT) . '.XML') ){
        echo "<h1>Factura no timbrada</h1>";
        exit();
    }

    $xml = simplexml_load_file( 'C:/Sistema Marver/Facturas/XML/' . $datos_factura_electronica['Serie'] . '_' . str_pad((string)$_GET['folio_comprobante'], 10, '0', STR_PAD_LEFT) . '.XML' );

    $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
    $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
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
        font-size: 10px;
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
        padding: 5px;
    }

</style>

</head>

<body>

    <div class="contenedor texto-centrado" >
        <img class="aliniacion-vertical" src="img/logo.png" width="150">
        <div class="aliniacion-vertical contenedor-central" >
            <h3><?php echo $xml->xpath('//cfdi:Emisor')[0]['Nombre'] ?></h3>
            <h3><?php echo $xml->xpath('//cfdi:Emisor')[0]['Rfc'] ?></h3>
            <p>SANTOS DEGOLLADO 451 CENTRO LOS MOCHIS</p>
            <p>SINALOA MEXICO CP.<?php echo $xml->xpath('//cfdi:Comprobante')[0]['LugarExpedicion'] ?></p>
            <p>TEL.8123595</p>
            <h3>Regimen</h3>
            <p><?php echo $xml->xpath('//cfdi:Emisor')[0]['RegimenFiscal'] ?> Persona Física con Actividades<br>Empresariales y Profesionales</p>
        </div>
        <div class="aliniacion-vertical" >
            <h3>INGRESO FACTURA ORIGINAL</h3>
            <h3 class="linea" >Fecha: </h3><p class="linea" ><?php echo $datos_factura_electronica['Fecha'] . ' ' . $datos_factura_electronica['Hora'] ?></p>
            <br>
            <h3 class="linea me-10" >Serie <?php echo $datos_factura_electronica['Serie'] ?> </h3><h3 class="linea" >Folio <?php echo $_GET['folio_comprobante'] ?></h3>
            <h3 style="padding-top: 10px;" >Folio Fiscal</h3>
            <p class="linea" ><?php echo $xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] ?></p>
            <br>
            <p class="linea" >Fecha de certificación: </p><p class="linea" ><?php echo $xml->xpath('//tfd:TimbreFiscalDigital')[0]['FechaTimbrado'] ?></p>
            <br>
            <p class="linea" >Num. Serie del CSD: </p><p class="linea" ><?php echo $xml->xpath('//cfdi:Comprobante')[0]['NoCertificado'] ?></p>
            <br>
            <p class="linea" >CSD del SAT: </p><p class="linea" ><?php echo $xml->xpath('//tfd:TimbreFiscalDigital')[0]['NoCertificadoSAT'] ?></p>
        </div>
    </div>

    <div class="contenedor texto-centrado" >
        <div class="aliniacion-vertical me-30" >
                <h3><?php echo $xml->xpath('//cfdi:Receptor')[0]['Rfc'] ?></h3>
                <p><?php echo $datos['cliente']['Clave'] . " " . $xml->xpath('//cfdi:Receptor')[0]['Nombre'] ?></p>
                <P><?php echo
                    $datos['cliente']['Domicilio'] . " " .
                    $datos['cliente']['Num_Exterior']  . ", " .
                    $datos['cliente']['Num_interior']  . " " .
                    $datos['cliente']['Colonia']  . " " .
                    $datos['cliente']['Municipio']  . " " .
                    $datos['cliente']['Estado']  . ", " .
                    $datos['cliente']['Ciudad']  . " " .
                    $datos['cliente']['Pais']  . " C.P. " .
                    $xml->xpath('//cfdi:Receptor')[0]['DomicilioFiscalReceptor']
                ?></P>
                <h3 class="linea" >Uso de CFDI: </h3><p class="linea" ><?php echo $xml->xpath('//cfdi:Receptor')[0]['UsoCFDI'] ?></p>
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
          <th>ClaveProdServ</th>
          <th>Codigo</th>
          <th>Descripcion</th>
          <th>%Descuento</th>
          <th class="corrido" >Precio U.</th>
          <th>Importe</th>
        </tr>
      </thead>
      <tbody>
        <?php

            $total_codigos = 0;
            $total_piezas = 0;

            foreach ($xml->xpath('//cfdi:Concepto') as $concepto) {
                $atributos = $concepto->attributes();

                $total_codigos += 1;
                $total_piezas += $atributos['Cantidad'];

                echo 
                    "<tr>". 
                    "<td>" . $atributos['Cantidad'] . "</td>".
                    "<td class='corrido' >" . $atributos["Unidad"] . "</td>".
                    "<td class='corrido' >" . $atributos["ClaveProdServ"] . "</td>".
                    "<td class='corrido' >" . $atributos["NoIdentificacion"] . "</td>".
                    "<td>" . $atributos["Descripcion"] . "</td>".
                    "<td class='corrido' >" .  number_format( 100 * (float)$atributos["Descuento"] / (float)$atributos['Importe'] , 2, '.', ',') . "</td>".
                    "<td class=\"dinero corrido\" >" . number_format((float)$atributos["ValorUnitario"], 2, '.', ',') . "</td>".
                    "<td class=\"dinero corrido\" >" . number_format((float)$atributos["Importe"], 2, '.', ',') . "</td>".
                    "</tr>";

            }
        ?>
      </tbody>
    </table>

    <div class="contenedor p-15" >
         <div class="aliniacion-vertical flotar-derecha" >
            <h3 class="linea" >Descuento: </h3><p class="linea" ><?php echo number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['Descuento'], 2, '.', ',') ?></p>
            <div><h3 class="linea" >Subtotal: </h3><p class="linea" ><?php echo number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['SubTotal'], 2, '.', ',') ?></p></div>
            <h3 class="linea" >Iva: </h3><p class="linea" ><?php echo number_format((float)$xml->xpath('/cfdi:Comprobante/cfdi:Impuestos')[0]['TotalImpuestosTrasladados'], 2, '.', ',') ?></p>
            <div><h3 class="linea" >Total: </h3><p class="linea" ><?php echo number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['Total'], 2, '.', ',') ?></p></div>
            <?php QRcode::png( "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx" .
                                "?re=" . $xml->xpath('//cfdi:Emisor')[0]['Rfc'] . 
                                "&rr=" . $xml->xpath('//cfdi:Receptor')[0]['Rfc'] . 
                                "&tt=" . ( str_pad( explode('.',$xml->xpath('//cfdi:Comprobante')[0]['Total'])[0], 10, '0', STR_PAD_LEFT ) . '.' . str_pad( explode('.',$xml->xpath('//cfdi:Comprobante')[0]['Total'])[1], 6, '0', STR_PAD_RIGHT ) ) . 
                                "&id=" . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] .
                                "&fe=" . substr( $xml->xpath('//cfdi:Comprobante')[0]['Sello'], -8 ) , "qr/" . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] . ".png"); ?>
            <img width="200" height="200" style="background: red;" src="<?php echo "qr/" . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] . ".png" ?>" alt="">

        </div>
        <div class="aliniacion-vertical" >
            <h3 class="linea" >Total de codigos: </h3><p class="linea me-10" ><?php echo $total_codigos ?></p>
            <h3 class="linea" >Total de piezas: </h3><p class="linea me-10" ><?php echo $total_piezas ?></p>
            <h3 class="linea" >Condiciones: </h3><p class="linea" ><?php echo $xml->xpath('//cfdi:Comprobante')[0]['CondicionesDePago'] ?></p>
            <div>
                <h3 class="linea" >Forma de pago: </h3><p class="linea me-10" ><?php echo $xml->xpath('//cfdi:Comprobante')[0]['FormaPago'] ?></p>
                <h3 class="linea" >Metodo de pago: </h3><p class="linea me-10" ><?php echo $xml->xpath('//cfdi:Comprobante')[0]['MetodoPago'] ?></p>
                <!-- <h3 class="linea" >Cuenta: </h3><p class="linea" ></p> -->
            </div>
            <h3 class="linea" >Importe con leta: </h3><p class="linea" >(<?php echo (new NumberFormatter("es", NumberFormatter::SPELLOUT))->format( floatval(number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['Total'], 2, '.', '')) ); ?> /100 M.N.)</p>
            <h3>Sello Digital del CFDI</h3>
            <p class="me-10" style="font-size: 8px;" ><?php
                $salida = "";
                $acumulador_salto = 0;
                foreach( str_split( $xml->xpath('//cfdi:Comprobante')[0]['Sello']) as $caracter ){
                    $acumulador_salto += 1;
                    $salida .= $caracter;
                    if( $acumulador_salto == 100 ){
                        $acumulador_salto = 0;
                        $salida .= '<br>';
                    }
                }
                echo $salida ?></p>

            <h3>Sello del SAT</h3>
            <p class="me-10" style="font-size: 8px;" ><?php
                $salida = "";
                $acumulador_salto = 0;
                foreach( str_split( $xml->xpath('//tfd:TimbreFiscalDigital')[0]['SelloSAT']) as $caracter ){
                    $acumulador_salto += 1;
                    $salida .= $caracter;
                    if( $acumulador_salto == 100 ){
                        $acumulador_salto = 0;
                        $salida .= '<br>';
                    }
                }
                echo $salida ?></p>

            <h3>Cadena original del complemento de certificación digital del SAT</h3>
            <p class="me-10" style="font-size: 8px;" ><?php
                $salida = "";
                $acumulador_salto = 0;
                foreach( str_split( '||1.0|' . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['UUID'] . '|' . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['FechaTimbrado'] . '|' 
                        . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['SelloSAT'] . '|' . $xml->xpath('//tfd:TimbreFiscalDigital')[0]['NoCertificadoSAT'] . '||' ) as $caracter ){
                    $acumulador_salto += 1;
                    $salida .= $caracter;
                    if( $acumulador_salto == 100 ){
                        $acumulador_salto = 0;
                        $salida .= '<br>';
                    }
                }
                echo $salida ?></p>
        </div>
    </div>

    <div class="contenedor p-15" >
        <h3 class="linea" >Bueno por: <?php echo number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['Total'], 2, '.', ',') ?></h3>
    </div>

    <p class="p-15" >
        Debe(mos) y pagare(mos) incondicionalmente por este pagaré a la orden de <?php echo $xml->xpath('//cfdi:Emisor')[0]['Nombre'] ?> en el día <?php echo $datos_factura_electronica['Fecha'] . ' ' . $datos_factura_electronica['Hora'] ?>
        la cantidad de (<?php echo (new NumberFormatter("es", NumberFormatter::SPELLOUT))->format( floatval(number_format((float)$xml->xpath('//cfdi:Comprobante')[0]['Total'], 2, '.', '')) ); ?> /100 M.N.) valor recibido a mi(nuestra) entera satisfaccion este pagaré forma parte de una serie
        numeral del 1 al 0 y todos estan sujetos a la condicion de que al no pagarse cualquiera de ellos a su vencimiento serán exigibles todos los que le sigan en numero, ademas de los ya vencidos, desde la
        <br>
        Nombre: <?php echo $xml->xpath('//cfdi:Receptor')[0]['Nombre'] ?>
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
                    $xml->xpath('//cfdi:Receptor')[0]['DomicilioFiscalReceptor']
                ?>
    </p>

    <h3 class="linea flotar-derecha p-15" >Acepto(amos)</h3>

    <h3 class="texto-centrado p-15" >Esta es una presentación impresa del Comprobante Fiscal Digital</h3>
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

    $dompdf->stream("preventa " . $_GET['folio'] . ".pdf", array("Attachment" => true));
    //file_put_contents('filename.pdf', $dompdf->output());
?>