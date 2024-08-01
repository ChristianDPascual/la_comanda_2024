<?php
//ejecutar -- php -S localhost:666 -t app
date_default_timezone_set('America/Argentina/Buenos_Aires');//utilizar el sistema horario de buenos aires
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

//use Psr\Http\Server\RequestHandlerInterface as - El objeto controlador de solicitudes PSR15 (parámetro).
use Psr\Http\Message\ResponseInterface as Response; //respuesta
use Psr\Http\Message\ServerRequestInterface as Request; //(parámetro).
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;


require_once('C:/xampp/htdocs/la comanda/vendor/autoload.php');
require_once('C:\xampp\htdocs\la comanda\app\funciones\funciones.php');
require_once('C:\xampp\htdocs\la comanda\app\JWT\token.php');
require_once('C:\xampp\htdocs\la comanda\app\interface\interface.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Personal.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerPersonal.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Cliente.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerClientes.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Preparaciones.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerPreparaciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Comanda.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerComandas.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Mesa.php');
require_once('C:\xampp\htdocs\la comanda\app\controller\controllerMesas.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Evaluaciones.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Registros.php');
require_once('C:\xampp\htdocs\la comanda\app\class\Guardar.php');


// Instantiate Appc
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();


$app->group('/borrar', function (RouteCollectorProxy $group){
  $group->delete('/personal', \ControllerPersonal::class . ':eliminarUno');
  $group->delete('/cliente', \ControllerClientes::class . ':eliminarUno');
  $group->delete('/preparacion', \controllerPreparaciones::class . ':eliminarUno');
  $group->delete('/comanda', \controllerComandas::class . ':eliminarUno');
  $group->delete('/mesa', \controllerMesas::class . ':eliminarUno');
});


$app->group('/modificar', function (RouteCollectorProxy $group){
  $group->put('/personal', \controllerPersonal::class . ':modificarUno');
  $group->put('/estado-personal', \controllerPersonal::class . ':cambiarEstado');
  $group->put('/cliente', \controllerClientes::class . ':modificarUno');
  $group->put('/deuda-cliente', \controllerClientes::class . ':cambiarEstado');
  $group->put('/preparacion', \controllerPreparaciones::class . ':modificarUno');
  $group->put('/precio-preparacion', \controllerPreparaciones::class . ':cambiarEstado');
  $group->put('/comanda', \controllerComandas::class . ':modificarUno');
  $group->put('/estado-preparacion', \ControllerComandas::class . ':cambiarEstado');
  $group->put('/estado-mesa', \ControllerMesas::class . ':cambiarEstado');
  $group->put('/cancelar-comanda', \controllerComandas::class . ':cancelar');

});

$app->group('/listar', function (RouteCollectorProxy $group){
  $group->get('/personal', \ControllerPersonal::class . ':mostrarTodos');
  $group->get('/personalDNI', \ControllerPersonal::class . ':mostrarUno');
  $group->get('/clientes', \ControllerClientes::class . ':mostrarTodos');
  $group->get('/clienteDNI', \ControllerClientes::class . ':mostrarUno');
  $group->get('/preparaciones', \ControllerPreparaciones::class . ':mostrarTodos');
  $group->get('/preparacionID', \ControllerPreparaciones::class . ':mostrarUno');
  $group->get('/comandas', \ControllerComandas::class . ':mostrarTodos');
  $group->get('/comandasClave', \ControllerComandas::class . ':mostrarUno');
  $group->get('/mostrarPedidos', \ControllerComandas::class . ':mostrarPedidos');
  $group->get('/ver-mis-pedidos', \Cliente::class . ':verMisPedidos');
  $group->get('/mesas', \ControllerMesas::class . ':mostrarTodos');
  $group->get('/mesa', \ControllerMesas::class . ':mostrarUno');
  $group->get('/login', \Registros::class . ':registroLogin');
  $group->get('/acciones-por-todo-los-sectores', \Registros::class . ':registroSectores');
  $group->get('/acciones-por-todo-los-sectores-de-cada-empleado', \Registros::class . ':registroSectoresEmpleado');
  $group->get('/mas-vendidos', \Comanda::class . ':masVendido');
  $group->get('/comandas-demoradas', \Comanda::class . ':demorados');
  $group->get('/comandas-sin-demoras', \Comanda::class . ':sinDemorados');
  $group->get('/solo-demoras', \Comanda::class . ':verDemoras');
  $group->get('/comandas-canceladas', \Comanda::class . ':verCancelados');
  $group->get('/mesas-usos', \Mesa::class . ':masUsada');
  $group->get('/mesas-facturaciones', \Mesa::class . ':mejoresFacturaciones');
  $group->get('/mejor-factura', \Mesa::class . ':laFactura');
  $group->get('/peor-factura', \Mesa::class . ':laFactura');
  $group->get('/facturacion-entre-fechas', \Mesa::class . ':facturacionEntreFechas');
  $group->get('/mejores-comentarios', \Evaluaciones::class . ':listarEncuestas');
  $group->get('/peores-comentarios', \Evaluaciones::class . ':listarEncuestas');
  $group->get('/ver-clave-cliente', \Cliente::class . ':verClave');

});

$app->group('/mesa', function (RouteCollectorProxy $group){
  $group->post('/cobro', \Mesa::class . ':cobrar');
  $group->post('/cierre', \Mesa::class . ':cerrarMesas');
  $group->get('/listas-cierre', \Mesa::class . ':mostrarListasParaCerrar');
});

$app->group('/descargar', function (RouteCollectorProxy $group){
  $group->get('/logo', \ControllerPersonal::class . ':descargarLogo');
  $group->get('/comandas-csv', \Guardar::class . ':guardarComandasCSV');
  $group->get('/subir-comandas-csv', \Guardar::class . ':cargarComandasCSV');
  $group->get('/registros-pdf', \Guardar::class . ':guardarEnPDF');
});

$app->group('/realizar', function (RouteCollectorProxy $group){
  $group->post('/encuesta', \Evaluaciones::class . ':encuesta');
});

$app->group('/agregar', function (RouteCollectorProxy $group){
  $group->post('/foto-mesa', \controllerMesas::class . ':modificarUno');
});

$app->group('/alta', function (RouteCollectorProxy $group){
  $group->post('/cliente', \ControllerClientes::class . ':crearUno');
  $group->post('/personal', \ControllerPersonal::class . ':crearUno');
  $group->post('/preparacion', \ControllerPreparaciones::class . ':crearUno');
  $group->post('/comanda', \ControllerComandas::class . ':crearUno');
});

$app->group('/inicio', function (RouteCollectorProxy $group){
    $group->post('/login', \ControllerPersonal::class . ':login');
});
$app->run();
?>
