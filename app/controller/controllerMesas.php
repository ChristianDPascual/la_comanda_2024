<?php
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Personal.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Preparaciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Mesa.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');


class ControllerMesas extends mesa implements InterfaceApiUsable{
    
    public static function crearUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $retorno = FALSE;
        $parametros = $request->getParsedBody();
            if(TRUE){
                $accion ="/alta/Mesa";
                $estado = "esperando pedido";
                $numero_Mesa = $parametros["numero_Mesa"];
                $total = 0;
                $clave = $parametros["clave"];
                $fecha = date("Y-m-d");
                $foto = null;

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare('INSERT INTO mesas (numero_Mesa, fecha, estado, total, clave, foto) 
                                                VALUES (:numero_Mesa, :fecha, :estado, :total, :clave, :foto) ');
                    $sentencia->bindValue(':numero_Mesa', $numero_Mesa);
                    $sentencia->bindValue(':estado', $estado);
                    $sentencia->bindValue(':total', $total);
                    $sentencia->bindValue(':clave', $clave);
                    $sentencia->bindValue(':foto', $foto);
                    $sentencia->bindValue(':fecha', $fecha);

    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion))
                    {
                        $pdo = null;
                        echo "se asigno la mesa exitosamente\n";
                        $retorno = TRUE;
                    }               
                }
                catch(PDOException $e)
                {
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                echo "Error al asignarse la mesa";
                $retorno = FALSE;
            }
            return $retorno;

    }

    public static function modificarUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $parametros = $request->getParsedBody();
        $accion = $request->getUri()->getPath();
        $clave = $parametros["clave"];
        $mesaNro = $parametros["numero_Mesa"];
        $fotoData = $request->getUploadedFiles()['foto'];
        $foto = file_get_contents($fotoData->getStream()->getMetadata('uri'));

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            if(Mesa :: disponibilidad_Clave($clave,$mesaNro) == "en uso"){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare('UPDATE mesas SET foto = :foto
                                                WHERE clave = :clave AND numero_Mesa = :numero_Mesa');
                    $sentencia->bindValue(':numero_Mesa', $mesaNro);
                    $sentencia->bindValue(':clave', $clave);
                    $sentencia->bindValue(':foto', $foto, PDO::PARAM_LOB);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion))
                    {
                        $sentencia = $pdo->prepare('SELECT foto FROM mesas WHERE clave = :clave AND numero_Mesa = :numero_Mesa');
                        $sentencia->bindValue(':numero_Mesa', $mesaNro);
                        $sentencia->bindValue(':clave', $clave);
                        $sentencia->execute();
                        $foto = $sentencia->fetchColumn();
                        $pdo = null;
                        
                        header('Content-Type: image/jpeg');
                        header('Content-Length: ' . strlen($foto));
                        echo $foto;
                        exit;
                    }               
                }
                catch(PDOException $e)
                {
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                $payload = json_encode(array("mensaje"=>"la clave no pertenece a la mesa, o la foto no se cargo correctamente"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function mostrarTodos($request, $response, $args){
        $modo = token :: validarToken($request);
        $parametros = $request->getQueryParams();
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            try{
                $listar = $parametros["listar"];
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                if($listar == "todas"){
                    $sentencia = $pdo->prepare("SELECT * FROM mesas");
                }
                else{
                    if($listar == "esperando pedido" || $listar == "comiendo" || $listar == "pagando" || $listar == "cerrada"){
                        $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE estado = :estado");
                        $sentencia->bindValue(':estado', $listar);
                    }
                    else{
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"listado seleccionado no valido o nulo"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    } 
                }
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        foreach($resultado as $m){
            
                            $numero_Mesa = $m["numero_Mesa"];
                            $estado = $m["estado"];
                            $clave = $m["clave"];
                            $fecha = $m["fecha"];
                            echo "mesa $numero_Mesa $estado $clave $fecha";

                            if($estado == "pagando"){
                                $total = Mesa :: calcularTotal($clave);//funcin
                                echo "total a abonar $total";//calcular total
                            }

                            if($estado == "cerrada"){
                                $total = Mesa :: calcularTotal($clave);//funcin
                                echo "total pagado $total";//calcular total
                            }
                            echo "\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de mesas mostradas exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron mesas"));
                        $response->getBody()->write($payload);
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
                $response->getBody()->write($payload);
                echo "Error: " .$e->getMessage();
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function mostrarUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $parametros = $request->getQueryParams();
        $accion = $request->getUri()->getPath();
        $clave = $parametros["clave"];

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE clave = :clave");
                $sentencia->bindValue(':clave', $clave);
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        $numero_Mesa = $resultado["numero_Mesa"];
                        $estado = $resultado["estado"];
                        $fecha = $resultado["fecha"];
                        echo "mesa $numero_Mesa $estado $clave $fecha";

                        if($estado == "pagando"){
                            $total = Mesa :: calcularTotal($clave);//funcin
                            echo "total a abonar $total";//calcular total
                        }

                        if($estado == "cerrada"){
                            $total = Mesa :: calcularTotal($clave);//funcin
                            echo "total pagado $total";//calcular total
                        }
                        $payload = json_encode(array("mensaje"=>"mesa mostrada exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraro la mesa"));
                        $response->getBody()->write($payload);
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
                $response->getBody()->write($payload);
                echo "Error: " .$e->getMessage();
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function eliminarUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $parametros = $request->getParsedBody();
        $clave = $parametros["clave"];
        $numero_Mesa = $parametros["numero_Mesa"];

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){

            if(Mesa :: disponibilidad_Clave($clave,$numero_Mesa) == "en uso"){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("DELETE FROM mesas WHERE clave = :clave AND numero_Mesa = :numero_Mesa");
                    $sentencia->bindValue(':clave', $clave);
                    $sentencia->bindValue(':numero_Mesa', $numero_Mesa);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"la mesa ha sido borrada"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                catch(PDOException $e){
                    $pdo = null;
                    $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
                    $response->getBody()->write($payload);
                    echo "Error: " .$e->getMessage();
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            else{
                $payload = json_encode(array("mensaje"=>"la clave no corresponde a la mesa"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    } 

    public static function cambiarEstado($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $parametros = $request->getParsedBody();
        $estado = $parametros["estado"];
        $clave = $parametros["clave"];

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo" ) && $modo["estado"] == TRUE){

                if(validarEstadoMesa($parametros["estado"]) && Mesa :: validarPreparacionesEnMesas($clave) == TRUE ){
                    $validacion = FALSE;
                    try{
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="christian";
                        $pass ="cp35371754";
                        $pdo = new PDO($conStr,$user,$pass);
                        switch($estado){
                            case "comiendo":
                                if(Mesa :: validarCambioEstadoMesa($clave) == "esperando pedido"){
                                    $validacion = TRUE;
                                    $situacion = "comiendo";
                                }
                                else{
                                    echo "no se pueden saltear estados";
                                }
                                break;
                            case "pagando":
                                if(Mesa :: validarCambioEstadoMesa($clave) == "comiendo"){
                                    $validacion = TRUE;
                                    $situacion = "pagando";
                                }
                                else{
                                    echo "no se pueden saltear estados";
                                }
                                break;
                            case "lista para cerrar":
                                if($modo["categoria"] == "socio" && Mesa :: validarCambioEstadoMesa($clave) == "lista para cerrar"){//solo el socio la puede cerrar
                                    $validacion = TRUE;
                                    $situacion = "cerrada";
                                }
                                else{
                                    echo "categoria invalida para la accion";
                                }
                            break;
                        }
    
                        if($validacion == FALSE){
                            $pdo = null;
                            $payload = json_encode(array("mensaje"=>"error al modificar el estado de la mesa"));
                            $response->getBody()->write($payload);
                        }
                        else{
                            
                            $sentencia = $pdo->prepare("UPDATE mesas SET estado = :estado WHERE clave = :clave");
                            $sentencia->bindValue(':estado', $situacion);
                            $sentencia->bindValue(':clave', $clave);  
                            if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                                $pdo = null;
                                $payload = json_encode(array("mensaje"=>"la mesa fue modificada correctamente"));
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                            $pdo = null;
                        }
                    }
                    catch(PDOException $e){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
                        $response->getBody()->write($payload);
                        echo "Error: " .$e->getMessage();
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    
                }
                else{
                    $payload = json_encode(array("mensaje"=>"el cambio de estado no se pudo realizar ya que no pertenece a su categoria, no es la mesa correcta o el estado que quiere asignarle no es valido"));
                    $response->getBody()->write($payload);
                }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}