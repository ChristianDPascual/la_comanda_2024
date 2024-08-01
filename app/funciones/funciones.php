<?php

require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Personal.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Preparaciones.php');

function generarClaveAlfanumerica() {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyz';
    $mezclar = str_shuffle($caracteres);
    return substr($mezclar, 0, 5);
}

function compararClavesAlfa($clave, $lista){// Retornar true si la clave no está en la lista
    return !in_array($clave, $lista);
}

function generarLegajos() {
    $caracteres = '0123456789';
    $mezclar = str_shuffle($caracteres);
    return substr($mezclar, 0, 5);
}

function compararLegajos($clave, $lista){// Retornar true si la clave no está en la lista
    return !in_array($clave, $lista);
}

function validarNumero($valor){//valida numeros
    if(isset($valor) && is_numeric($valor)){
        return true;
    }
    else{
        echo "no se ingreso un numero valido\n";
        return false;
    }
}

function validarCadena($valor){//valida cadenas de texto
    if(isset($valor) && is_string($valor)){
        return true;
    }
    else{
        echo "el nombre o apellido no es valido\n";
        return false;
    }
}

function validarDNI($valor){//valida numeros de dni
    if(isset($valor) && is_string($valor)){
        $contador = 0;
        $aux = 0;
        $caracteres = str_split($valor);
        
        for($i = 0 ; $i<count($caracteres) ; $i++){
            if(validarNumero($caracteres[$i]) == true){
                $contador++;
            }
            else{
                return false;
            }
        }
        
        if($contador>=7 && $contador<9){
            return true;
        }
        else{
            return false;
        }
    }
    else{
        echo "no se ingreso un dni valido\n";
        return false;
    }
}

function validarEmail($valor){//valida mail si contiene el caracter arroba y el caracter punto
  return (false !== strpos($valor, "@") && false !== strpos($valor, "."));
}


function validarPrecio($valor){//valida precios
    if(isset($valor) && (is_float($valor) || is_numeric($valor)) && $valor>0){
        return true;
    }
    else{
        echo "no se ingreso un precio valido\n";
        return false;
    }
}

function validarArchivos($valor){//valida que sea un archivo
    if ($valor['name'] != null && isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK){
        return TRUE;
    }
    echo "no se ingreso una foto\n";
    return FALSE;
}



function validarFecha($valor){//valida fechas
        if(isset($valor)){
            
            $fecha=date_create_from_format("d-m-Y",$valor);
            $fechaFinal =date_parse_from_format("Y/m/d",date_format($fecha,"Y/m/d"));

            if(checkdate($fechaFinal["month"],$fechaFinal["day"],$fechaFinal["year"])){
                return TRUE;
            }
            else{
                echo "ingrese una fecha valida";
                return FALSE;
            }
        }
        else{
            echo "ingrese una fecha\n";
            return FALSE;
        }
}

function validarEstadoPreparacion($valor){//valida sector de las preparaciones
    if(isset($valor)){
        $categorias = ["en preparacion", "pendiente", "listo para servir"];  
        if(in_array($valor, $categorias)){
            return TRUE;
        }
        else{
            echo "ingrese un estado validao\n";
            return FALSE;
        }
    }
    else{
        echo "No se ingreso un estado\n";
        return FALSE;
    }
}

function validarEstadoMesa($valor){//valida estados de la mesa
    if(isset($valor)){
        $categorias = ["esperando pedido", "comiendo", "pagando", "cerrada"];  
        if(in_array($valor, $categorias)){
            return TRUE;
        }
        else{
            echo "ingrese un estado validao\n";
            return FALSE;
        }
    }
    else{
        echo "No se ingreso un estado\n";
        return FALSE;
    }
}

function validarCategoria($valor){//valida categorias
    if(isset($valor)){
        $categorias = ["mozo", "choperas", "candy", "cocina", "tragos", "socio"];  
        if(in_array($valor, $categorias)){
            return TRUE;
        }
        else{
            echo "ingrese una categoria valida\n";
            return FALSE;
        }
    }
    else{
        echo "No se ingreso una categoria\n";
        return FALSE;
    }
}

function validarSector($valor){//valida sector de las preparaciones
    if(isset($valor)){
        $categorias = ["candy", "cocina", "tragos", "choperas"];  
        if(in_array($valor, $categorias)){
            return TRUE;
        }
        else{
            echo "ingrese una categoria valida\n";
            return FALSE;
        }
    }
    else{
        echo "No se ingreso una categoria\n";
        return FALSE;
    }
}

function validarSiNo($valor){//valida sector de las preparaciones
    if(isset($valor)){
        $categorias = ["si", "no"];  
        if(in_array($valor, $categorias)){
            return TRUE;
        }
        else{
            echo "ingrese si es una preparacion para mayor de 18 años\n";
            return FALSE;
        }
    }
    else{
        echo "No se ingreso si es una preparacion para mayor de 18 años\n";
        return FALSE;
    }
}

function validarPuntaje($numero){
    return is_numeric($numero) && $numero >= 1 && $numero <= 10;
}

