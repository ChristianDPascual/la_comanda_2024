<?php
require_once('C:\xampp\htdocs\la comanda\app\class\Persona.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');
require_once('C:\xampp\htdocs\la comanda\app\funciones\funciones.php');

class Comanda
{
    public $id_Atencion;
    public $legajo_Mozo;
    public $legajo_Preparacion;
    public $estado;
    public $fecha;
    public $demora; //entero, se usan minutos, y se convierte a formato tiempo
    public $id_Preparacion; //identificador del plato o bebida
    public $numero_Mesa; //numero de mesa del pedido
    public $clave; //clave alfa numerica
    public $hora; //hora de pedido

    //comprar entre tdas  las claves que exista y q sea el mail del cliente
    //CUANDO EL CLIENTE PAGA SE LE BORRA LA CLAVE QUE YA TIENE
    //si el cliente ya existe solo cargarle una clave nueva, si tiene la clave vacia
    //modificar clientes primero las funciones
    public static function validarComanda($email,$clave){
        $lista = Cliente :: traerClaves();
        $retorno = FALSE;
        if(validarEmail($email) && compararClavesAlfa($clave, $lista)){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare('SELECT * FROM clientes WHERE email = :email');
                $sentencia->bindValue(':email', $email);
                if($sentencia->execute()){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if($resultado["email"] == $email && $resultado["clave"] == $clave){
                        $retorno = TRUE;
                        return $retorno;
                    }
                    else{
                        echo "la clave no pertenece al cliente asociado";
                        return $retorno;
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }
        }
        else{
            echo "email o clave no valida";
        }
        return $retorno;
    }

    public static function validarDelete($clave,$id_Preparacion,$cantidad,$numero_Mesa,$accion){//valida que tenga el mismo id y clave para poder borrar la comanda
        $retorno = FALSE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM comandas WHERE id_Preparacion = :id_Preparacion AND clave = :clave 
                                        AND numero_Mesa = :numero_Mesa AND cantidad = :cantidad');
            $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
            $sentencia->bindValue(':clave', $clave);
            $sentencia->bindValue(':cantidad', $cantidad);
            $sentencia->bindValue(':numero_Mesa', $numero_Mesa);
            $pdo =null;
    
            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);

                if($accion == "/borrar/comanda"){
                    if(!empty($resultado)){
                        $retorno = TRUE;    
                    }
                }
                else{
                    if(!empty($resultado) && $resultado["estado"] != "cancelado"){
                        $retorno = TRUE;    
                    }
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

    public static function validarCambioEstadoComanda($clave,$id_Preparacion,$mesa,$cantidad){//valida los cambios de estado de la comanda
        $retorno = FALSE;
        try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE clave = :clave AND id_Preparacion = :id_Preparacion 
                                            AND cantidad = :cantidad AND numero_Mesa = :numero_Mesa");
                $sentencia->bindValue(':clave', $clave);
                $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                $sentencia->bindValue(':cantidad', $cantidad);
                $sentencia->bindValue(':numero_Mesa', $mesa);
                if($sentencia->execute()){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultado)){
                        return $resultado["estado"];
                    }
                    else{
                        echo "los datos no pertenecen a una comanda valida";
                        return $retorno;
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }
        return $retorno;  
    }

    public static function masVendido($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $listar = "listo para servir";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE estado = :estado");
                $sentencia->bindValue(':estado', $listar);
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(preparacionMasVendida($resultado)){
                        $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no se pudo encontra el listado"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }   
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function demorados($request, $response, $args){//pedidosDemorados($demora,$hora)
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $listar = "listo para servir";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE estado = :estado");
                $sentencia->bindValue(':estado', $listar);
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(empty($resultado)){
                        $payload = json_encode(array("mensaje"=>"no hay comandas"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        foreach($resultado as $c)
                        {
                            $id_Preparacion = $c["id_Preparacion"];
                            $plato =Preparaciones ::nombrePreparacion($c['id_Preparacion']);
                            $cantidad =$c["cantidad"];
                            $mesa =$c["numero_Mesa"];
                            $clave =$c["clave"];
                            $cocinero =Personal :: nombrePersonal($c["legajo_Preparacion"]);
                            $fecha =$c["fecha"];
                            $hora_inicial =$c["hora"];
                            $hora_final =$c["hora_Entrega"];
                            $demora =$c["demora"];
                            if(tiemposDemora($demora,$hora_inicial,$hora_final) == TRUE){
                                echo " $id_Preparacion $plato, cantidad $cantidad,mesa $mesa, clave $clave, cocinero $cocinero $fecha\n";
                            }
                        }

                        $payload = json_encode(array("mensaje"=>"listado de tiempo de demoras"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }   
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function sinDemorados($request, $response, $args){//pedidossinDemorados($demora,$hora)
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $listar = "listo para servir";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE estado = :estado");
                $sentencia->bindValue(':estado', $listar);
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(empty($resultado)){
                        $payload = json_encode(array("mensaje"=>"no hay comandas"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        foreach($resultado as $c)
                        {
                            $id_Preparacion = $c["id_Preparacion"];
                            $plato =Preparaciones ::nombrePreparacion($c['id_Preparacion']);
                            $cantidad =$c["cantidad"];
                            $mesa =$c["numero_Mesa"];
                            $clave =$c["clave"];
                            $cocinero =Personal :: nombrePersonal($c["legajo_Preparacion"]);
                            $fecha =$c["fecha"];
                            $hora_inicial =$c["hora"];
                            $hora_final =$c["hora_Entrega"];
                            $demora =$c["demora"];
                            if(tiemposSinDemora($demora,$hora_inicial,$hora_final) == TRUE){
                                echo " $id_Preparacion $plato, cantidad $cantidad,mesa $mesa, clave $clave, cocinero $cocinero $fecha\n";
                            }
                        }

                        $payload = json_encode(array("mensaje"=>"listado de tiempo de demoras"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }   
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verDemoras($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM comandas");

                if($sentencia->execute()){
                    $resultados = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultados)){
                        foreach ($resultados as $c){
                            $id = $c["id_Preparacion"];
                            $preparacion= Preparaciones :: nombrePreparacion($c["id_Preparacion"]);
                            $cantidad = $c["cantidad"];
                            $nroMesa = $c["numero_Mesa"];
                            $clave = $c["clave"];
                            $hora = $c["hora"];
                            $fecha = $c["fecha"];
                            $situacion = $c["estado"];
                            $demora = $c["demora"];  
                            if(!empty($demora)){
                                $mozo =Personal :: nombrePersonal($c["legajo_Mozo"]);
                                $cocinero = Personal :: nombrePersonal($c["legajo_Preparacion"]);
                                echo "demora $demora min $id $preparacion cantidad $cantidad estado $situacion mesa $nroMesa y clave $clave mozo $mozo cocinero $cocinero fecha y hora $fecha $hora\n";
                            }
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

    public static function verCancelados($request, $response, $args){
        $modo = token :: validarToken($request);
        $parametros = $request->getQueryParams();
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            try{
                $listar = "cancelado";  
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE estado = :estado");
                $sentencia->bindValue(':estado', $listar);

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        foreach($resultado as $c){
                            $preparacion= Preparaciones :: nombrePreparacion($c["id_Preparacion"]);
                            $estado = $c["estado"];
                            $fecha = $c["fecha"];
                            $hora = $c["hora"];
                            $mesa = $c["numero_Mesa"];
                            $clave= $c["clave"];
                            $cantidad = $c["cantidad"];
                            $mozo = Personal :: nombrePersonal($c["legajo_Mozo"]);            
                            echo "$preparacion $estado cantidad:$cantidad mozo: $mozo fecha:$fecha hora:$hora $clave ";
                            echo "\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de comandas canceladas mostradas exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron comandas canceladas"));
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

}
?>