<?php
require_once('C:\xampp\htdocs\la comanda\app\funciones\funciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');

class Mesa
{
    public $numero_Mesa;
    public $total;
    public $estado;
    public $foto;
    public $disponibilidad;
    public $id_Atencion;//es unico


    public static function traerClaves(){
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT clave FROM mesas');
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

    //funcion q verifica si la emsa esta asiganda en la comanda realmente esta libre
    //funcion q solo trae el numero de la mesa y si disponibilidad es si o no, para esto lo comparo con el estado;
    //traigo los id de servicio de esa mesas, y verifico q esos id de servicio tengan todos el estado cerrada si hay alguno q no tenga
    //eso, signifca que la mesa se encuentra abierta.
    //ej mesa numero 10 primero traigo de mi base de datos todos los id_Servicios asignados a ese numero de mesa
    //recorro el array, si algunos de esos id_Servicio tiene el estado dif a cerrada significa que la mesa no esta disp
    
    public static function numeroMesaLibre($mesaNro){//verifico que ese numero de mesa se encuentre libre
        $retorno = FALSE;
        if(validarNumero($mesaNro) && ($mesaNro >= 1 && $mesaNro <= 15)){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE numero_Mesa = :numero_Mesa");
                $sentencia->bindValue(':numero_Mesa', $mesaNro);

                if($sentencia->execute()){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                
                    if(!empty($resultado)){
                        $retorno = TRUE;
                        foreach($resultado as $m){
                            if ($m["estado"] !== "cerrada"){
                                $retorno = FALSE;
                                break;
                            }
                        }
                    }
                    else{
                        $retorno = TRUE;
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }
        }
        else{
            echo "la mesa ingresada no es valida";
        }
        return $retorno;
    }

    public static function disponibilidad_Clave($clave,$mesaNro){
        //recibo la clave y verificio devuelvo como retorno nueva = si hay q crear una mesa nueva, en uso = si ya tiene la misma mesa asignada
        //usada si ya esta asignada otra mesa con el estado dif a cerrada
        //si esa clave no esta asignad aa ninguna mesa, procedo a crear una, codigo = nueva;
        //si esa clave esta asignada a maesas, q su estado sea en todas cerrada;
        //si esta a asignada a alguna mesa y se encuentra alguna con el estado dif a cerrada no se podra crear;
        $disponibilidad = "usada";
        if(validarNumero($mesaNro) && ($mesaNro >= 1 && $mesaNro <= 15)){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE clave = :clave");
                $sentencia->bindValue(':clave', $clave);
    
                if($sentencia->execute()){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(empty($resultado) && Mesa :: numeroMesaLibre($mesaNro)){//si el array viene nulo, significa que la clave no tien mesa asociada, por lo tanto es una  mesa nueva
                        $disponibilidad = "nueva";
                        return $disponibilidad;
                    }
                    else{
                        foreach($resultado as $m){//si la mesa tiene esa clave y el estado es dif a cerrada significa, que esta en uso
                            if ($m["estado"] !== "cerrada" && $m["clave"] == $clave && $m["numero_Mesa"] == $mesaNro){
                                $disponibilidad = "en uso";
                                break;
                            }
                        }
                        return $disponibilidad;
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }
        }
        return $disponibilidad;
    }

    public static function calcularTotal($clave){//calcula el total de la mesa
        $retorno = FALSE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $estado = "listo para servir";
            $pdo = new PDO($conStr,$user,$pass);
            $total = 0;
            $acumulador = 0;

            $sentencia = $pdo->prepare("SELECT preparaciones.precio, comandas.estado, comandas.cantidad FROM comandas JOIN preparaciones 
                                        ON comandas.id_Preparacion = preparaciones.id_Preparacion WHERE comandas.clave = :clave");
            $sentencia->bindValue(':clave', $clave);

            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                $pdo = null;
                if(!empty($resultado)){//si el array viene nulo, significa que la clave no tien mesa asociada, por lo tanto es una  mesa nueva
                    
                    foreach ($resultado as $c){
                        $acumulador = $c["precio"]*$c["cantidad"];
                        $total = $acumulador + $total;
                        $retorno = $total;

                        if($c["estado"] !== "listo para servir"){
                            echo " aun existen pedidos que no fueron entregado s";
                            $retorno = FALSE;
                            break;
                        }
                    }
                    return $retorno;
                }
                else{
                    echo "no se encontraron comandas con esa clave";
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

    public static function validarCambioEstadoMesa($clave){//valida los cambios de estado de la comanda
        $retorno = FALSE;
        try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE clave = :clave");
                $sentencia->bindValue(':clave', $clave);

                if($sentencia->execute()){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultado)){
                        return $resultado["estado"];
                    }
                    else{
                        echo "los datos no pertenecen a una mesa valida";
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

    public static function validarPreparacionesEnMesas($clave){//valido que todas las preparaciones con esa clave tengan el estado listo para servir
        $retorno = FALSE;                                     //no puedo permitir un cambio de estado de mesa si tengo una comida en pendiente
        try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT comandas.estado FROM mesas JOIN comandas 
                                            ON comandas.clave = mesas.clave AND mesas.clave = :clave");
                $sentencia->bindValue(':clave', $clave);

                if($sentencia->execute()){
                    $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultado)){
                        $retorno = TRUE;
                        foreach($resultado as $m){
                            if($m["estado"] !== "listo para servir"){
                                echo "hay preparaciones que no fueron entregadas";
                                $retorno = FALSE;
                                break;
                            }
                        }
                        return $retorno;
                    }
                    else{
                        echo "la clave no es correcta";
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

    public static function cobrar($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $parametros = $request->getParsedBody();
        $clave = $parametros["clave"];
        $numero_Mesa = $parametros["numero_Mesa"];

        if(($modo["categoria"] == "socio" || $modo["categoria"] == "mozo") && $modo["estado"] == TRUE ){ 
            if(Mesa :: disponibilidad_Clave($clave,$numero_Mesa) == "en uso"){
                if(Mesa :: validarCambioEstadoMesa($clave) == "pagando"){
                    $total = Mesa :: calcularTotal($clave);
                    $situacion = "lista para cerrar";

                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE mesas SET estado = :estado, total = :total WHERE clave = :clave");
                    $sentencia->bindValue(':estado', $situacion);
                    $sentencia->bindValue(':total', Mesa :: calcularTotal($clave));
                    $sentencia->bindValue(':clave', $clave);

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $pdo = null;
                        $payload = json_encode(array("mensaje"=>"se abono correctamente el importe $total"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }

                }
                else{
                    $payload = json_encode(array("mensaje"=>"error, la mesa no esta en estado de pagando"));
                    $response->getBody()->write($payload);
                }
            }
            else{
                $payload = json_encode(array("mensaje"=>"la clave y mesa no son compatibles"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
        
    }

    public static function cerrarMesas($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $parametros = $request->getParsedBody();
        $clave = $parametros["clave"];
        $numero_Mesa = $parametros["numero_Mesa"];

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){ 
            if(Mesa :: disponibilidad_Clave($clave,$numero_Mesa) == "en uso"){
                if(Mesa :: validarCambioEstadoMesa($clave) == "lista para cerrar"){
                    $situacion = "cerrada";
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE mesas SET estado = :estado WHERE clave = :clave");
                    $sentencia->bindValue(':estado', $situacion);
                    $sentencia->bindValue(':clave', $clave);
                    $pdo = null;

                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion) && Cliente :: liberarCliente($clave) == TRUE){
                        $payload = json_encode(array("mensaje"=>"se cerro correctamente la mesa $numero_Mesa"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }

                }
                else{
                    $payload = json_encode(array("mensaje"=>"error, la mesa no esta lista para cerrar"));
                    $response->getBody()->write($payload);
                }
            }
            else{
                $payload = json_encode(array("mensaje"=>"la clave y mesa no son compatibles"));
                $response->getBody()->write($payload);
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
        
    }

    public static function mostrarListasParaCerrar($request, $response, $args){
        $modo = token :: validarToken($request);
        $parametros = $request->getQueryParams();
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio"  && $modo["estado"] == TRUE ){
            try{
                $listar = "lista para cerrar";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE estado = :estado");
                $sentencia->bindValue(':estado', $listar);

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultado)){
                        foreach($resultado as $m){ 
                            $numero_Mesa = $m["numero_Mesa"];
                            $clave = $m["clave"];
                            $fecha = $m["fecha"];
                            $total = Mesa :: calcularTotal($clave);
                            echo "$numero_Mesa $clave $fecha total pagado $total \n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado de mesas mostradas exitosamente"));
                        $response->getBody()->write($payload);

                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no hay mesas listas para cerrar"));
                        $response->getBody()->write($payload);
                    }
                    return $response->withHeader('Content-Type', 'application/json');
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

    public static function disponibilidad_Encuesta($clave,$mesaNro,$dni){
        $retorno = FALSE;
        $datosCliente = Cliente :: traerDatosCliente($dni);
        if(validarNumero($mesaNro) && ($mesaNro >= 1 && $mesaNro <= 15) && !empty($datosCliente)){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM mesas WHERE clave = :clave AND numero_Mesa = :numero_Mesa");
                $sentencia->bindValue(':clave', $clave);
                $sentencia->bindValue(':numero_Mesa', $mesaNro);
    
                if($sentencia->execute()){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(empty($resultado)){//si el array viene nulo, significa que la clave no tien mesa asociada, por lo tanto es una  mesa nueva
                        return $retorno;
                    }
                    else{
                        foreach($resultado as $m){
                            foreach($datosCliente as $c){
                                if($c["clave"] == $m["clave"] && $m["estado"] == "cerrada" && $c["estado_Deuda"] == "libre"){
                                    $retorno = TRUE;
                                    break;
                                }
                            }
                        }
                        return $retorno;
                    }
                }
            }
            catch(PDOException $e){
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }
        }
        return $retorno;
    }

    public static function masUsada($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT numero_Mesa, COUNT(*) as uso FROM mesas GROUP BY numero_Mesa ORDER BY uso DESC");  
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        foreach ($resultado as $m) {
                            echo "Mesa: ". $m['numero_Mesa']." acumulado de veces que se utilizo: ". $m['uso']."\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no se uso ninguna mesa"));
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

    public static function mejoresFacturaciones($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $estado= "cerrada";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT numero_Mesa, SUM(total) as total_facturado FROM mesas WHERE estado =:estado
                GROUP BY numero_Mesa ORDER BY total_facturado DESC");
                $sentencia->bindValue(':estado', $estado);

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        foreach($resultado as $m){
                            echo "Mesa: ".$m['numero_Mesa']." Total Facturado: $".$m['total_facturado']."\n";
                        }
                        $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no se uso ninguna mesa"));
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

    public static function laFactura($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            try{
                $estado= "cerrada";
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                if($accion == "/listar/mejor-factura"){
                    $sentencia = $pdo->prepare("SELECT numero_Mesa, total, clave, fecha FROM mesas WHERE estado =:estado
                    GROUP BY clave ORDER BY total DESC");
                    $sentencia->bindValue(':estado', $estado);
                }
                else{
                    $sentencia = $pdo->prepare("SELECT numero_Mesa, total, clave, fecha FROM mesas WHERE estado =:estado
                    GROUP BY clave ORDER BY total ASC");
                    $sentencia->bindValue(':estado', $estado);
                }
                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                    $pdo = null;

                    if(!empty($resultado)){
                        echo "Mesa: ".$resultado['numero_Mesa']." Total Facturado: $".$resultado['total']." clave ".$resultado["clave"].
                        " fecha ".$resultado["fecha"]."\n";
                        $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"no se uso ninguna mesa"));
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

    public static function facturacionEntreFechas($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            $parametros = $request->getQueryParams();
            $fecha_Inicial = $parametros["fecha_Inicial"];
            $fecha_Fin = $parametros["fecha_Fin"];
            $fechaI = DateTime::createFromFormat('d-m-Y', $fecha_Inicial)->format('Y-m-d');
            $fechaF =DateTime::createFromFormat('d-m-Y', $fecha_Fin)->format('Y-m-d');

            if($fechaI <= $fechaF){
                try{
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="christian";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    
                    $sentencia = $pdo->prepare("SELECT numero_Mesa, SUM(total) AS total_facturado FROM mesas 
                                               WHERE fecha BETWEEN :fecha_Inicial AND :fecha_Fin GROUP BY numero_Mesa");
                    $sentencia->bindValue(':fecha_Inicial', $fechaI);
                    $sentencia->bindValue(':fecha_Fin', $fechaF);
                    $sentencia->execute();
                    
                    if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                        $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                        $pdo = null;

                        if(!empty($resultado)){
                            foreach ($resultado as $m) {
                                echo "Numero de mesa: ".$m['numero_Mesa']." Total Facturado:".$m['total_facturado']."\n";
                            }
                            $payload = json_encode(array("mensaje"=>"listado mostrado exitosamente"));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        else{
                            $payload = json_encode(array("mensaje"=>"no se uso ninguna mesa en esas fechas"));
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
                $payload = json_encode(array("mensaje"=>"fechas ingresadas invalidas"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }    
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario no valido"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function mesasLibres($numero_Mesa){
        //Idem pero recorro uno x uno el numero de las 10 mesas que tenemos en total
        //si alguna en todos sus estados es igual  a cerrada esa mesa esta libre
    }
}
?>