function validarEncuensta($clave,$dni,$puntaje_Cocinero,$puntaje_Mesa,$puntaje_Mozo,$puntaje_Restaurante,$comentarios,$numero_Mesa){
    $retorno = TRUE;
    
    if(!validarPuntaje($puntaje_Cocinero)){
        $retorno = FALSE;
        echo "el puntaje para la cocina no es valido \n";
    }

    if(!validarPuntaje($puntaje_Mesa)){
        $retorno = FALSE;
        echo "el puntaje para la mesa no es valido \n";
    }

    if(!validarPuntaje($puntaje_Mozo)){
        $retorno = FALSE;
        echo "el puntaje para el mozo no es valido \n";
    }

    if(!validarPuntaje($puntaje_Restaurante)){
        $retorno = FALSE;
        echo "el puntaje para el restaurante no es valido \n";
    }

    if(Mesa :: disponibilidad_Encuesta($clave,$numero_Mesa,$dni) == FALSE){
        $retorno = FALSE;
        echo "el dni no esta asociado a esa clave o numero de mesa\n";
    }

    if(strlen($comentarios) > 66){
        $retorno = FALSE;
        echo "los comentarios no pueden exceder los 66 caracteres\n";
    }
    
    return $retorno;
}

function verificarDatosCliente($parametros){
    $retorno = TRUE;

    if(validarCadena($parametros["nombre"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarCadena($parametros["apellido"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarDNI($parametros["dni"]) == FALSE || Cliente :: clienteRepetido($parametros["dni"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarEmail($parametros["email"]) == FALSE ){
        $retorno = FALSE;
    }

    if(validarFecha($parametros["fecha_Nacimiento"]) == FALSE){
        $retorno = FALSE;
    }
    return $retorno;
}


function verificarDatosPersonal($parametros){
    $retorno = TRUE;

    if(validarCadena($parametros["nombre"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarCadena($parametros["apellido"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarDNI($parametros["dni"]) == FALSE || Personal :: personalRepetido($parametros["dni"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarEmail($parametros["email"]) == FALSE ){
        $retorno = FALSE;
    }

    if(validarCategoria($parametros["categoria"]) == FALSE){
        $retorno = FALSE;
    }
    return $retorno;
}

function verificarDatosPreparacion($parametros){
    $retorno = TRUE;

    if(validarCadena($parametros["nombre"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarPrecio($parametros["precio"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarSiNo($parametros["mayor18"]) == FALSE){
        $retorno = FALSE;
    }

    if(validarSector($parametros["sector"]) == FALSE){
        $retorno = FALSE;
    }
    return $retorno;
}

function calcularDemora($demora,$hora){
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora_inicial = DateTime::createFromFormat('H:i:s', $hora);// Hora donde el cocinero toma el pedido
    $intervalo = new DateInterval('PT' . $demora . 'M');// los minutos de la demora
    $hora_inicial->add($intervalo);// Sumar la demora
    $hora_resultante = $hora_inicial->format("H:i:s"); //la hora que debería estar el pedido
    $obtenerHoraSistema = new DateTime();
    $hora_sistema_actual = $obtenerHoraSistema;// Hora actual del sistema
    $pedido_listo = DateTime::createFromFormat("H:i:s", $hora_resultante);// Convertir la hora resultante a DateTime para calcular la diferencia

    if ($hora_sistema_actual < $pedido_listo) { // verificar si la hora actual del sistema es menor a la hora que deberia estar el pedido
        $minutos_restantes = $hora_sistema_actual->diff($pedido_listo);
        return "Minutos restantes para el pedido: ".($minutos_restantes->h * 60 + $minutos_restantes->i);
    } else {//caso contrario se calcula el tiempo de atraso
        $minutos_restantes = $pedido_listo->diff($hora_sistema_actual);
        $total_minutos = ($minutos_restantes->days * 24 * 60) + ($minutos_restantes->h * 60) + $minutos_restantes->i;
        return "El pedido está demorado: ".$total_minutos ." minutos";
    }
}

function tiemposSinDemora($demora,$hora,$hora_Entrega){
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora_inicial = DateTime::createFromFormat('H:i:s', $hora);// Hora donde el cocinero toma el pedido
    $intervalo = new DateInterval('PT' . $demora . 'M');// los minutos de la demora
    $hora_inicial->add($intervalo);// Sumar la demora
    $hora_resultante = $hora_inicial->format("H:i:s"); //la hora que debería estar el pedido
    $obtenerHoraSistema = DateTime::createFromFormat('H:i:s', $hora_Entrega);
    $hora_sistema_actual = $obtenerHoraSistema;// Hora actual del sistema
    $pedido_listo = DateTime::createFromFormat("H:i:s", $hora_resultante);// Convertir la hora resultante a DateTime para calcular la diferencia
    $minutos_restantes = $pedido_listo->diff($hora_sistema_actual);
    $total_minutos = ($minutos_restantes->days * 24 * 60) + ($minutos_restantes->h * 60) + $minutos_restantes->i;
    if ($hora_sistema_actual < $pedido_listo) { // verificar si la hora actual del sistema es menor a la hora que deberia estar el pedido
        $minutos_restantes = $hora_sistema_actual->diff($pedido_listo);
        echo "El pedido fue entregado correctamente a los ".($minutos_restantes->h * 60 + $minutos_restantes->i)." minutos";
        return TRUE;
    } else {
        return FALSE;
    }
}

function tiemposDemora($demora,$hora,$hora_Entrega){
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $hora_inicial = DateTime::createFromFormat('H:i:s', $hora);// Hora donde el cocinero toma el pedido
    $intervalo = new DateInterval('PT' . $demora . 'M');// los minutos de la demora
    $hora_inicial->add($intervalo);// Sumar la demora
    $hora_resultante = $hora_inicial->format("H:i:s"); //la hora que debería estar el pedido
    $obtenerHoraSistema = DateTime::createFromFormat('H:i:s', $hora_Entrega);
    $hora_sistema_actual = $obtenerHoraSistema;// Hora actual del sistema
    $pedido_listo = DateTime::createFromFormat("H:i:s", $hora_resultante);// Convertir la hora resultante a DateTime para calcular la diferencia
    $minutos_restantes = $pedido_listo->diff($hora_sistema_actual);
    $total_minutos = ($minutos_restantes->days * 24 * 60) + ($minutos_restantes->h * 60) + $minutos_restantes->i;
    if ($hora_sistema_actual > $pedido_listo) { // verificar si la hora actual del sistema es menor a la hora que deberia estar el pedido
        $minutos_restantes = $pedido_listo->diff($hora_sistema_actual);
        $total_minutos = ($minutos_restantes->days * 24 * 60) + ($minutos_restantes->h * 60) + $minutos_restantes->i;
        echo "El pedido se retraso: ".$total_minutos ." minutos";
        return TRUE;
    } else {
        return FALSE;
    }
}

function depurarArrayPersonal($datos){
    $retorno = FALSE;
    if(!empty($datos)){
        $retorno = TRUE;
        $resultados_unicos = [];// 1 Array para almacenar los resultados únicos
        foreach ($datos as $registro) {// 2 Recorrer el array original
            $email = $registro['email'];
            $categoria = $registro['categoria'];
            if (!isset($resultados_unicos[$email])) {//asegurarse de que solo se agreguen valores únicos al array 
                $resultados_unicos[$email] = $categoria;
            }
        }

        $resultados_unicos = array_map(function($categoria, $email){// Convertir el array asociativo a un array de array
            return ['email' => $email, 'categoria' => $categoria];
        },$resultados_unicos, array_keys($resultados_unicos));

        return $resultados_unicos;
    }
    return $retorno;
}

function preparacionMasVendida($datos){
    
    if(!empty($datos)){
        $acumulado = [];
        foreach ($datos as $p){
            $id_Preparacion = $p['id_Preparacion'];
            $cantidad = $p['cantidad'];

            if(!isset($acumulado[$id_Preparacion])){ // Sumar las cantidades para cada nombre
                $acumulado[$id_Preparacion] = 0;
            }
            $acumulado[$id_Preparacion] += $cantidad;
        }

        $total_acumulado = [];// Convertir el array acumulado en un formato plano para ordenar
        foreach($acumulado as $id_Preparacion => $cantidad){
            $total_acumulado[] = ['id_Preparacion' => $id_Preparacion, 'cantidad' => $cantidad];
        }

        usort($total_acumulado, function($a, $b){// Ordenar el array por cantidad en orden descendente
            return $b['cantidad'] <=> $a['cantidad'];
        });

        foreach ($total_acumulado as $preparacion) {
            echo "id prepracion: ".$preparacion['id_Preparacion']." ".Preparaciones ::nombrePreparacion($preparacion['id_Preparacion']).
            ", Cantidad: ".$preparacion['cantidad']."\n";
        }
        return TRUE;
    }
    else{
        echo "no hay Preparaciones pedidas por los clientes";
        return false;
    }
}

function mesaMasUsada($datos){
    
    if(!empty($datos)){
        $acumulado = [];
        foreach ($datos as $m){
            $numero_Mesa = $m['numero_Mesa'];

            if(!isset($acumulado[$numero_Mesa])){ // Sumar las cantidades para cada nombre
                $acumulado[$id_Preparacion] = 0;
            }
            $acumulado[$id_Preparacion] += $cantidad;
        }

        $total_acumulado = [];// Convertir el array acumulado en un formato plano para ordenar
        foreach($acumulado as $id_Preparacion => $cantidad){
            $total_acumulado[] = ['id_Preparacion' => $id_Preparacion, 'cantidad' => $cantidad];
        }

        usort($total_acumulado, function($a, $b){// Ordenar el array por cantidad en orden descendente
            return $b['cantidad'] <=> $a['cantidad'];
        });

        foreach ($total_acumulado as $preparacion) {
            echo "id prepracion: ".$preparacion['id_Preparacion']." ".Preparaciones ::nombrePreparacion($preparacion['id_Preparacion']).
            ", Cantidad: ".$preparacion['cantidad']."\n";
        }
        return TRUE;
    }
    else{
        echo "no hay Preparaciones pedidas por los clientes";
        return false;
    }
}

?>