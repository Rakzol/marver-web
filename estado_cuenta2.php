<?php
    ob_start();
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
            --bs-table-bg: rgba(0,0,0,0);
            --bs-table-striped-color: #777;
            --bs-table-striped-bg: rgba(0,0,0,0.05);
            --bs-table-active-color: #777;
            --bs-table-active-bg: rgba(0,0,0,0.1);
            --bs-table-hover-color: #777;
            --bs-table-hover-bg: rgba(0,0,0,0.075);
            width: 100%;
            margin-bottom: 1rem;
            color: #777;
            vertical-align: top;
            border-color: #dee2e6;
        }
        table {
            caption-side: bottom;
            border-collapse: collapse;
        }

        table>thead {
            vertical-align: bottom;
        }
        thead, tbody, tfoot, tr, td, th {
            border-color: inherit;
            border-style: solid;
            border-width: 0;
        }

        table>:not(:last-child)>:last-child>* {
            border-bottom-color: currentColor;
        }

        table>:not(caption)>*>* {
            padding: 0.5rem 0.5rem;
            background-color: var(--bs-table-bg);
            border-bottom-width: 1px;
            box-shadow: inset 0 0 0 9999px var(--bs-table-accent-bg);
        }

th {
    text-align: inherit;
    text-align: -webkit-match-parent;
}

table>tbody {
    vertical-align: inherit;
}

</style>

<style>
    body{
        margin: 0;
    }

    *{
        font-family:"Open Sans",sans-serif;
    }

    p, h3{
        margin: 0px 0px 5px 0px;
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
        </div>
        <div class="aliniacion-vertical" >
            <h3>Estado de cuenta</h3>
            <?php date_default_timezone_set('America/Mexico_City'); ?>
            <P><?php echo "Fecha: " . date('d/m/Y') ?></P>
            <P><?php echo "Hora: " . date('h:i:s A') ?></P>
        </div>
    </div>

        <div class="texto-centrado" >
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

    <table class="margen-auto" >
      <thead>
        <tr>
          <th>Folio</th>
          <th>Inicio</th>
          <th>Vencimiento</th>
          <th>Importe</th>
          <th>Abono</th>
          <th>Debe</th>
        </tr>
      </thead>
      <tbody>
        <?php
        
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Pagos.Fecha, Pagos.FechaVencimiento, Pagos.Importe, Pagos.Abono, Pagos.Folio AS FolioComprobante, PedidosCliente.Folio AS Folio FROM Pagos INNER JOIN PedidosCliente ON PedidosCliente.FolioComprobante = Pagos.Folio AND PedidosCliente.Cliente = :clienteTemp AND PedidosCliente.Status != 'CA' WHERE Pagos.Cliente = :cliente AND Pagos.Saldado = 0 ORDER BY Pagos.Fecha");
            $preparada->bindValue(':clienteTemp', $datos['cliente']['Clave']);
            $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
            $preparada->execute();

            $importes = 0;
            $abonos = 0;

            foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $factura ){
                $importes += $factura["Importe"];
                $abonos += $factura["Abono"];
                echo "<tr>". 
                        "<td>" . $factura["FolioComprobante"] . "</td>".
                        "<td>" . $factura["Fecha"] . "</td>".
                        "<td>" . $factura["FechaVencimiento"] . "</td>".
                        "<td class=\"dinero\" >" . $factura["Importe"] . "</td>".
                        "<td class=\"dinero\" >" . $factura["Abono"] . "</td>".
                        "<td class=\"dinero\" >" . $factura["Importe"] - $factura["Abono"] . "</td>".
                    "</tr>";
            }

        ?>
      </tbody>
      <tfoot>
        <tr>
          <th></th>
          <th></th>
          <th>Totales:</th>
          <th class="dinero"><?php echo $importes; ?></th>
          <th class="dinero"><?php echo $abonos; ?></th>
          <th class="dinero"><?php echo $importes - $abonos; ?></th>
        </tr>
    </tfoot>
    </table>


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

    $dompdf->stream("estado de cuenta.pdf", array("Attachment" => false));
    //file_put_contents('filename.pdf', $dompdf->output());
?>