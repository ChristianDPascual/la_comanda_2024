<?php

class Registros
{
    public $fecha;
    public $hora;
    public $accion;
    public $email;

    public static function registro($email,$accion)
    {
        try
        {
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $fecha = date('Y-m-d H:i:s');
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("INSERT INTO registros (fecha,email,accion)
                                        VALUES (:fecha,:email,:accion)");
            $sentencia->bindValue(':fecha', $fecha);
            $sentencia->bindValue(':email', $email);
            $sentencia->bindValue(':accion', $accion);

            if($sentencia->execute())
            {
                $pdo =null;
                return true;
            }
            else
            {
                $pdo =null;
                return false;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
        
    }

    public static function registroLogin($request, $response, $args){//a-	Los días y horarios que se ingresaron al sistema. 
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $registro = "/inicio/login";
        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM registros WHERE accion =:accion");
                $sentencia->bindValue(":accion",$registro);

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        $contador = 0;

                        foreach($resultado as $r){
                            $email = $r["email"];
                            $fecha = $r["fecha"];
                            $reg = $r["accion"];
                            echo "$email $fecha $reg\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de logins mostrado exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron logins"));
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

    public static function registroSectoresEmpleado($request, $response, $args){//cantidad de operaciones por sector
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $area = array("socio", "mozo", "candy", "cocina", "tragos", "choperas");
        $contador = 0;

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT registros.*, personal.categoria, personal.email FROM registros JOIN personal 
                ON registros.email = personal.email");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(empty($resultado)){
                        $payload = json_encode(array("mensaje"=>"No se encontrarn acciones por parte del personal"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{

                        $datos = depurarArrayPersonal($resultado);
                        for($i=0;$i<5;$i++){
                            echo $area[$i]."\n";
                            foreach($datos as $d)
                            { 
                                echo "email ".$d["email"]."\n";
                                foreach($resultado as $r){
                                    if($d["categoria"] == $r["categoria"] && $d["email"] == $r["email"] && $d["categoria"] == $area[$i])
                                    {
                                        $contador++;
                                        $fecha = $r["fecha"];
                                        $email = $r["email"];
                                        $reg = $r["accion"];
                                        echo "$email $reg $fecha\n";
                                    }
                                }
                                echo "cantidad de acciones $contador\n\n";
                                $contador = 0;
                            }
                        }
                        $payload = json_encode(array("mensaje"=>"se mostro exitosamente el listado"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');

                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos"));
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

    public static function registroSectores($request, $response, $args){//cantidad de operacion por cada empleado de cada sector
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $area = array("socio", "mozo", "candy", "cocina", "tragos", "choperas");
        $contador = 0;

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT registros.*, personal.categoria FROM registros JOIN personal ON registros.email = personal.email");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(empty($resultado)){
                        $payload = json_encode(array("mensaje"=>"No se encontrarn acciones por parte del personal"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        foreach($area as $a){
                            echo "Sector $a\n";

                            foreach($resultado as $r){
                                $fecha = $r["fecha"];
                                $email = $r["email"];
                                $reg = $r["accion"];
                                if($a == $r["categoria"]){
                                    $contador++;
                                    //echo "$email $reg $fecha\n";
                                }
                            }
                            echo "Cantida de operaciones del sector $contador\n\n";
                            $contador = 0;
                        }
                        $payload = json_encode(array("mensaje"=>"se mostro exitosamente el listado"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');

                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos"));
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
}
?>