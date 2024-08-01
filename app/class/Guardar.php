<?php

class Guardar
{
    public static function guardarComandasCSV($request, $response, $args){
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){

            $directorio = 'C:\xampp\htdocs\la comanda\app\archives';
            $ruta = $directorio . '/comandas.csv';//la ruta de mi directorio a donde voy a guardar el archivo
            if (!is_dir($directorio)) {//si no existe lo creo
                if (!mkdir($directorio, 0777, true)) {
                    $payload = json_encode(array("mensaje" => "Error al crear el directorio"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            try{
                $conStr = "mysql:host=localhost;dbname=la_comanda";
                $user ="christian";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);
    
                $sentencia = $pdo->prepare("SELECT * FROM comandas");

                if($sentencia->execute() && Registros :: registro($modo["email"],$accion)){
                    $resultado = $sentencia->fetchAll(PDO :: FETCH_ASSOC);
                    $pdo = null;
                    if(!empty($resultado)){
                        $archivo = fopen($ruta,"w");
                        foreach($resultado as $c){
                            $preparacion = $c["id_Preparacion"];
                            $estado = $c["estado"];
                            $fecha = $c["fecha"];
                            $hora = $c["hora"];
                            $mesa = $c["numero_Mesa"];
                            $clave= $c["clave"];
                            $cantidad = $c["cantidad"];
                            $mozo = $c["legajo_Mozo"];
                            if($estado == "pendiente"){
                                if(fwrite($archivo,"$preparacion,$estado,$fecha,$hora,$mesa,$clave,$cantidad,$mozo\n")==0){
                                    $payload = json_encode(array("mensaje"=>"Ocurrio un error al guardar el armamento"));
                                    $response->getBody()->write($payload);
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            }
                            if($estado == "en preparacion"){
                                $cocinero = $c["legajo_Preparacion"];
                                $demora = $c["demora"];
                                if(fwrite($archivo,"$preparacion,$estado,$fecha,$hora,$mesa,$clave,$cantidad,$mozo,$cocinero,$demora\n")==0){
                                    $payload = json_encode(array("mensaje"=>"Ocurrio un error al guardar el armamento"));
                                    $response->getBody()->write($payload);
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            }
                            if($estado == "listo para servir"){
                                $cocinero = $c["legajo_Preparacion"];
                                $demora = $c["demora"];
                                $hora_Entrega = $c["hora_Entrega"];
                                if(fwrite($archivo,"$preparacion,$estado,$fecha,$hora,$mesa,$clave,$cantidad,$mozo,$cocinero,$demora,$hora_Entrega\n")==0){
                                    $payload = json_encode(array("mensaje"=>"Ocurrio un error al guardar el armamento"));
                                    $response->getBody()->write($payload);
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            }
                        }
                        fclose($archivo);
                        $payload = json_encode(array("mensaje"=>"comandas guardadas en un archivo.csv con exito"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                    else{
                        $payload = json_encode(array("mensaje"=>"No hay comandas para guardar"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
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
        else
        {
            $payload = json_encode(array("mensaje"=>"error de autenticacion"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function cargarComandasCSV($request, $response, $args)
    {
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $ruta = 'C:\xampp\htdocs\la comanda\app\archives\comandas.csv';

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE ){
            if(file_exists($ruta)){
                $archivo = fopen($ruta,"r");
                $valores = array();
                $contador = 0;
                $aux;

                while(!feof($archivo)){             
                    $aux = explode(',',fgets($archivo));
                    if(count($aux)<12)
                    {
                        array_push($valores,$aux);
                    }
                }
                fclose($archivo);

                foreach($valores as $c)
                {
                    $cat = $c[1];
                    try{
                        if($cat == "pendiente"){
                            $conStr = "mysql:host=localhost;dbname=la_comanda";
                            $user ="christian";
                            $pass ="cp35371754";
                            $pdo = new PDO($conStr,$user,$pass);
                            $preparacion = $c[0];
                            $estado = $c[1];
                            $fecha = $c[2];
                            $hora = $c[3];
                            $mesa = $c[4];
                            $clave = $c[5];
                            $cantidad = $c[6];
                            $mozo = $c[7];
                            $sentencia = $pdo->prepare("INSERT INTO backup_comandas (id_Preparacion, estado, fecha, hora, numero_Mesa, clave, cantidad,
                                                                                     legajo_Mozo) 
                                                        VALUES (:id_Preparacion, :estado, :fecha, :hora, :numero_Mesa, :clave, :cantidad,
                                                               :legajo_Mozo)");
                            $sentencia->bindValue(':id_Preparacion',$preparacion);
                            $sentencia->bindValue(':estado',$estado);
                            $sentencia->bindValue(':fecha',$fecha);
                            $sentencia->bindValue(':hora',$hora);
                            $sentencia->bindValue(':numero_Mesa',$mesa);
                            $sentencia->bindValue(':clave',$clave);
                            $sentencia->bindValue(':cantidad',$cantidad);
                            $sentencia->bindValue(':legajo_Mozo',$mozo);
                            if($sentencia->execute()){
                                $contador++;
                                $pdo = null;
                            }
                        }
    
                        if($cat == "en preparacion"){
                            $conStr = "mysql:host=localhost;dbname=la_comanda";
                            $user ="christian";
                            $pass ="cp35371754";
                            $pdo = new PDO($conStr,$user,$pass);
                            $preparacion = $c[0];
                            $estado = $c[1];
                            $fecha = $c[2];
                            $hora = $c[3];
                            $mesa = $c[4];
                            $clave = $c[5];
                            $cantidad = $c[6];
                            $mozo = $c[7];
                            $cocinero = $c[8];
                            $demora = $c[9];
    
                            $sentencia = $pdo->prepare("INSERT INTO backup_comandas (id_Preparacion, estado, fecha, hora, numero_Mesa, clave, cantidad,
                                                                                     legajo_Mozo, legajo_Preparacion, demora) 
                                                        VALUES (:id_Preparacion, :estado, :fecha, :hora, :numero_Mesa, :clave, :cantidad,
                                                               :legajo_Mozo, :legajo_Preparacion, :demora)");
                            $sentencia->bindValue(':id_Preparacion',$preparacion);
                            $sentencia->bindValue(':estado',$estado);
                            $sentencia->bindValue(':fecha',$fecha);
                            $sentencia->bindValue(':hora',$hora);
                            $sentencia->bindValue(':numero_Mesa',$mesa);
                            $sentencia->bindValue(':clave',$clave);
                            $sentencia->bindValue(':cantidad',$cantidad);
                            $sentencia->bindValue(':legajo_Mozo',$mozo);
                            $sentencia->bindValue(':legajo_Preparacion',$cocinero);
                            $sentencia->bindValue(':demora',$demora);
                            if($sentencia->execute()){
                                $contador++;
                                $pdo = null;
                            }
                        }
    
                        if($cat == "listo para servir"){
                            $conStr = "mysql:host=localhost;dbname=la_comanda";
                            $user ="christian";
                            $pass ="cp35371754";
                            $pdo = new PDO($conStr,$user,$pass);
                            $preparacion = $c[0];
                            $estado = $c[1];
                            $fecha = $c[2];
                            $hora = $c[3];
                            $mesa = $c[4];
                            $clave = $c[5];
                            $cantidad = $c[6];
                            $mozo = $c[7];
                            $cocinero = $c[8];
                            $demora = $c[9];
                            $hora_Entrega = $c[10];
    
                            $sentencia = $pdo->prepare("INSERT INTO backup_comandas (id_Preparacion, estado, fecha, hora, numero_Mesa, clave, cantidad,
                                                                                     legajo_Mozo, legajo_Preparacion, demora, hora_Entrega) 
                                                        VALUES (:id_Preparacion, :estado, :fecha, :hora, :numero_Mesa, :clave, :cantidad,
                                                               :legajo_Mozo, :legajo_Preparacion, :demora, :hora_Entrega)");
                            $sentencia->bindValue(':id_Preparacion',$preparacion);
                            $sentencia->bindValue(':estado',$estado);
                            $sentencia->bindValue(':fecha',$fecha);
                            $sentencia->bindValue(':hora',$hora);
                            $sentencia->bindValue(':numero_Mesa',$mesa);
                            $sentencia->bindValue(':clave',$clave);
                            $sentencia->bindValue(':cantidad',$cantidad);
                            $sentencia->bindValue(':legajo_Mozo',$mozo);
                            $sentencia->bindValue(':legajo_Preparacion',$cocinero);
                            $sentencia->bindValue(':demora',$demora);
                            $sentencia->bindValue(':hora_Entrega',$hora_Entrega);
                            if($sentencia->execute()){
                                $contador++;
                                $pdo = null;
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

                if($contador>0 && Registros :: registro($modo["email"],$accion))
                {
                    $payload = json_encode(array("mensaje"=>"las comandas se subieron a la tabla exitosamente"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"error al subir las comandas a una tabla"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                

            }
            else{
                $payload = json_encode(array("mensaje"=>"no existe el archivo"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"error de autenticacion"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    
    public static function guardarEnPDF($request, $response, $args)
    {
        // Configuración de la base de datos
        $conStr = "mysql:host=localhost;dbname=la_comanda";
        $user ="christian";
        $pass ="cp35371754";
        $pdo = new PDO($conStr,$user,$pass);
    
        try {
            // Conexión a la base de datos
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare("SELECT * FROM evaluacion");
            $sentencia->execute();
            $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar si hay datos
            if (empty($resultado)) {
                $payload = json_encode(array("mensaje" => "No hay datos para generar el PDF."));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            
            // Crear una nueva instancia de TCPDF
            $pdf = new TCPDF();
            $pdf->AddPage();
        
            foreach ($resultado as $e) {
                // Convertir el puntaje a entero y construir el mensaje
                $mozo = (int)$e['puntaje_Mozo'];
                $restaurante = (int)$e['puntaje_Restaurante'];
                $cocinero = (int)$e['puntaje_Cocinero'];
                $mesa = (int)$e['puntaje_Mesa'];
                $htmlContent ="clave: ".$e["clave"]." mozo: " . $mozo. " cocineros: " . $cocinero . " mesa: " . $mesa . " restaurante: " . $restaurante;
                
                // Escribir HTML en el PDF
                $pdf->writeHTML($htmlContent);
            }
    
            $pdfPath = "C:/xampp/htdocs/la comanda/app/archives/comentarios.pdf";
            $pdf->Output('C:/xampp/htdocs/la comanda/app/archives/comentarios.pdf');
        
            $payload = json_encode(array("mensaje" => "PDF guardado exitosamente en: " . $pdfPath));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (PDOException $e) {
            // Manejo de errores de PDO
            $payload = json_encode(array("mensaje" => "Error en la base de datos: " . $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            // Manejo de otros errores
            $payload = json_encode(array("mensaje" => "Error al generar el PDF: " . $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

}

?>