<?php
    $ip_eticketera = '10.10.10.126';
    $puerto_eticketera = 9100;

    $esc = chr(27);
    $gs = chr(29);
    $line_feed = chr(10);
    $cut_paper = $esc . 'm';

    $socket_eticketera = fsockopen($ip_eticketera, $puerto_eticketera, $errno, $errstr, 3);

    for($c = 0; $c < 256; $c++){
    
        $esc_pos = $esc . '@';
        $esc_pos .= $esc . 'a' . chr(1); 
        $esc_pos .= $gs . 'k' . chr($c) . chr(strlen('1')) . '1';
        $esc_pos .= $c;
        $esc_pos .= $line_feed;
        $esc_pos .= $line_feed;
        $esc_pos .= $line_feed;
        $esc_pos .= $line_feed;
        $esc_pos .= $line_feed;
        //$esc_pos .= $cut_paper;

        fwrite($socket_eticketera, $esc_pos);

        echo $c;
    }
?>
