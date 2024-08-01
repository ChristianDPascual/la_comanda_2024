<?php
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');

class ControllerPersonal extends Personal implements InterfaceApiUsable{

    public static function login($request, $response, $args){
        $parametros = $request->getParsedBody();// parseo la respuesta enviada
        $email = $parametros["email"];//obtengo el mail
        $aux = Personal :: obtenerDatosLogin($email);//obtengo los datos con el mail en la base de datos
        

        if($aux["dni"] !== null && $aux["estado"] == "activo"){//verifico el dni y q se encuentre en un estado activo el personal
            
            $accion = $request->getUri()->getPath();
            if(Registros :: registro($email,$accion)){
                $cat = $aux["categoria"];//obtengo su categoria
                $token = token::crearToken($aux["dni"], $aux["categoria"]);//llamo a la funcion token y le envio el dni y la categoria
                $payload = json_encode(array("mensaje" => "OK. $cat", "token" => $token));//devuelvo el codigo generado en la respuesta
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR al guardar los registros"));
            }
            
            $cat = $aux["categoria"];//obtengo su categoria
            $token = token::crearToken($aux["dni"], $aux["categoria"]);//llamo a la funcion token y le envio el dni y la categoria
            $payload = json_encode(array("mensaje" => "OK. $cat", "token" => $token));//devuelvo el codigo generado en la respuesta
        }
        else{
            $payload = json_encode(array("mensaje" => "ERROR en el ingreso de las credenciales $email"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function crearUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();
            if(verificarDatosPersonal($parametros)){
                $nombre = $parametros["nombre"];
                $apellido = $parametros["apellido"];
                $dni = $parametros["dni"];
                $fecha_Ingreso = date("Y-m-d");
                $email = $parametros["email"];
                $categoria = $parametros["categoria"];
                $estado = "activo";
                $fecha_Baja = "1900-1-1";
                $legajo = Personal :: legajos();

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare('INSERT INTO personal (nombre, apellido, dni, email, categoria, legajo,
                                                                      fecha_Ingreso, fecha_Baja, estado) 
                                                VALUES (:nombre, :apellido, :dni, :email, :categoria, :legajo,
                                                                      :fecha_Ingreso, :fecha_Baja, :estado) ');
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':apellido', $apellido);
                    $sentencia->bindValue(':dni', $dni);
                    $sentencia->bindValue(':email', $email);
                    $sentencia->bindValue(':categoria', $categoria);
                    $sentencia->bindValue(':fecha_Ingreso', $fecha_Ingreso);
                    $sentencia->bindValue(':fecha_Baja', $fecha_Baja);
                    $sentencia->bindValue(':estado', $estado);
                    $sentencia->bindValue(':legajo', $legajo);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"personal: ".$nombre." ".$apellido." fue creado con exito\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }               
                }
                catch(PDOException $e){
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR al crear el personal"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje" => "ERROR en el ingreso de las credenciales"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function modificarUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();
            if(validarCadena($parametros["nombre"]) && validarCadena($parametros["apellido"]) && validarDNI($parametros["dni"]) && 
               validarDNI($parametros["dni_Antiguo"]) && validarEmail($parametros["email"]) && validarCategoria($parametros["categoria"]) &&
            (Personal:: traerDatosPersonal($parametros["dni_Antiguo"]) !== null || $parametros["dni_Antiguo"] == $parametros["dni"])){
                $nombre = $parametros["nombre"];
                $apellido = $parametros["apellido"];
                $dni = $parametros["dni"];
                $email = $parametros["email"];
                $categoria = $parametros["categoria"];

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE personal SET nombre = :nombre, apellido = :apellido,
                                                                     email = :email, categoria = :categoria, dni = :dni
                                                WHERE dni = :dni_Antiguo");
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':apellido', $apellido);
                    $sentencia->bindValue(':dni', $dni);
                    $sentencia->bindValue(':email', $email);
                    $sentencia->bindValue(':categoria', $categoria);
                    $sentencia->bindValue(':dni_Antiguo', $parametros["dni_Antiguo"]);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"personal: ".$nombre." ".$apellido." fue modificado correctamente\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }               
                }
                catch(PDOException $e){
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR al modificar el personal"));
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
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM personal");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        $contador = 0;

                        foreach($resultado as $p){
                            if($p["estado"] == "activo"){
                                $contador++;
                                $nombre = $p["nombre"];
                                $apellido = $p["apellido"];
                                $dni = $p["dni"];
                                $email = $p["email"];
                                $categoria = $p["categoria"];
                                $legajo = $p["legajo"];
                                echo "$nombre $apellido $dni $email $categoria $legajo\n";
                            }
                        }
                        $payload = json_encode(array("mensaje"=>"cantidad de personal activo $contador"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron empleados"));
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
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getQueryParams();
            $dni = $parametros["dni"];

            if(validarDNI($dni)){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("SELECT * FROM personal WHERE dni = :dni");
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        
                        if(!empty($resultado)){
                            $nombre = $resultado["nombre"];
                            $apellido = $resultado["apellido"];
                            $dni = $resultado["dni"];
                            $email = $resultado["email"];
                            $categoria = $resultado["categoria"];
                            $legajo = $resultado["legajo"];
                            $estado = $resultado["estado"];
                            $fecha_Ingreso = $resultado["fecha_Ingreso"];

                            if($resultado["estado"] == "activo"){
                                $payload = json_encode(array("mensaje"=>"$nombre $apellido $dni $email $categoria $legajo $estado $fecha_Ingreso"));
                                $response->getBody()->write($payload);
                            }
                            else{
                                $fecha_Baja = $resultado["fecha_Baja"];
                                $payload = json_encode(array("mensaje"=>"$nombre $apellido $dni $email $categoria $legajo $estado $fecha_Ingreso $fecha_Baja "));
                                $response->getBody()->write($payload);

                            }
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"No se encontro un empleado con ese DNI"));
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
                $payload = json_encode(array("mensaje"=>"Error, al ingresar el DNI"));
                $response->getBody()->write($payload);
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

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];

            if(validarDNI($dni) && Personal :: traerDatosPersonal($parametros["dni"]) !== null){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("DELETE FROM personal WHERE dni = :dni");
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"$dni ha sido borrado"));
                        $response->getBody()->write($payload);
                    }
                }
                catch(PDOException $e)
                {
                    $pdo = null;
                    $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
                    $response->getBody()->write($payload);
                    echo "Error: " .$e->getMessage();
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error, al ingresar el DNI"));
                $response->getBody()->write($payload);
            }

        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function cambiarEstado($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();

            if(validarDNI($parametros["dni"]) && Personal :: traerDatosPersonal($parametros["dni"]) !== null &&
               ($parametros["estado"] == "activo" || $parametros["estado"] == "despedido")){
                $estado = $parametros["estado"];
                $dni =$parametros["dni"];
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE personal SET estado = :estado WHERE dni = :dni");
                    $sentencia->bindValue(':estado', $estado);
                    $sentencia->bindValue(':dni', $dni);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"el estado de personal fue modificado correctamente"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }               
                }
                catch(PDOException $e){
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR al modificar el personal"));
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
?>