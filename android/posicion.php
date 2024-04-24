<?php
    try{
        header('Content-Type: application/json');

        $conexion = new PDO('sqlsrv:Server=10.10.10.130;Database=Mochis;TrustServerCertificate=true','MARITE','2505M$RITE');
        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        $preparada = $conexion->prepare('SELECT Clave FROM Vendedores WHERE Clave = :clave AND Contraseña = :contrasena');
        $preparada->bindValue(':clave', $_POST['u']);
        $preparada->bindValue(':contrasena', $_POST['c']);
        $preparada->execute();

        $usuarios = $preparada->fetchAll(PDO::FETCH_ASSOC);

        if( count($usuarios) == 0 ){
            $resultado["status"] = 1;
            echo json_encode($resultado);
            exit();
        }

        $preparada = $conexion->prepare('INSERT INTO posiciones VALUES( :usuario, :latitud, :longitud, :velocidad, GETDATE() )');
        $preparada->bindValue(':usuario', $_POST['u']);
        $preparada->bindValue(':latitud', $_POST['la']);
        $preparada->bindValue(':longitud', $_POST['ln']);
        $preparada->bindValue(':velocidad', $_POST['v']);
        $preparada->execute();

        $preparada = $conexion->prepare('INSERT INTO posiciones_test VALUES( :usuario, :presicion, :latitud, :longitud, GETDATE() )');
        $preparada->bindValue(':usuario', $_POST['u']);
        $preparada->bindValue(':presicion', $_POST['presicion']);
        $preparada->bindValue(':latitud', $_POST['la']);
        $preparada->bindValue(':longitud', $_POST['ln']);
        $preparada->execute();

        /**********************************/
        
        /*$cabeceras = getallheaders();

        // Captura los datos recibidos
        // Para datos GET
        $datosGET = $_GET;

        // Para datos POST (Nota: esto puede ser un array vacío si el cuerpo de la petición no es form-data o x-www-form-urlencoded)
        $datosPOST = $_POST;

        // Para datos brutos enviados (ejemplo: JSON, XML, etc.)
        $datosBrutos = file_get_contents('php://input');

        // Combinar toda la información en una sola estructura
        $informacionCompleta = [
            'Cabeceras' => $cabeceras,
            'GET' => $datosGET,
            'POST' => $datosPOST,
            'DatosBrutos' => $datosBrutos
        ];

        // Convertir la información a formato JSON para almacenamiento
        $informacionJSON = json_encode($informacionCompleta, JSON_PRETTY_PRINT);

        // Especificar la ruta del archivo donde se almacenarán los datos
        $rutaArchivo = 'datos_recibidos.json';

        // Guardar la información en el archivo
        file_put_contents($rutaArchivo, $informacionJSON);*/
        
        /**********************************/
        

        /*$resultado["status"] = 0;
        echo json_encode($resultado);*/
    }catch( Exception $exception ) {
        header('HTTP/1.1 500 ' . $exception->getMessage());
    }
?>