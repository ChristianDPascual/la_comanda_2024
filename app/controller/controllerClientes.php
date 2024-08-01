<?php
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');

class ControllerClientes extends Cliente implements InterfaceApiUsable{
    
    public static function crearUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE) 
        {
            $parametros = $request->getParsedBody();
            if(verificarDatosCliente($parametros)){
                $nombre = $parametros["nombre"];
                $apellido = $parametros["apellido"];
                $dni = $parametros["dni"];
                $fecha = new DateTime($parametros["fecha_Nacimiento"]);
                $email = $parametros["email"];
                $categoria = "cliente";
                $deuda = "pendiente";
                $clave = Cliente :: claveAlfanumerica();

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare('INSERT INTO clientes (nombre, apellido, dni, fecha_Nacimiento, 
                                                                      email, categoria, estado_Deuda, clave) 
                                                VALUES (:nombre, :apellido, :dni, :fecha, :email, :categoria, :deuda, :clave)');
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':apellido', $apellido);
                    $sentencia->bindValue(':dni', $dni);
                    $sentencia->bindValue(':email', $email);
                    $sentencia->bindValue(':categoria', $categoria);
                    $sentencia->bindValue(':fecha', $fecha->format('Y-m-d'));
                    $sentencia->bindValue(':deuda', $deuda);
                    $sentencia->bindValue(':clave', $clave);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion))
                    {
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." fue creado con exito\n"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }               
                }
                catch(PDOException $e)
                {
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR al crear el cliente"));
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

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            $parametros = $request->getParsedBody();
            if(validarCadena($parametros["nombre"]) && validarCadena($parametros["apellido"]) && validarDNI($parametros["dni"]) && 
               validarDNI($parametros["dni_Antiguo"]) && validarEmail($parametros["email"]) && validarFecha($parametros["fecha_Nacimiento"]) &&
            (Cliente :: traerDatosCliente($parametros["dni_Antiguo"]) !== null || $parametros["dni_Antiguo"] == $parametros["dni"])){
                $nombre = $parametros["nombre"];
                $apellido = $parametros["apellido"];
                $dni = $parametros["dni"];
                $email = $parametros["email"];
                $dni_antiguo = $parametros["dni_Antiguo"];
                $fecha_Nacimiento = new DateTime($parametros["fecha_Nacimiento"]);

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE clientes SET nombre = :nombre, apellido = :apellido, 
                                                                    fecha_Nacimiento = :fecha_Nacimiento, email = :email, dni = :dni
                                                WHERE dni = :dni_Antiguo");
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':apellido', $apellido);
                    $sentencia->bindValue(':dni', $dni);
                    $sentencia->bindValue(':email', $email);
                    $sentencia->bindValue(':fecha_Nacimiento', $fecha_Nacimiento->format('Y-m-d'));
                    $sentencia->bindValue(':dni_Antiguo', $parametros["dni_Antiguo"]);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"cliente: ".$nombre." ".$apellido." fue modificado correctamente\n"));
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
                $payload = json_encode(array("mensaje" => "ERROR al modificar el cliente"));
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

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM clientes");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        $contador = 0;

                        foreach($resultado as $c){
                            $nombre = $c["nombre"];
                            $apellido = $c["apellido"];
                            $dni = $c["dni"];
                            $email = $c["email"];
                            $fecha_Nacimiento = $c["fecha_Nacimiento"];
                            $clave = $c["clave"];
                            $deuda = $c["estado_Deuda"];
                            echo "$nombre $apellido $dni $email $fecha_Nacimiento $clave $deuda\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de clientes mostrado exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron clientes"));
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

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            $parametros = $request->getQueryParams();
            $dni = $parametros["dni"];

            if(validarDNI($dni)){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("SELECT * FROM clientes WHERE dni = :dni");
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        
                        if(!empty($resultado)){
                            $nombre = $resultado["nombre"];
                            $apellido = $resultado["apellido"];
                            $dni = $resultado["dni"];
                            $email = $resultado["email"];
                            $clave = $resultado["clave"];
                            $fecha_Nacimiento = $resultado["fecha_Nacimiento"];
                            $estado_Deuda = $resultado["estado_Deuda"];

                            if( $estado_Deuda == "libre"){
                                $payload = json_encode(array("mensaje"=>"$nombre $apellido $dni $email $clave $fecha_Nacimiento"));
                                $response->getBody()->write($payload);
                            }
                            else{
                                $payload = json_encode(array("mensaje"=>"cuidado cliente deudor $nombre $apellido $dni $email $clave $fecha_Nacimiento"));
                                $response->getBody()->write($payload);

                            }
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"No se encontro un cliente con ese DNI"));
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

    public static function eliminarUno($request, $response, $args){//solo lo puede eliminar el socio por si tiene deuda pendiente
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();
            $dni = $parametros["dni"];

            if(validarDNI($dni) && Cliente :: traerDatosCliente($parametros["dni"]) !== null){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("DELETE FROM clientes WHERE dni = :dni");
                    $sentencia->bindValue(':dni', $dni);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"$dni ha sido borrado"));
                        $response->getBody()->write($payload);
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

    public static function cambiarEstado($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $parametros = $request->getParsedBody();

            if(validarDNI($parametros["dni"]) && Cliente :: traerDatosCliente($parametros["dni"]) !== null &&
               ($parametros["estado"] == "libre" || $parametros["estado"] == "deudor")){
                $estado_Deuda = $parametros["estado"];
                $dni =$parametros["dni"];
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE clientes SET estado_Deuda = :estado_Deuda WHERE dni = :dni");
                    $sentencia->bindValue(':estado_Deuda', $estado_Deuda);
                    $sentencia->bindValue(':dni', $dni);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"el estado del cliente fue modificado correctamente"));
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
                $payload = json_encode(array("mensaje" => "ERROR al modificar al cliente"));
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