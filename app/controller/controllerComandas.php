<?php
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Personal.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Preparaciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Mesa.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerMesas.php');


class ControllerComandas extends Comanda implements InterfaceApiUsable{

    public static function crearUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE) 
        {
            $parametros = $request->getParsedBody();
            $clave = $parametros["clave"];
            $email = $parametros["email"];
            $disponibilidad = Mesa :: disponibilidad_Clave($parametros["clave"],$parametros["numero_Mesa"]);

            switch($disponibilidad)
            {
                case "nueva":
                    if(Comanda :: validarComanda($email,$clave) && ControllerMesas :: crearUno($request, $response, $args)){
                        $legajo= Personal :: traerUnLegajo($modo["email"]);
        
                        if(Preparaciones :: preparacionRepetida($parametros["id_Preparacion"]) == FALSE 
                           && $legajo !== null && validarNumero($parametros["cantidad"]) && $parametros["cantidad"] > 0){
                            $cantidad = $parametros["cantidad"];
                            $id_Preparacion = $parametros["id_Preparacion"];
                            $fecha = date("Y-m-d");
                            $hora = date("H:i");
                            $numero_Mesa = $parametros["numero_Mesa"];
                            $Legajo_Preparacion = 0;
                            $hora_Entrega = 0;
                            $demora = 0;
                            $estado = "pendiente";
        
                            try{
                                $conStr = "mysql:host=localhost;dbname=la_comanda";
                                $user ="christian";
                                $pass ="cp35371754";
                                $pdo = new PDO($conStr,$user,$pass);
                        
                                $sentencia = $pdo->prepare('INSERT INTO comandas (hora_Entrega, legajo_Mozo, estado, fecha, demora, id_Preparacion, 
                                                                                  numero_Mesa, clave, hora, cantidad) 
                                                            VALUES (:hora_Entrega, :legajo_Mozo, :estado, :fecha, :demora, :id_Preparacion, 
                                                                    :numero_Mesa, :clave, :hora, :cantidad)');
                                $sentencia->bindValue(':hora_Entrega', $hora_Entrega);
                                $sentencia->bindValue(':legajo_Mozo', $legajo);
                                $sentencia->bindValue(':estado', $estado);
                                $sentencia->bindValue(':fecha', $fecha);
                                $sentencia->bindValue(':demora', $demora);
                                $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                                $sentencia->bindValue(':numero_Mesa', $numero_Mesa);
                                $sentencia->bindValue(':clave', $clave);
                                $sentencia->bindValue(':hora', $hora);
                                $sentencia->bindValue(':cantidad', $cantidad);
                
                                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                                    $pdo = null;
                                    $payload = json_encode(array("mensaje"=>"la orden fue creada con exito\n"));
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
                            echo "el id de la preparacion no es valido";
                        }

                    }
                    else
                    {
                        $payload = json_encode(array("mensaje" => "ERROR al crear la comanda"));
                        $response->getBody()->write($payload);
                    }
                    break;
                case "en uso":
                    if(Comanda :: validarComanda($email,$clave)){
                        $legajo= Personal :: traerUnLegajo($modo["email"]);
        
                        if(Preparaciones :: preparacionRepetida($parametros["id_Preparacion"]) == FALSE 
                           && $legajo !== null && validarNumero($parametros["cantidad"]) && $parametros["cantidad"] > 0){
                            $cantidad = $parametros["cantidad"];
                            $id_Preparacion = $parametros["id_Preparacion"];
                            $fecha = date("Y-m-d");
                            $hora = date("H:i");
                            $numero_Mesa = $parametros["numero_Mesa"];
                            $Legajo_Preparacion = 0;
                            $hora_Entrega = 0;
                            $demora = 0;
                            $estado = "pendiente";
        
                            try{
                                $conStr = "mysql:host=localhost;dbname=la_comanda";
                                $user ="christian";
                                $pass ="cp35371754";
                                $pdo = new PDO($conStr,$user,$pass);
                        
                                $sentencia = $pdo->prepare('INSERT INTO comandas (hora_Entrega, legajo_Mozo, estado, fecha, demora, id_Preparacion, 
                                                                                  numero_Mesa, clave, hora, cantidad) 
                                                            VALUES (:hora_Entrega, :legajo_Mozo, :estado, :fecha, :demora, :id_Preparacion, 
                                                                    :numero_Mesa, :clave, :hora, :cantidad)');
                                $sentencia->bindValue(':hora_Entrega', $hora_Entrega);
                                $sentencia->bindValue(':legajo_Mozo', $legajo);
                                $sentencia->bindValue(':estado', $estado);
                                $sentencia->bindValue(':fecha', $fecha);
                                $sentencia->bindValue(':demora', $demora);
                                $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                                $sentencia->bindValue(':numero_Mesa', $numero_Mesa);
                                $sentencia->bindValue(':clave', $clave);
                                $sentencia->bindValue(':hora', $hora);
                                $sentencia->bindValue(':cantidad', $cantidad);
                
                                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                                    $pdo = null;
                                    $payload = json_encode(array("mensaje"=>"la orden fue creada con exito"));
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
                            echo "el id de la preparacion no es valido";
                        }
                    }
                    else{
                        $payload = json_encode(array("mensaje" => "ERROR al crear la comanda"));
                        $response->getBody()->write($payload);
                    }
                    break;
                case "usada":
                    $payload = json_encode(array("mensaje"=>"la mesa o la clave esta en uso"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                    break; 

            }
            
        }
        else{
            $payload = json_encode(array("mensaje" => "ERROR en el ingreso de las credenciales"));
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

                if($listar == "todos"){
                    $sentencia = $pdo->prepare("SELECT * FROM comandas");
                }
                else{
                    if($listar == "en preparacion" || $listar == "pendiente" || $listar == "listo para servir"){
                        $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE estado = :estado");
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

                            if($estado == "en preparacion"){
                                $cocinero = Personal :: nombrePreparacion($c["legajo_Preparacion"]);
                                $demora = calcularDemora($c["demora"],$hora);
                                echo "cocinero: ".$cocinero." ".$demora;
                            }
                            if($estado == "listo para servir"){
                                $hora_Entrega = $c["hora_Entrega"];
                                echo " hora de entrega $hora_Entrega";
                            }
                            echo "\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de comandas mostradas exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontraron comandas"));
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
            $clave = $parametros["clave"];
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM comandas WHERE clave= :clave");
                $sentencia->bindValue(':clave', $clave);

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
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

                            if($estado == "en preparacion"){
                                $cocinero = Personal :: nombrePersonal($c["legajo_Preparacion"]);
                                $demora = calcularDemora($c["demora"],$hora);
                                echo "cocinero: ".$cocinero." ".$demora;
                            }
                            if($estado == "listo para servir"){
                                $hora_Entrega = $c["hora_Entrega"];
                                echo " hora de entrega $hora_Entrega";
                            }
                            echo "\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de comandas mostradas exitosamente"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No se encontro comandas"));
                        $response->getBody()->write($payload);
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

    public static function eliminarUno($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            $parametros = $request->getParsedBody();
            $clave = $parametros["clave"];
            $id_Preparacion = $parametros["id_Preparacion"];
            $cantidad = $parametros["cantidad"];
            $numero_Mesa = $parametros["numero_Mesa"];

            if(Comanda :: validarDelete($clave,$id_Preparacion,$cantidad,$numero_Mesa,$accion)){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("DELETE FROM comandas WHERE clave = :clave AND id_Preparacion = :id_Preparacion");
                    $sentencia->bindValue(':clave', $clave);
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"la comanda ha sido eliminada"));
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
                $payload = json_encode(array("mensaje"=>"Error, en el ingreso de clave o preparacion"));
                $response->getBody()->write($payload);
            }

        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function mostrarPedidos($request, $response, $args){
        $modo = token :: validarToken($request);
        $sector = $modo["categoria"];
        $parametros = $request->getQueryParams();
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "cocina" || $modo["categoria"] == "choperas" || $modo["categoria"] == "tragos"
        || $modo["categoria"] == "candy") && $modo["estado"] == TRUE){
            try{
                $listar = $parametros["listar"];
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                if($listar === "todos"){
                    
                    $sentencia = $pdo->prepare("SELECT * FROM comandas t1 INNER JOIN preparaciones t2 
                                                WHERE t2.sector = :sector AND t1.id_Preparacion = t2.id_Preparacion");
                    $sentencia->bindValue(':sector', $sector);
                    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $resultados = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                        $pdo = null;
                        if(!empty($resultados)){
                           
                            foreach ($resultados as $c){
                                $preparacion= Preparaciones :: nombrePreparacion($c["id_Preparacion"]);
                                $nroMesa = $c["numero_Mesa"];
                                $mozo = Personal:: nombrePersonal($c["legajo_Mozo"]);
                                $hora = $c["hora"];
                                $fecha = $c["fecha"];
                                $cantidad = $c["cantidad"];
                                $situacion = $c["estado"];
                                echo "$preparacion $cantidad $situacion $nroMesa $fecha $hora $mozo \n";
                            }
                            $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                            $response->getBody()->write($payload);
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"no se encontraron pedidos con esa categoria"));
                            $response->getBody()->write($payload);
                        }
                    }
                }
                else{
                    if($listar == "en preparacion" || $listar == "pendiente" || $listar == "listo para servir"){
                        
                        $sentencia = $pdo->prepare("SELECT * FROM comandas t1 INNER JOIN preparaciones t2 
                                                WHERE t2.sector = :sector AND t1.estado = :estado AND t1.id_Preparacion = t2.id_Preparacion");
                        $sentencia->bindValue(':sector', $sector);
                        $sentencia->bindValue(':estado', $listar);
                        if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){                  
                            $resultados = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                            $pdo = null;
                            if(!empty($resultados)){
                                foreach ($resultados as $c){
                                    $preparacion= Preparaciones :: nombrePreparacion($c["id_Preparacion"]);
                                    $nroMesa = $c["numero_Mesa"];
                                    $mozo = Personal:: nombrePersonal($c["legajo_Mozo"]);
                                    $hora = $c["hora"];
                                    $fecha = $c["fecha"];
                                    $cantidad = $c["cantidad"];
                                    $situacion = $c["estado"];
                                    echo "$preparacion $cantidad $situacion $nroMesa $fecha $hora $mozo";
                                    if($situacion == "en preparacion"){
                                        $cocinero = Personal :: nombrePersonal($c["legajo_Preparacion"]);
                                        $demora = calcularDemora($c["demora"],$hora);
                                        echo "cocinero: ".$cocinero." ".$demora;
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
                                $payload = json_encode(array("mensaje"=>"no se encontraron pedidos con esa categoria"));
                                $response->getBody()->write($payload);
                            }
                        }
                        else{
                            $pdo = null;
                            $payload = json_encode(array("mensaje"=>"listado seleccionado no valido o nulo"));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"seleccion de listado invalida"));
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

    public static function modificarUno($request, $response, $args){//para modificar el pedido por parte del mozo
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            $parametros = $request->getParsedBody();
            $clave = $parametros["clave"];
            $email = $parametros["email"];
            $nroMesa = $parametros["numero_Mesa"];
            $id_Preparacion = $parametros["id_Preparacion"];
            $disponibilidad = Mesa :: disponibilidad_Clave($clave,$nroMesa);

            if(Comanda :: validarComanda($email,$clave) && $disponibilidad == "en uso"){

                $legajo= Personal :: traerUnLegajo($modo["email"]);
                if(Preparaciones :: preparacionRepetida($parametros["id_Preparacion"]) == FALSE &&
                   $legajo !== null && validarNumero($parametros["cantidad"]) && $parametros["cantidad"] > 0){
                    $cantidad = $parametros["cantidad"];
                    $id_PreparacionNuevo = $parametros["id_PreparacionNuevo"];
                    $estado = "pendiente";//tiene q volver a pendeinte por q se genera una nueva orden para q la vea el cocinero
                    $fecha = date("Y-m-d");
                    $hora = date("H:i");//nueva hora y fecha

                    try{
                        $conStr = "mysql:host=localhost;dbname=la_comanda";
                        $user ="christian";
                        $pass ="cp35371754";
                        $pdo = new PDO($conStr,$user,$pass);
                
                        $sentencia = $pdo->prepare("UPDATE comandas SET id_Preparacion = :id_PreparacionNuevo, cantidad = :cantidad, estado = :estado,
                                                                        legajo_Mozo = :legajo_Mozo, fecha = :fecha, hora = :hora, demora = :demora,
                                                                        legajo_Preparacion = :legajo_Preparacion, hora_Entrega = :hora_Entrega
                                                    WHERE clave = :clave AND id_Preparacion = :id_Preparacion");
                        $sentencia->bindValue(':hora_Entrega', 0);
                        $sentencia->bindValue(':legajo_Mozo', $legajo);
                        $sentencia->bindValue(':estado', $estado);
                        $sentencia->bindValue(':fecha', $fecha);
                        $sentencia->bindValue(':demora', 0);
                        $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                        $sentencia->bindValue(':id_PreparacionNuevo', $id_PreparacionNuevo);
                        $sentencia->bindValue(':clave', $clave);
                        $sentencia->bindValue(':hora', $hora);
                        $sentencia->bindValue(':cantidad', $cantidad);
                        $sentencia->bindValue(':legajo_Preparacion', null);
    
                        if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                            $pdo = null;
                            $payload = json_encode(array("mensaje"=>"la comanda fue modificada correctamente"));
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
                    $payload = json_encode(array("mensaje" => "ERROR con los atributos para modificar"));
                $response->getBody()->write($payload);
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "ERROR en la clave, nro de mesa o email"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public static function cambiarEstado($request, $response, $args){//para q los cocineros puedan administrar el estado
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $parametros = $request->getParsedBody();

        if(($modo["categoria"] == "cocina" || $modo["categoria"] == "choperas" || $modo["categoria"] == "tragos"||
            $modo["categoria"] == "candy") && $modo["estado"] == TRUE){
                if(validarEstadoPreparacion($parametros["estado"])  &&
                Preparaciones :: traerCategoriaPreparacion($parametros["id_Preparacion"]) == $modo["categoria"] &&
                "en uso" == Mesa :: disponibilidad_Clave($parametros["clave"],$parametros["numero_Mesa"])){
                    $clave = $parametros["clave"];//envio la clave
                    $nroMesa = $parametros["numero_Mesa"];//el numero de mesa
                    $estado = $parametros["estado"];//el estado al cual voy a cambiar
                    $id_Preparacion = $parametros["id_Preparacion"];//el id de la preparacion
                    $legajo= Personal :: traerUnLegajo($modo["email"]);//obtengo el legajo de la persona con el token
                    $hora = date("H:i:s");//nueva hora y fecha del sistema
                    $cantidad = $parametros["cantidad"];
                    $validacion = FALSE;

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);

                    switch($estado){
                        case "en preparacion":
                            $demora = $parametros["demora"];
                            if(Comanda :: validarCambioEstadoComanda($clave,$id_Preparacion,$nroMesa,$cantidad) == "pendiente" &&
                               validarNumero($parametros["demora"]) && $parametros["demora"]>0){
                                $validacion = TRUE;
                                $sentencia = $pdo->prepare("UPDATE comandas SET estado = :estado, hora = :hora, demora = :demora,
                                                            legajo_Preparacion = :legajo_Preparacion
                                                            WHERE clave = :clave AND id_Preparacion = :id_Preparacion 
                                                            AND cantidad = :cantidad AND numero_Mesa = :numero_Mesa");
                                                    $sentencia->bindValue(':estado', $estado);
                                                    $sentencia->bindValue(':demora', $demora);
                                                    $sentencia->bindValue(':hora', $hora);
                                                    $sentencia->bindValue(':legajo_Preparacion', $legajo);
                                                    $sentencia->bindValue(':clave', $clave);
                                                    $sentencia->bindValue(':cantidad', $cantidad);
                                                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                                                    $sentencia->bindValue(':numero_Mesa', $nroMesa);
                            }
                            break;
                        case "listo para servir":
                            if(Comanda :: validarCambioEstadoComanda($clave,$id_Preparacion,$nroMesa,$cantidad,$estado) == "en preparacion"){
                                $validacion = TRUE;
                                $sentencia = $pdo->prepare("UPDATE comandas SET estado = :estado, hora_Entrega = :hora_Entrega,
                                legajo_Preparacion = :legajo_Preparacion WHERE clave = :clave AND id_Preparacion = :id_Preparacion 
                                AND cantidad = :cantidad AND numero_Mesa = :numero_Mesa");
                                $sentencia->bindValue(':estado', $estado);
                                $sentencia->bindValue(':hora_Entrega', $hora);
                                $sentencia->bindValue(':legajo_Preparacion', $legajo);
                                $sentencia->bindValue(':clave', $clave);
                                $sentencia->bindValue(':cantidad', $cantidad);
                                $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                                $sentencia->bindValue(':numero_Mesa', $nroMesa);
                            }
                            break;
                    }

                    if($validacion == FALSE){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"error al modificar el estado de preparacion"));
                        $response->getBody()->write($payload);
                    }
                    else{
                        if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                            $pdo = null;
                            $payload = json_encode(array("mensaje"=>"la comanda fue modificada correctamente"));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        $pdo = null;
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

    public static function cancelar($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $estado_pendiente = "pendiente";
        $estado_Nuevo = "cancelado";


        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){
            $parametros = $request->getParsedBody();
            $clave = $parametros["clave"];
            $id_Preparacion = $parametros["id_Preparacion"];
            $cantidad = $parametros["cantidad"];
            $numero_Mesa = $parametros["numero_Mesa"];

            if(Comanda :: validarDelete($clave,$id_Preparacion,$cantidad,$numero_Mesa,$accion)){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
    
                    $sentencia = $pdo->prepare("UPDATE comandas SET estado = :estado WHERE clave = :clave AND id_Preparacion = :id_Preparacion 
                                               AND estado = :estado_pendiente AND cantidad = :cantidad");
                    $sentencia->bindValue(':clave', $clave);
                    $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
                    $sentencia->bindValue(':estado_pendiente', $estado_pendiente);
                    $sentencia->bindValue(':cantidad', $cantidad);
                    $sentencia->bindValue(':estado', $estado_Nuevo);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"la comanda ha sido cancelada"));
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
                $payload = json_encode(array("mensaje"=>"Error, en el ingreso de clave o preparacion"));
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