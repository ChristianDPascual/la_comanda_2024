<?php

interface InterfaceApiUsable
{
    public static function crearUno($request, $response, $args);
    public static function modificarUno($request, $response, $args);
    public static function mostrarTodos($request, $response, $args);
    public static function mostrarUno($request, $response, $args);
    public static function eliminarUno($request, $response, $args);
    public static function cambiarEstado($request, $response, $args);//cambio estado del empleado(activo-despedido),de las comandas,
                                                                     //el cliente(deudor), mesa(libre,ocupada), las tipo de cada una de las
                                                                     //preparacion(solamente el precio), sin borrarlo de la base de datos
   /*public static function cambiarEstado($request, $response, $args);*/
}

?>