<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
$app = AppFactory::create();

$app->setBasePath('/Proyecto/public');
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

function validarTipos ($datos, $campos, $tipos, $longitudes, &$errores){
    foreach($campos as $indice => $campo){
        if (!isset($datos[$campo]) || empty($datos[$campo]) || trim($datos[$campo])===''){
            array_push($errores,"El campo " . $campo . " es requerido");
        } else{
            $tipo = $tipos[$indice];
            $longitud = $longitudes[$indice];
            switch($tipo){
                case 'string':
                    if (is_numeric($datos[$campo])){
                        array_push($errores,"El campo " . $campo . " debe ser del tipo " . $tipo);
                    } elseif (strlen($datos[$campo])>$longitud){
                        array_push($errores, "El campo " . $campo . " excede la longitud maxima de " . $longitud);
                    }
                    break;
                case 'integer':
                    if (!is_numeric($datos[$campo])){
                        array_push($errores,"El campo " . $campo . " debe ser del tipo " . $tipo);
                    }
                    break;
                case 'datetime':
                    $date = DateTime::createFromFormat('Y-m-d', $datos[$campo]);
                    if ($date === false || $date->format('Y-m-d') != $datos['fecha_desde']){
                        array_push($errores, "El campo " . $campo . " debe ser del tipo " . $tipo);
                    }
                    break;
                default:
                array_push($errores,"Tipo de dato no valido para el campo ". $campo);
            }
        }
    }
}

//controladores
include_once 'Controladores/Conexion.php';
include_once 'Controladores/Localidades.php';
include_once 'Controladores/Inquilinos.php';
include_once 'Controladores/tipoPropiedades.php';
include_once 'Controladores/Propiedades.php';
include_once 'Controladores/Reservas.php';



$app->run();
