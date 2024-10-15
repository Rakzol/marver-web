<?php
    $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');

    $preparada = $conexion->prepare('INSERT INTO mensajes (mensaje) VALUES (:mensaje)');
    $preparada->bindValue(':mensaje', $_POST['mensaje']);
    $preparada->execute();
?>