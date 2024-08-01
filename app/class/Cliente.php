<?php
require_once('C:\xampp\htdocs\la comanda\app\class\Persona.php');
require_once('C:\xampp\htdocs\la comanda\app\funciones\funciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Comanda.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Mesa.php');

class Cliente extends Persona
{
    /**public $nombre;
    public $apellido;
    public $dni;
    public $mail;
    public $categoria; */
    public $clave;
    public $fecha_Nacimiento;
    public $deuda;

    public static function claveAlfanumerica(){
        $clave = FALSE;
        do{
            $clave = generarClaveAlfanumerica();
            $listaC = Cliente :: traerClaves();
            $listaM = Mesa :: traerClaves();
        }while(compararClavesAlfa($clave,$listaC) == FALSE && compararClavesAlfa($clave,$listaM) == FALSE);
        return $clave;
    }

    public static function traerClaves(){
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT clave FROM clientes');
            $pdo =null;
    
            if($sentencia->execute())
            {
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);                
                return $resultado;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }

    }

    public static function clienteRepetido($dni){
        $retorno = TRUE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT dni, estado_Deuda FROM clientes');
            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                $pdo =null;
                if(empty($resultado)){
                    $retorno = TRUE;
                }
                else{
                    foreach($resultado as $d){
                        if($d["dni"] === $dni && $d["estado_Deuda"] !== "libre")
                        {
                            echo "dni actualmente en uso\n";
                            $retorno = FALSE;
                            break;
                        }
                    }
                }
                return $retorno;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }
        return FALSE;
    }

    public static function traerDatosCliente($dni){//traigo todos los datos del cliente en forma de array solo con el dni
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM clientes WHERE dni = :dni');
            $sentencia->bindValue(':dni', $dni);
            if($sentencia->execute()){
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                $pdo = null;

                if(isset($resultado) && is_array($resultado)){
                    return $resultado;
                }
                else{
                    return null;
                }
    
            }
        }
        catch(PDOException $e){
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function verClave($request, $response, $args){
        $parametros = $request->getQueryParams();
        $email = $parametros["email"];

        if(!empty($email)){
            try{
                $estado ="pendiente";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
        
                $sentencia = $pdo->prepare('SELECT * FROM clientes
                                            WHERE email = :email AND estado_Deuda = :estado_Deuda');
                $sentencia->bindValue(':estado_Deuda', $estado);
                $sentencia->bindValue(':email', $email);
    

                if($sentencia->execute()){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    $c = $resultado["clave"];
                    $payload = json_encode(array("mensaje"=>"la clave es $c"));
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
            $payload = json_encode(array("mensaje"=>"el email no tiene clave asignada"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verMisPedidos($request, $response, $args){
        $parametros = $request->getQueryParams();
        $clave = $parametros["clave"];
        $email = $parametros["email"];
        if(Comanda :: validarComanda($email,$clave)){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE clave = :clave");
                $sentencia->bindValue(':clave', $clave);

                if($sentencia->execute()){
                    $resultados = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultados)){
                        foreach ($resultados as $c){
                            $preparacion= Preparaciones :: nombrePreparacion($c["id_Preparacion"]);
                            $nroMesa = $c["numero_Mesa"];
                            $hora = $c["hora"];
                            $fecha = $c["fecha"];
                            $cantidad = $c["cantidad"];
                            $situacion = $c["estado"];
                            echo "$preparacion"." cantidad"." $cantidad $situacion"." mesa "."$nroMesa $fecha $hora";
                            if($situacion == "en preparacion"){
                                $demora = calcularDemora($c["demora"],$hora);
                                echo " ".$demora;
                            }
                            if($situacion == "listo para servir"){
                                $hora_Entrega = $c["hora_Entrega"];
                                echo " hora de entrega $hora_Entrega";
                            }
                            echo "\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no hay ordenes para mostrar"));
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
            $payload = json_encode(array("mensaje"=>"error en los datos ingresados"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public static function liberarCliente($clave){
        
        $retorno = FALSE;
        try{
            $estado = "libre";
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("UPDATE clientes SET estado_Deuda = :estado_Deuda WHERE clave = :clave");
            $sentencia->bindValue(':estado_Deuda', $estado);
            $sentencia->bindValue(':clave', $clave);
            if($sentencia->execute()){
                $pdo = null;
                $retono = TRUE;
                return $retorno;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            $payload = json_encode(array("mensaje"=>"Error al realizar la coneccion con la base de datos\n"));
            $response->getBody()->write($payload);
            echo "Error: " .$e->getMessage();
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $retorno;
    } 
    

}
?>