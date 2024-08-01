<?php

class Preparaciones
{
    public $nombre;
    public $tipo;
    public $precio;
    public $id_Preparacion;
    public $categoria;

    public static function traerDatosPreparacion($id_Preparacion){
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM preparaciones WHERE id_Preparacion= :id_Preparacion');
            $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
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


    public static function preparacionRepetida($id_Preparacion){
        $retorno = TRUE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT id_Preparacion FROM preparaciones');

            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                $pdo =null;
                foreach($resultado as $p){
                    if($p["id_Preparacion"] == $id_Preparacion){
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

    public static function id_Preparacion(){//general un nuevo id de preparacion
        $legajo = FALSE;
        do{
            $legajo = generarLegajos();
            $lista = Preparaciones :: traerPreparaciones();
        }while(compararLegajos($legajo,$lista) == FALSE);
        return $legajo;
    }

    public static function traerPreparaciones(){
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT id_Preparacion FROM preparaciones');
            $pdo =null;
            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);          
                return $resultado;
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }

    }

    public static function traerCategoriaPreparacion($id_Preparacion){
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT * FROM preparaciones WHERE id_Preparacion= :id_Preparacion');
            $sentencia->bindValue(':id_Preparacion', $id_Preparacion);
            if($sentencia->execute()){
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado["sector"];
    
            }
        }
        catch(PDOException $e){
            $pdo = null;
            throw new Exception("Error al conectarse a la base de datos");
        }
        return FALSE;
    }

    public static function preparacionExistente($nombre){
        $retorno = TRUE;
        try{
            $conStr = "mysql:host=localhost;dbname=la_comanda";
            $user ="christian";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare('SELECT nombre FROM preparaciones');
    
    
            if($sentencia->execute()){
                $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                $pdo =null;
                foreach($resultado as $p){
                    if($p["nombre"] === $nombre){
                        echo "preparacion ya existente\n";
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

    public static function nombrePreparacion($id_Preparacion){
        $preparacion = Preparaciones :: traerDatosPreparacion($id_Preparacion);
        return $preparacion["nombre"];
    }
}
?>