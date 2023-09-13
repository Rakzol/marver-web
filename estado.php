<?php
    //$html = ob_get_clean();

    require_once 'dompdf/autoload.inc.php';

    use Dompdf\Dompdf;
    use Dompdf\Options;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', __DIR__);
    $options->set('tempDir', 'tamporaldir');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('debugPng', true);
    //$options->set('debugCss', true);

    $dompdf = new Dompdf($options);

    $dompdf->loadHtmlFile(file_get_contents('https://github.com/dompdf/dompdf/blob/master/src/Dompdf.php'));

    $dompdf->render();

    // $debugLog = $options->get('debugLogOutput');
    // file_put_contents('dompdf_debug.log', $debugLog);

    $dompdf->stream("estado_cuenta.pdf", array("Attachment" => false));
?>