<?php
    header('Content-Type: application/json');

    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

    $preparada = $conexion->prepare('SELECT * FROM mensajes');
    $preparada->execute();

    $resultados = $preparada->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
?>