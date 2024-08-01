<?php
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');

class ControllerPreparaciones extends Preparaciones implements InterfaceApiUsable{
    public static function crearUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE) 
        {
            $parametros = $request->getParsedBody();
            if(verificarDatosPreparacion($parametros) && Preparaciones :: preparacionExistente($parametros["nombre"])){
                $nombre = $parametros["nombre"];
                $precio = $parametros["precio"];
                $sector = $parametros["sector"];
                $mayor18 = $parametros["mayor18"];
                $id_Preparacion = Preparaciones :: id_Preparacion();

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare('INSERT INTO preparaciones (nombre, precio, sector, mayor18, id_Preparacion) 
                                                VALUES (:nombre, :precio, :sector, :mayor18, :id_Preparacion)');
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':precio', $precio);
                    $sentencia->bindValue(':sector', $sector);
                    $sentencia->bindValue(':mayor18', $mayor18);
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion))
                    {
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"preparacion: ".$nombre." fue creado con exito\n"));
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
                $payload = json_encode(array("mensaje" => "ERROR al crear la preparacion"));
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

        if($modo["categoria"] == "socio"  && $modo["estado"] == TRUE ){
            $parametros = $request->getParsedBody();
            if(verificarDatosPreparacion($parametros) && Preparaciones :: traerDatosPreparacion($parametros["id_Preparacion"]) !== null){
                $nombre = $parametros["nombre"];
                $precio = $parametros["precio"];
                $mayor18 = $parametros["mayor18"];
                $sector = $parametros["sector"];
                $id_Preparacion = $parametros["id_Preparacion"];

                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE preparaciones SET nombre = :nombre, precio = :precio, sector = :sector, mayor18 = :mayor18
                                                WHERE id_Preparacion = :id_Preparacion");
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':precio', $precio);
                    $sentencia->bindValue(':sector', $sector);
                    $sentencia->bindValue(':mayor18', $mayor18);
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"preparacion: ".$nombre." fue modificada correctamente\n"));
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
                $payload = json_encode(array("mensaje" => "ERROR al modificar la preparacion"));
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

        if($modo["estado"] == TRUE){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM preparaciones");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    $contador = 0;

                    if(!empty($resultado)){
                        foreach($resultado as $p){
                            $id_Preparacion = $p["id_Preparacion"];
                            $nombre = $p["nombre"];
                            $sector = $p["sector"];
                            $precio = $p["precio"];
                            $mayor18 = $p["mayor18"];
                            echo "$id_Preparacion $nombre $sector $precio $mayor18\n";
                            $contador++;
                        }
                        $payload = json_encode(array("mensaje"=>"cantidad de preparaciones $contador"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron preparaciones"));
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

        if($modo["estado"] == TRUE){
            $parametros = $request->getQueryParams();

            if(Preparaciones :: traerDatosPreparacion($parametros["id_Preparacion"]) !== null){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("SELECT * FROM preparaciones WHERE id_Preparacion = :id_Preparacion");
                    $sentencia->bindValue(':id_Preparacion', $parametros["id_Preparacion"]);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        
                        if(!empty($resultado)){
                            $id_Preparacion = $resultado["id_Preparacion"];
                            $nombre = $resultado["nombre"];
                            $sector = $resultado["sector"];
                            $precio = $resultado["precio"];
                            $mayor18 = $resultado["mayor18"];
                            echo "$id_Preparacion $nombre $sector $precio $mayor18\n";
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"No se encontro una preparacion con ese id"));
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
                $payload = json_encode(array("mensaje"=>"Error, al ingresar el id"));
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
            $id_Preparacion = $parametros["id_Preparacion"];

            if(Preparaciones :: traerDatosPreparacion($parametros["id_Preparacion"]) !== null){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("DELETE FROM preparaciones WHERE id_Preparacion = :id_Preparacion");
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"$id_Preparacion ha sido borrado"));
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
                $payload = json_encode(array("mensaje"=>"Error, al ingresar el id"));
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

            if(Preparaciones :: traerDatosPreparacion($parametros["id_Preparacion"]) !== null && validarPrecio($parametros["precio"])){
                $precio = $parametros["precio"];
                $id_Preparacion =$parametros["id_Preparacion"];
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
            
                    $sentencia = $pdo->prepare("UPDATE preparaciones SET precio = :precio WHERE id_Preparacion = :id_Preparacion");
                    $sentencia->bindValue(':precio', $precio);
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"el precio fue modificado correctamente"));
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
                $payload = json_encode(array("mensaje" => "ERROR al modificar el precio"));
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