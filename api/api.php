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
        }else{
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
        }

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
                    echo json_encode(["id" => $conexio->lastInsertId()], JSON_UNESCAPED_UNICODE);
                break;
                case 'GET':

                    if($id){
                        $preparada = $conexion->prepare("SELECT * FROM paqueterias WHERE id = :id");
                        $preparada->bindValue(":id", $id);
                    }else{
                        $preparada = $conexion->prepare("SELECT * FROM paqueterias");
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
