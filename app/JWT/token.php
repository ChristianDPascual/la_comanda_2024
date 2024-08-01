<?php

use Firebase\JWT\JWT;
use Firebase\JWT\key;

class token{


    public static function crearToken($dni,$categoria){

        $ahora = time();
        $identificador = "COMANDA API 2023";
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + (60000)*24*90,
            'app' => $identificador,
            'DNI' => $dni,
            'categoria' => $categoria
        );

        return JWT::encode($payload,"SCHry169","HS256");
    }

    public static function obtenerCategoria($token)//obtiene la categoria que esta dentro del token
    {
        try
        {
            return JWT::decode($token,"SCHry169",['HS256'])->categoria;
        }
        catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error - Token invalido' => $e->getMessage())));
        }
    }

    public static function obtenerDNI($token)//obtiene el dni que esta dentro del token
    {
        try
        {
            return JWT::decode($token,"SCHry169",['HS256'])->DNI;
        }
        catch (Exception $e) {
            $response->getBody()->write(json_encode(array('error - Token invalido' => $e->getMessage())));
        }
    }

    public static function validarToken($request){//retorno si el token se encuentra activo
        $header = $request->getHeaderLine('Authorization');
        if(!empty($header)){
            try{
                // Obtener el token y limpiarlo
                $token = trim(str_replace("Bearer", "", $header));
    
                // Decodificar el token utilizando la clave secreta
                $decoded = JWT::decode($token, new Key("SCHry169", 'HS256'));
    
                // Obtener los datos del token
                $categoria = $decoded->categoria;
                $registro = $decoded->DNI;
    
                // Verificar el estado del personal
                $aux = Personal::traerDatosPersonal($registro);
    
                if(($categoria == "socio" || $categoria == "mozo" || $categoria == "cocina" || 
                $categoria == "choperas" || $categoria == "tragos" || $categoria == "candy") && $aux["estado"] == "activo"){
                    return $aux;
                }
                else{
                    throw new Exception("El token no es valido");
                }
            }
            catch(Exception $e){
                throw new Exception("Error al decodificar el token: " . $e->getMessage());
            }
        }
        else{
            throw new Exception("Token vacío");
        }
    }
    /*
    public static function validarToken($request)//devuelvo todos los datos del personal asociados a ese token
    {
        $header = $request->getHeaderLine('Authorization');
        if(!empty($header))
        {
            $token = trim(explode("Bearer", $header)[1]);
            $categoria = JWT::decode($token,"SCHry169",['HS256'])->categoria;//decodifico la categoria
            $registro = JWT::decode($token,"SCHry169",['HS256'])->DNI;//decodifico el dni

            $aux = Personal :: traerDatosPersonal($registro);//uso la funcion de traer el empleado para verificar que se encuentre trabajando actualmente

            if(($categoria == "socio" || $categoria == "mozo" || $categoria == "cocinero" || 
                $categoria == "bartender" || $categoria == "cervecero") && $aux["estado"] == "activo"){
                return $aux;
            }
            else{
                throw new Exception("el token no es valido");

            }
        }
        else
        {                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
            throw new Exception("Token vacío");
        }        
       
    }*/       

}