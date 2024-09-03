<?php
    // Configuración de la impresora
    $ip_eticketera = '10.10.10.104';
    $puerto_eticketera = 9100; // Puerto típico para impresoras de red

    // Comandos ESC/POS
    $esc = chr(27); // Código de escape ESC
    $gs = chr(29);  // Código GS
    $line_feed = chr(10); // Salto de línea
    $cut_paper = $esc . 'm';
    $barcode_number = '5';
    $bold_on = $esc . 'E' . chr(1);  // Activar negritas
    $bold_off = $esc . 'E' . chr(0); // Desactivar negritas

    // Construir el contenido en formato ESC/POS
    $esc_pos = $esc . '@'; // Inicializa la impresora
    $esc_pos .= $esc . 'a' . chr(1); // Alineación centrada
    $esc_pos .= $bold_on;
    $esc_pos .= 'Marver Refacciones';
    $esc_pos .= $line_feed;
    $esc_pos .= 'VECM880923NI1';
    $esc_pos .= $line_feed;
    $esc_pos .= 'MARIO ALBERTO VERDUZCO COTA';
    $esc_pos .= $line_feed;
    $esc_pos .= $esc . 'a' . chr(0); // Alineación izquierda
    $esc_pos .= 'Fecha: ';
    $esc_pos .= $bold_off;
    $esc_pos .= date('Y-m-d');
    $esc_pos .= $line_feed;
    $esc_pos .= $bold_on;
    $esc_pos .= 'Hora: ';
    $esc_pos .= $bold_off;
    $esc_pos .= date('h:i:s a');
    $esc_pos .= $line_feed;
    $esc_pos .= $bold_on;
    $esc_pos .= chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205).chr(205);

    // Salto de línea para separación
    $esc_pos .= $line_feed;
    // Configurar alineación centrada para el texto
    $esc_pos .= $esc . 'a' . chr(1); // Alineación centrada
    // Imprimir código de barras UPC-A
    $esc_pos .= $gs . 'k' . chr(79) . chr(strlen($barcode_number)) . $barcode_number;
    // Salto de línea para separación
    $esc_pos .= $line_feed;
    $esc_pos .= $line_feed;
    $esc_pos .= $line_feed;
    $esc_pos .= $line_feed;
    $esc_pos .= $line_feed;
    // Cortar el papel (esto puede variar según el modelo de impresora)
    $esc_pos .= $cut_paper; // Corta el papel

    // Enviar el contenido a la impresora
    $socket_eticketera = fsockopen($ip_eticketera, $puerto_eticketera);
    fwrite($socket_eticketera, $esc_pos);
    fclose($socket_eticketera);
?>
