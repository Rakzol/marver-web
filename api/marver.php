<?php
    header('Content-Type: application/json');

    try{

        require_once 'JWTHelper.php';

        if( $_SERVER['REQUEST_METHOD'] != 'POST' ){
            http_response_code(400);
            echo json_encode(["error" => "Metodo de consulta invalido"], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $datos = json_decode(file_get_contents('php://input'), true);
        $sucursal = $datos['sucursal'] ?? '';

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

        $accion = $_GET['accion'] ?? '';

        if($accion == 'login' ){
            $clave = $datos['clave'] ?? '';
            $contraseña = $datos['contraseña'] ?? '';
    
            $preparada = $conexion->prepare("SELECT TOP 1 Clave, Perfil FROM Usuarios WHERE Clave = :clave AND Contraseña = :contrasena");
            $preparada->bindValue(":clave", $clave);
            $preparada->bindValue(":contrasena", $contraseña);
            $preparada->execute();

            $usuario = $preparada->fetch(PDO::FETCH_ASSOC);

            if($usuario){

                $payload = [
                    "clave" => $usuario['Clave'],
                    "perfil" => $usuario['Perfil'],
                    "iat" => time(),
                    "exp" => time() + (60 * 60 * 24 *30)
                ];

                $token = generarJWT($payload);

                echo json_encode(["token" => $token], JSON_UNESCAPED_UNICODE);
            }else{
                http_response_code(401);
                echo json_encode(["error" => "Credenciales invalidas"], JSON_UNESCAPED_UNICODE);
            }
        }
        else if($accion == "validar"){
            $token = obtenerJWT();
            $payload = validarJWT($token);
            
            if($payload){
                echo json_encode(["payload" => $payload], JSON_UNESCAPED_UNICODE);
                exit();
            }

            http_response_code(401);
            echo json_encode(["token" => $token, "error" => "Token invalido"], JSON_UNESCAPED_UNICODE);
        }

        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"], JSON_UNESCAPED_UNICODE);
        
    }catch( Exception $exception ) {
        http_response_code(500);
        echo json_encode(["error" => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
    }
?>
