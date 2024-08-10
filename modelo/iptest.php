<?php

    $ip = "187.134.127.101";
    
    $url = "https://ipinfo.io/{$ip}?token=a39ff8f192d166";

    $ch = curl_init();
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if ($response != false) {
        $data = json_decode($response, true);

        $location = explode(',', $data['loc']);

        $lat_api = $location[0];
        $lon_api = $location[1];
    }

    $lat_api = null;
    $lon_api = null;

    curl_close($ch);

?>