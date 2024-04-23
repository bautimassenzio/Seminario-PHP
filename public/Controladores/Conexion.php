<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

function getConnection(){
    $dbhost = "localhost";
    $dbname = "inmobiliaria";  
    $dbuser = "root";         
    $dbpass = "";
    
    $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $connection ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $connection;
}

try {
    $conn = getConnection();
    echo "Conexión exitosa";

} catch (PDOException $e) { 
    echo "Error de conexión: " . $e->getMessage();
} 
$app->get('/', function (Request $request, Response $response, $args) { 
    
        $response->getBody(); 
        return $response;      
    });
?>