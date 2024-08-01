<?php
require_once('C:\xampp\htdocs\la comanda\app\class\Persona.php');

class Personal extends Persona
{
    /**public $nombre;
    public $apellido;
    public $dni;
    public $mail;
    public $categoria; */
    public $legajo;
    public $fecha_Ingreso;
    public $fecha_Baja;//si es 1/1/1900 significa que sigue activo
    public $estado;//activo o inactivo


    public static function obtenerDatosLogin($email){//con el email obtengo todos los datos de la persona
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("SELECT * FROM personal WHERE email = :email");
            $sentencia->bindValue(':email', $email);
    
    
            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;

                if($resultado["estado"]=="activo"){
                    return $resultado;
                }
                else{
                    echo "usuario despedido";
                }
                
            }
        }
        catch(PDOException $e){
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerDatosPersonal($dni){//traigo todos los datos del personal en forma de array solo con el dni
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM personal WHERE dni = :dni');
            $sentencia->bindValue(':dni', $dni);
            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;

                if (isset($resultado) && is_array($resultado)){
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

    public static function personalRepetido($dni){//valida al momento de crear una nuevo personal ya existe ese dni
        $retorno = TRUE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT dni FROM personal');
    
    
            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                $pdo =null;
                foreach($resultado as $d){
                    if($d["dni"] === $dni){
                        echo "dni ya existente\n";
                        $retorno = FALSE;
                        break;
                    }
                }

                return $retorno;
            }
            else{
                $pdo =null;
                $retorno = FALSE;
                return $retorno ;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }
    }

    public static function legajos(){//genera un nuevo numero de legajo
        $legajo = FALSE;
        do{
            $legajo = generarLegajos();
            $lista = Personal :: traerLegajos();
        }while(compararLegajos($legajo,$lista) == FALSE);
        return $legajo;
    }

    public static function traerLegajos(){//traigo todos los legajos
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT legajo FROM personal');
    
            if($sentencia->execute()){
                $pdo =null;
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);                
                return $resultado;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }

    }

    public static function traerUnLegajo($email){//con el email traigo el numero de legajo del personal 
        $retorno = FALSE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM personal WHERE email = :email');
            $sentencia->bindValue(':email', $email);
    
            if($sentencia->execute()){
                $pdo =null;
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);  
                $retorno = $resultado["legajo"];             
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }
        return $retorno;
    }

    public static function nombrePersonal($legajo){//con el numero de legajo traigo el nombre del personal
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM personal WHERE legajo = :legajo');
            $sentencia->bindValue(':legajo', $legajo);
            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;

                if (isset($resultado) && is_array($resultado)){
                    return $resultado["nombre"]." ".$resultado["apellido"];
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
        return null;
    }

    
    public static function descargarLogo($request, $response, $args){
        $url = 'https://drive.google.com/uc?export=download&id=1ZbyerEMoYyaNdDuhvVtXdMy_xn3yDFBx';
        $modo = token :: validarToken($request);
        $accion = $request->getUri()->getPath();
        $validacion = FALSE;

        if($modo["categoria"] == "socio" && $modo["estado"] == TRUE){
            $validacion = TRUE;
            $desktopPath = 'C:/Users/chris/OneDrive/Escritorio/logo.pdf';
            $descarga = file_get_contents($url);
            if($descarga == FALSE){
                $validacion = FALSE;
                echo "Error al descargar la imagen";
            }

            $ubicacion = file_put_contents($desktopPath, $descarga);
            if($ubicacion == FALSE){
                $validacion = FALSE;
                echo "Error al guardar la imagen";
            }

            if($validacion == TRUE){
                $payload = json_encode(array("mensaje"=>"imagen descargada en escritorio exitosamente"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else{
                $payload = json_encode(array("mensaje"=>"fallo la accion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else{
            $payload = json_encode(array("mensaje"=>"usuario invalido"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        
    }

    /*
    public static function descargarLogo($request, $response, $args) {
        // Iniciar el buffer de salida para evitar salidas no deseadas
        ob_start();
    
        $url = 'https://drive.google.com/file/d/1ZbyerEMoYyaNdDuhvVtXdMy_xn3yDFBx/view?usp=sharing';
        $modo = token::validarToken($request);
        $validacion = FALSE;
    
        if ($modo["categoria"] == "socio" && $modo["estado"] == TRUE) {
            $validacion = TRUE;
            $desktopPath = 'C:/Users/chris/OneDrive/Escritorio/logo.pdf'; // Ruta en el escritorio
    
            // Descargar la imagen desde la URL
            $descarga = file_get_contents($url);
            if ($descarga === FALSE) {
                $validacion = FALSE;
                $payload = json_encode(array("mensaje" => "Error al descargar la imagen"));
                $response->getBody()->write($payload);
                ob_end_clean(); // Limpiar el buffer de salida
                return $response->withHeader('Content-Type', 'application/json');
            }
    
            // Crear un nuevo archivo PDF con TCPDF
            $pdf = new TCPDF();
    
            // Añadir una página al PDF
            $pdf->AddPage();
    
            // Guardar la imagen en un archivo temporal
            $tempImagePath = 'C:/Users/chris/OneDrive/Escritorio/temp_image.jpg';
            file_put_contents($tempImagePath, $descarga);
    
            // Establecer la imagen en el PDF
            $pdf->Image($tempImagePath, 10, 10, 190); // Ajusta la posición y tamaño según sea necesario
    
            // Guardar el archivo PDF
            $pdfOutput = $pdf->Output('S'); // Obtener el contenido del PDF como cadena
            $fileSaved = file_put_contents($desktopPath, $pdfOutput); // Guardar el PDF en el disco
    
            // Eliminar el archivo temporal
            unlink($tempImagePath);
    
            if ($fileSaved === FALSE) {
                $validacion = FALSE;
                $payload = json_encode(array("mensaje" => "Error al guardar el archivo PDF"));
                $response->getBody()->write($payload);
                ob_end_clean(); // Limpiar el buffer de salida
                return $response->withHeader('Content-Type', 'application/json');
            }
    
            if ($validacion === TRUE) {
                $payload = json_encode(array("mensaje" => "Imagen descargada y guardada en escritorio exitosamente"));
                $response->getBody()->write($payload);
                ob_end_clean(); // Limpiar el buffer de salida
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Fallo la acción"));
                $response->getBody()->write($payload);
                ob_end_clean(); // Limpiar el buffer de salida
                return $response->withHeader('Content-Type', 'application/json');
            }
        } else {
            $payload = json_encode(array("mensaje" => "Usuario inválido"));
            $response->getBody()->write($payload);
            ob_end_clean(); // Limpiar el buffer de salida
            return $response->withHeader('Content-Type', 'application/json');
        }
    }*/
}
?>