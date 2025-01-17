<?php
    header('Content-Type: application/json');

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    try{

        require_once 'JWTHelper.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        $metodo = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $path = trim(str_replace($scriptName, '', $requestUri), '/');
        $partes = explode('/', $path);

        $recurso = $partes[0] ?? null;
        $id = $partes[1] ?? null;

        if($recurso == 'login'){
            switch($metodo){
                case 'POST':
                    $sucursal = $datos['sucursal'] ?? null;

                    switch( $sucursal ){
                        case 'mochis':
                            $conexion = new PDO($arregloConexionMochis[0], $arregloConexionMochis[1], $arregloConexionMochis[2]);
                        break;
                        case 'guasave':
                            $conexion = new PDO($arregloConexionGuasave[0], $arregloConexionGuasave[1], $arregloConexionGuasave[2]);
                        break;
                        case 'higuera':
                            $conexion = new PDO($arregloConexionHiguera[0], $arregloConexionHiguera[1], $arregloConexionHiguera[2]);
                            break;
                        default:
                            http_response_code(400);
                            echo json_encode(["error" => "Sucursal invalida"], JSON_UNESCAPED_UNICODE);
                            exit();
                        break;
                    }        
            
                    $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

                    $clave = $datos['clave'] ?? null;
                    $contraseña = $datos['contraseña'] ?? null;
            
                    // if( filter_var($clave, FILTER_VALIDATE_INT) == false || filter_var($contraseña, FILTER_VALIDATE_INT) == false ){
                    //     http_response_code(400);
                    //     echo json_encode(["error" => "La clave y contraseña tienen que ser enteros"], JSON_UNESCAPED_UNICODE);
                    //     exit();
                    // }

                    $preparada = $conexion->prepare("SELECT TOP 1 Clave, Nombre, Perfil FROM Usuarios WHERE Clave = :clave AND Contraseña = :contrasena");
                    $preparada->bindValue(":clave", $clave);
                    $preparada->bindValue(":contrasena", $contraseña);
                    $preparada->execute();
        
                    $usuario = $preparada->fetch(PDO::FETCH_ASSOC);
        
                    if(!$usuario){
                        http_response_code(401);
                        echo json_encode(["error" => "Credenciales invalidas"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    
                    $payload = [
                        "clave" => $usuario['Clave'],
                        "nombre" => $usuario['Nombre'],
                        "sucursal" => $sucursal,
                        "perfil" => $usuario['Perfil'],
                        "iat" => time(),
                        "exp" => time() + (60 * 60 * 24 *30)
                    ];
        
                    $token = generarJWT($payload);
        
                    echo json_encode(["token" => $token], JSON_UNESCAPED_UNICODE);
                break;
                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Metodo no permitido"], JSON_UNESCAPED_UNICODE);
                break;
            }
            exit();
        }

        $token = obtenerJWT();
        $payload = validarJWT($token);

        if(!$payload){
            http_response_code(401);
            echo json_encode(["token" => $token, "error" => "Token invalido"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $sucursal = $payload['sucursal'];

        switch( $sucursal ){
            case 'mochis':
                $conexion = new PDO($arregloConexionMochis[0], $arregloConexionMochis[1], $arregloConexionMochis[2]);
            break;
            case 'guasave':
                $conexion = new PDO($arregloConexionGuasave[0], $arregloConexionGuasave[1], $arregloConexionGuasave[2]);
            break;
            case 'higuera':
                $conexion = new PDO($arregloConexionHiguera[0], $arregloConexionHiguera[1], $arregloConexionHiguera[2]);
                break;
            default:
                http_response_code(400);
                echo json_encode(["error" => "Sucursal invalida"], JSON_UNESCAPED_UNICODE);
                exit();
            break;
        }        

        $conexion->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, True);

        if($recurso == 'payload'){
            switch($metodo){
                case 'POST':
                    echo json_encode(["payload" => $payload], JSON_UNESCAPED_UNICODE);
                break;
                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Metodo no permitido"], JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        else if($recurso == 'paqueterias'){
            switch($metodo){
                case 'POST':
                    $nombre = $datos['nombre'] ?? null;

                    // if( !$nombre ){
                    //     http_response_code(400);
                    //     echo json_encode(["error" => "El nombre debe ser una cadena"], JSON_UNESCAPED_UNICODE);
                    //     exit();
                    // }

                    $preparada = $conexion->prepare("INSERT INTO paqueterias VALUES(:nombre)");
                    $preparada->bindValue(":nombre", $nombre);
                    $preparada->execute();

                    http_response_code(201);
                    echo json_encode(["id" => $conexion->lastInsertId()], JSON_UNESCAPED_UNICODE);
                break;
                case 'GET':

                    if($id){
                        $preparada = $conexion->prepare("SELECT * FROM paqueterias WHERE id = :id");
                        $preparada->bindValue(":id", $id);
                    }else{
                        $preparada = $conexion->prepare("SELECT * FROM paqueterias ORDER BY nombre");
                    }
                    $preparada->execute();

                    $paqueterias = $preparada->fetchAll(PDO::FETCH_ASSOC);
        
                    if(!$paqueterias){
                        http_response_code(404);
                        echo json_encode(["error" => "No hay paqueterias que coincidan"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    echo json_encode(["paqueterias" => $paqueterias], JSON_UNESCAPED_UNICODE);
                break;
                case 'DELETE':
                    $preparada = $conexion->prepare("DELETE FROM paqueterias WHERE id = :id");
                    $preparada->bindValue(":id", $id);
                    $preparada->execute();
        
                    if(!$preparada->rowCount()){
                        http_response_code(404);
                        echo json_encode(["error" => "No se elimino ninguna paqueteria"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    http_response_code(204);
                break;
                case 'PUT':
                    $nombre = $datos['nombre'] ?? null;

                    $preparada = $conexion->prepare("UPDATE paqueterias SET nombre = :nombre WHERE id = :id");
                    $preparada->bindValue(":nombre", $nombre);
                    $preparada->bindValue(":id", $id);
                    $preparada->execute();
        
                    if(!$preparada->rowCount()){
                        http_response_code(404);
                        echo json_encode(["error" => "No se actualizo ninguna paqueteria"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    http_response_code(204);
                break;
                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Metodo no permitido"], JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        else if($recurso == 'bitacoras'){
            switch($metodo){
                case 'POST':
                    $usuario = $datos['usuario'] ?? null;
                    //$fecha = $datos['fecha'] ?? null;
                    $proveedor = $datos['proveedor'] ?? null;
                    $numeroDeCajas = $datos['numeroDeCajas'] ?? null;
                    $paqueteria = $datos['paqueteria'] ?? null;
                    $observacion = $datos['observacion'] ?? null;

                    $preparada = $conexion->prepare("INSERT INTO bitacoras VALUES( :usuario, GETDATE(), :proveedor, :numeroDeCajas, :paqueteria, :observacion)");
                    $preparada->bindValue(":usuario", $usuario);
                    //$preparada->bindValue(":fecha", $fecha);
                    $preparada->bindValue(":proveedor", $proveedor);
                    $preparada->bindValue(":numeroDeCajas", $numeroDeCajas);
                    $preparada->bindValue(":paqueteria", $paqueteria);
                    $preparada->bindValue(":observacion", $observacion);
                    $preparada->execute();

                    http_response_code(201);
                    echo json_encode(["id" => $conexion->lastInsertId()], JSON_UNESCAPED_UNICODE);
                break;
                case 'GET':

                    $usuario = $_GET['usuario'] ?? null;
                    $fechaInicio = $_GET['fechaInicio'] ?? null;
                    $fechaFin = $_GET['fechaFin'] ?? null;
                    $proveedor = $_GET['proveedor'] ?? null;
                    $numeroDeCajas = $_GET['numeroDeCajas'] ?? null;
                    $paqueteria = $_GET['paqueteria'] ?? null;
                    $observacion = $_GET['observacion'] ?? null;

                    if($id){
                        $preparada = $conexion->prepare("
                            SELECT
                            bi.id AS 'bi.id', bi.fecha AS 'bi.fecha', bi.numeroDeCajas AS 'bi.numeroDeCajas', bi.observacion AS 'bi.observacion',
                            us.Clave AS 'us.id', us.Nombre AS 'us.nombre',
                            pr.Clave AS 'pr.id', pr.Nombre AS 'pr.nombre',
                            pa.id AS 'pa.id', pa.nombre AS 'pa.nombre'
                            FROM bitacoras bi
                            INNER JOIN Usuarios us ON us.Clave = bi.usuario
                            INNER JOIN Proveedores pr ON pr.Clave = bi.proveedor
                            INNER JOIN paqueterias pa ON pa.id = bi.paqueteria
                            WHERE bi.id = :id
                        ");
                        $preparada->bindValue(":id", $id);
                    }else{
                        $preparada = $conexion->prepare("
                            SELECT
                            bi.id AS 'bi.id', bi.fecha AS 'bi.fecha', bi.numeroDeCajas AS 'bi.numeroDeCajas', bi.observacion AS 'bi.observacion',
                            us.Clave AS 'us.id', us.Nombre AS 'us.nombre',
                            pr.Clave AS 'pr.id', pr.Nombre AS 'pr.nombre',
                            pa.id AS 'pa.id', pa.nombre AS 'pa.nombre'
                            FROM bitacoras bi
                            INNER JOIN Usuarios us ON us.Clave = bi.usuario
                            INNER JOIN Proveedores pr ON pr.Clave = bi.proveedor
                            INNER JOIN paqueterias pa ON pa.id = bi.paqueteria
                            ORDER BY bi.fecha DESC
                        ");
                    }
                    $preparada->execute();

                    $bitacoras = $preparada->fetchAll(PDO::FETCH_ASSOC);
        
                    if(!$bitacoras){
                        http_response_code(404);
                        echo json_encode(["error" => "No hay bitacoras que coincidan"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    $resultado = [];
                    foreach($bitacoras as $bitacora){
                        $resultado[] = [
                            "bitacora" => [
                                "id" => $bitacora["bi.id"],
                                "fecha" => $bitacora["bi.fecha"],
                                "numeroDeCajas" => $bitacora["bi.numeroDeCajas"],
                                "observacion" => $bitacora["bi.observacion"]
                            ],
                            "usuario" => [
                                "id" => $bitacora["us.id"],
                                "nombre" => $bitacora["us.nombre"]
                            ],
                            "proveedor" => [
                                "id" => $bitacora["pr.id"],
                                "nombre" => $bitacora["pr.nombre"]
                            ],
                            "paqueteria" => [
                                "id" => $bitacora["pa.id"],
                                "nombre" => $bitacora["pa.nombre"]
                            ],
                        ];
                    }

                    echo json_encode(["bitacoras" => $resultado], JSON_UNESCAPED_UNICODE);
                break;
                case 'DELETE':
                    $preparada = $conexion->prepare("DELETE FROM bitacoras WHERE id = :id");
                    $preparada->bindValue(":id", $id);
                    $preparada->execute();
        
                    if(!$preparada->rowCount()){
                        http_response_code(404);
                        echo json_encode(["error" => "No se elimino ninguna bitacora"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    http_response_code(204);
                break;
                case 'PUT':
                    $usuario = $datos['usuario'] ?? null;
                    $fecha = $datos['fecha'] ?? null;
                    $proveedor = $datos['proveedor'] ?? null;
                    $numeroDeCajas = $datos['numeroDeCajas'] ?? null;
                    $paqueteria = $datos['paqueteria'] ?? null;
                    $observacion = $datos['observacion'] ?? null;

                    $preparada = $conexion->prepare("UPDATE bitacoras SET usuario = :usuario, fecha = :fecha, proveedor = :proveedor, numeroDeCajas = :numeroDeCajas, paqueteria = :paqueteria, observacion = :observacion WHERE id = :id");
                    $preparada->bindValue(":usuario", $usuario);
                    $preparada->bindValue(":fecha", $fecha);
                    $preparada->bindValue(":proveedor", $proveedor);
                    $preparada->bindValue(":numeroDeCajas", $numeroDeCajas);
                    $preparada->bindValue(":paqueteria", $paqueteria);
                    $preparada->bindValue(":observacion", $observacion);
                    $preparada->bindValue(":id", $id);
                    $preparada->execute();
        
                    if(!$preparada->rowCount()){
                        http_response_code(404);
                        echo json_encode(["error" => "No se actualizo ninguna bitacora"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    http_response_code(204);
                break;
                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Metodo no permitido"], JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        else if($recurso == 'proveedores'){
            switch($metodo){
                case 'GET':
                    
                    if($id){
                        $preparada = $conexion->prepare("
                            SELECT Clave AS id, Nombre as nombre FROM Proveedores WHERE Clave = :id
                        ");
                        $preparada->bindValue(":id", $id);
                    }else{
                        $preparada = $conexion->prepare("
                            SELECT Clave AS id, Nombre as nombre FROM Proveedores ORDER BY Nombre DESC
                        ");
                    }
                    $preparada->execute();

                    $proveedores = $preparada->fetchAll(PDO::FETCH_ASSOC);
        
                    if(!$proveedores){
                        http_response_code(404);
                        echo json_encode(["error" => "No hay proveedores que coincidan"], JSON_UNESCAPED_UNICODE);
                        exit();
                    }

                    echo json_encode(["proveedores" => $proveedores], JSON_UNESCAPED_UNICODE);
                break;
                default:
                    http_response_code(405);
                    echo json_encode(["error" => "Metodo no permitido"], JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        else{
            http_response_code(404);
            echo json_encode(["error" => "Ruta no encontrada"], JSON_UNESCAPED_UNICODE);
        }
        
    }catch( Exception $exception ) {
        http_response_code(500);
        echo json_encode(["error" => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
    }
?>
