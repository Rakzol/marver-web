<?php
$json_envio['origin'] = array(
    'location' => array(
        'latLng' => array(
            'latitude' => 25.7941814,
            'longitude' => -108.9858957
        )
    )
);


$intermediarios[] = array(
    'location' => array(
        'latLng' => array(
            'latitude' => 25.79364297992391,
            'longitude' => -108.9817418526033
        )
        )
);

$intermediarios[] = array(
    'location' => array(
        'latLng' => array(
            'latitude' => 25.75364297992391,
            'longitude' => -108.9517418526033
        )
        )
);

if( isset($intermediarios) ){
    $json_envio['intermediates'] = $intermediarios;
}

$json_envio['destination'] = array(
    'location' => array(
        'latLng' => array(
            'latitude' => 25.7941814,
            'longitude' => -108.9858957
        )
    )
);

$json_envio['routingPreference'] = "TRAFFIC_AWARE";
$json_envio['optimizeWaypointOrder'] = true;

$curl = curl_init('https://routes.googleapis.com/directions/v2:computeRoutes');
$cabecera = array(
    'Content-Type: application/json',
    'X-Goog-Api-Key: AIzaSyCAaLR-LdWOBIf1pDXFq8nDi3-j67uiheo',
    'X-Goog-FieldMask: routes.legs.duration,routes.legs.distanceMeters,routes.optimizedIntermediateWaypointIndex,routes.legs.polyline.encodedPolyline'
);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json_envio));
curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecera);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$respuesta = curl_exec($curl);

$rutas = json_decode( $respuesta, true); 
echo isset($rutas['routes'][0]['optimizedIntermediateWaypointIndex']);
echo $respuesta;
?>
