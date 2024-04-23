<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//1 A
$app->POST('/localidades', function ($request, $response, $args) {
    $datos= $request->getParsedBody(); //Obtenemos los datos del PostMan
    if(!isset($datos["nombre"]) || empty($datos["nombre"])){ //si no existe nombre en el arreglo asociativo O el dato es nulo
        $payload = json_encode(['error' => 'El campo es requerido', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }

    $nombre=$datos["nombre"]; 
    $nombre = preg_replace('/\s+/', ' ', $nombre); 
    $nombre=trim($nombre); 

    if ($nombre === '') {//Evaluo si no es una cadena vacia
        $payload = json_encode(['error' => 'El campo esta vacio', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }
   
    try{
        $connection = getConnection(); 
        
        $sql = "SELECT * FROM localidades WHERE nombre = '" . $nombre . "'";
        $consulta_repetido = $connection->query($sql);
        
        if ($consulta_repetido->rowCount()>0){ //pregunto si se repite (nombre debe ser unico) 
            $response->getBody()->write(json_encode (['error' => 'El campo nombre no puede repetirse', 'code'=>400]));
            return $response;
        }
        $sql = ('INSERT INTO localidades (nombre) VALUES (:nombre)');
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
    
        $payload = json_encode([
            'status' => 'success',
            'code' => 201, 
            'data' => 'Opeacion exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;

    } catch (PDOException $e){
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }   
});

//1 B
$app->PUT('/localidades/{id}', function ($request, $response, $args){
    $datos= $request->getParsedBody(); //Obtengo los datos del cuerpo de la solicitud (Array asociativo)
    
    if (!isset($datos["nombre"]) | empty($datos["nombre"]) | !isset($args["id"]) | empty($args["id"])){
        $payload = json_encode(['error' => 'El campo nombre e id deben ser indicados', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }
    $id = $args['id']; 
    $nombre = $datos["nombre"]; 
    $nombre = preg_replace('/\s+/', ' ', $nombre); 
    $nombre=trim($nombre); 
    //Evaluo si es una cadena vacia
    if ($nombre === '') {
        $payload = json_encode(['error' => 'El campo nombre esta vacio', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    } 

    try{
        $connection=getConnection();

        //Evaluo si el id existe
        $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'Debe ingresar un id que exista en la tabla', 'code'=>400]));
            return $response;
        }

        //Evaluo si el nombre existe
        $sql = "SELECT * FROM localidades WHERE nombre = '" . $nombre . "' AND id != '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()>0){ //pregunto si se repite (nombre debe ser unico) 
            $response->getBody()->write(json_encode (['error' => 'El campo nombre no puede repetirse', 'code'=>400]));
            return $response;
        }

        $sql = ('UPDATE localidades SET nombre = :nombre WHERE id=:id'); //Aca preparo la consulta sql
        $stmt = $connection->prepare($sql); 
        $stmt->bindParam(':nombre',$nombre); //Asigno parametros para la consulta
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $payload = json_encode([ //Genero json donde indica el resultado de mi operacion
            'status' => 'success',
            'code' => 200,
            'data' => 'Opeacion exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;

    }catch (PDOException $e){ //En caso de error, informo el error de $e
        $payload = json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;
    }
});


//1 C
$app->DELETE('/localidades/{id}',function ($request, $response, $args){
    $id= $args['id'];
    try{
        $connection= getConnection();
        //Evaluo si id existe
        $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'Debe ingresar un id que exista en la tabla', 'code'=>400]));
        return $response;
        }
        
        $sql = "SELECT * FROM propiedades WHERE localidad_id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { 
            $payload = json_encode(['error' => 'La localidad tiene propiedades asociadas', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = 'DELETE FROM localidades WHERE id = :id';
        $stmt= $connection->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $payload = json_encode([ //Genero json donde indica el resultado de mi operacion
            'status' => 'success',
            'code' => 200,
            'data' => 'Opeacion exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;
    }catch (PDOException $e){ //En caso de error, informo el error de $e
        $payload = json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;
    }
});

//1 D
$app->GET('/localidades', function(request $request, response $response){
    $connection = getConnection(); //Obtiene la conexion a la base de datos
    try {
        $query = $connection->query('SELECT nombre FROM localidades');
        $tipos = $query->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode([
            'status' => 'success',
            'code' => 200,
            'data' => $tipos
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'success',
            'code' => 400,
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');        
    }
});
?>