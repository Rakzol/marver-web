<?php
    try{
        require_once('inicializar_datos.php');

        $preparada = $datos['conexion_base_sucursal']->prepare("SELECT * FROM Preventa WHERE Cliente = :cliente AND ( Extra1 != 'C' OR Extra1 IS NULL ) AND ( Extra2 != 'C' OR Extra2 IS NULL ) AND ( Extra3 != 'C' OR Extra3 IS NULL ) ORDER BY FOLIO DESC");
        $preparada->bindValue(':cliente', $datos['cliente']['Clave']);
        $preparada->execute();

        echo json_encode($preparada->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>