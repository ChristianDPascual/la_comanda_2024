<?php

class Evaluaciones
{
    public static function encuesta($request, $response, $args){//solo si el estado de deuda es libre, que el dni ya exista y que
        $parametros = $request->getParsedBody();
        $clave = $parametros["clave"];
        $dni = $parametros["dni"];
        $puntaje_Mozo = $parametros["puntaje_Mozo"];
        $puntaje_Mesa = $parametros["puntaje_Mesa"];
        $puntaje_Cocinero = $parametros["puntaje_Cocinero"];
        $puntaje_Restaurante = $parametros["puntaje_Restaurante"];
        $comentarios = $parametros["comentarios"];
        $numero_Mesa = $parametros["numero_Mesa"];

        if(validarEncuensta($clave,$dni,$puntaje_Cocinero,$puntaje_Mesa,$puntaje_Mozo,$puntaje_Restaurante,$comentarios,$numero_Mesa) == TRUE &&
        Evaluaciones :: disponibilidadEncuesta($clave) == TRUE){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
            
                $sentencia = $pdo->prepare('INSERT INTO evaluacion (clave, puntaje_Mozo, puntaje_Cocinero, puntaje_Restaurante, puntaje_Mesa,
                                                                    comentarios, numero_Mesa) 
                                                VALUES (:clave, :puntaje_Mozo, :puntaje_Cocinero, :puntaje_Restaurante, :puntaje_Mesa,
                                                        :comentarios, :numero_Mesa)');
                $sentencia->bindValue(':clave', $clave);
                $sentencia->bindValue(':puntaje_Mozo', $puntaje_Mozo);
                $sentencia->bindValue(':puntaje_Cocinero', $puntaje_Cocinero);
                $sentencia->bindValue(':puntaje_Mesa', $puntaje_Mesa);
                $sentencia->bindValue(':puntaje_Restaurante', $puntaje_Restaurante);
                $sentencia->bindValue(':comentarios', $comentarios);
                $sentencia->bindValue(':numero_Mesa', $numero_Mesa);
               
                if($sentencia->execute()){
                    $pdo = null;
                    $payload = json_encode(array("mensaje"=>"la encuesta se cargo exitosamente"));
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
            $payload = json_encode(array("mensaje"=>"argumentos de la encuesta invalidos"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
        
    }

    public static function disponibilidadEncuesta($clave){
        $retorno = FALSE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT * FROM evaluacion WHERE clave = :clave');
            $sentencia->bindValue(":clave",$clave);

            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
                $pdo =null;
                if(empty($resultado)){
                    $retorno = TRUE;
                }
                else{
                    echo "Esa encuesta ya se encuentra cargada";
                    $retorno = FALSE;
                }
                return $retorno;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }
        return $retorno;
    }

    public static function listarEncuestas($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $contador = 0;

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM evaluacion");
    
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                    $pdo =null;
                    if(!empty($resultado)){
                        if($accion == "/listar/mejores-comentarios"){
                            echo "mejores comentarios con una puntuacion igual o superior a 34 puntos entre los 4 items \n";
                            foreach($resultado as $c){
                                $total = $c["puntaje_Mozo"] + $c["puntaje_Cocinero"] + $c["puntaje_Mesa"] + $c["puntaje_Restaurante"];
                                if($total >= 34 && !empty($c["comentarios"])){
                                    $contador++;
                                    echo $c["comentarios"]."\n";
                                }
                                $total = 0; 
                            }
                        }
                        if($accion == "/listar/peores-comentarios"){
                            echo "peores comentarios con una puntuacion igual o inferior a 20 puntos entre los 4 items \n";
                            foreach($resultado as $c){
                                $total = $c["puntaje_Mozo"] + $c["puntaje_Cocinero"] + $c["puntaje_Mesa"] + $c["puntaje_Restaurante"];
                                if($total <= 20 && !empty($c["comentarios"])){
                                    $contador++;
                                    echo $c["comentarios"]."\n";
                                } 
                                $total = 0; 
                            }
                        }
                        if($contador == 0){
                            $payload = json_encode(array("mensaje"=>"sin encuentas aun"));
                            $response->getBody()->write($payload);
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                            $response->getBody()->write($payload); 
                        }
                        return $response->withHeader('Content-Type', 'application/json'); 
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no hay encuestas"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json'); 
                    }
    
                }
            }
            catch(PDOException $e){
                $pdo = null;
                throw new Exception("Error al conectarse a la base de datos");
            }
        }
        $payload = json_encode(array("mensaje"=>"usuario invalido"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json'); 
    }

}
?>