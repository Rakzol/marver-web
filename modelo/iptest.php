<?php

    $ip = "187.134.127.101";

    $url = "https://ipinfo.io/{$ip}?token=a39ff8f192d166";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    print_r($response);

?>