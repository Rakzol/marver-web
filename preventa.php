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
          <th>Cantidad</th>
          <th>Unidad</th>
          <th>Codigo</th>
          <th>Descripcion</th>
          <th>%Descuento</th>
          <th>Precio U.</th>
          <!-- <th>Importe</th> -->
        </tr>
      </thead>
      <tbody>
        <?php
        
            $preparada = $datos['conexion_base_sucursal']->prepare("SELECT Cantidad, Unidad, CodigoArticulo, Producto + ' ' + Descripcion AS Descripcion, Descuento, Precio FROM PreventaDetalle INNER JOIN Producto ON Codigo = CodigoArticulo WHERE Folio = :folio");
            $preparada->bindValue(':folio', $_GET['folio']);
            $preparada->execute();

            $preventas_positivas = null;
            foreach( $preparada->fetchAll(PDO::FETCH_ASSOC) as $preventa ){
                if( $preventa['Cantidad'] > 0 ){
                    if( $preventas_positivas[$preventa['CodigoArticulo']] ){
                        $preventas_positivas[$preventa['CodigoArticulo']]['Cantidad'] += $preventa['Cantidad'];
                    }else{
                        $preventas_positivas[$preventa['CodigoArticulo']] = $preventa;
                    }
                }
            }

            foreach( $preventas_positivas->fetchAll(PDO::FETCH_ASSOC) as $preventa ){
                echo "<tr>". 
                        "<td>" . $preventa["Cantidad"] . "</td>".
                        "<td>" . $preventa["Unidad"] . "</td>".
                        "<td>" . $preventa["CodigoArticulo"] . "</td>".
                        "<td>" . $preventa["Descripcion"] . "</td>".
                        "<td>" . $preventa["Descuento"] . "</td>".
                        "<td class=\"dinero\" >" . $preventa["Precio"] . "</td>".
                        // "<td class=\"dinero\" >" . $preventa["Importe"] . "</td>".
                    "</tr>";
            }

        ?>
      </tbody>
      <tfoot>
        <!-- <tr>
          <th></th>
          <th></th>
          <th>Totales:</th>
          <th class="dinero"><?php echo number_format($importes, 2, '.', ','); ?></th>
          <th class="dinero"><?php echo number_format($abonos, 2, '.', ','); ?></th>
          <th class="dinero"><?php echo number_format($importes - $abonos, 2, '.', ','); ?></th>
        </tr> -->
    </tfoot>
    </table>


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

    // $dompdf->stream("estado de cuenta.pdf", array("Attachment" => true));
    // //file_put_contents('filename.pdf', $dompdf->output());
?>