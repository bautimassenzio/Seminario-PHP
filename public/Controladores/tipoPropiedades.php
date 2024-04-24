<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';
include_once 'Controladores/Conexion.php';

$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


//2 A
$app->POST('/tipos_propiedad/crear', function ($request, $response, $args) {
    $datos= $request->getParsedBody(); 
    if(!isset($datos["nombre"]) || empty($datos["nombre"])){ 
        $payload = json_encode(['error' => 'El campo es requerido', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    } else { 
        $nombre=$datos["nombre"]; 
        $nombre = preg_replace('/\s+/', ' ', $nombre); 
        $nombre=trim($nombre);
        if ($nombre === '') {
            $payload = json_encode(['error' => 'El campo esta vacio', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }
    }   
    try{    
        $connection = getConnection(); 
        $sql = "SELECT * FROM tipo_propiedades WHERE nombre = '" . $nombre . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()>0){ 
            $response->getBody()->write(json_encode (['error' => 'El campo nombre no puede repetirse', 'code'=>400]));
            return $response;
        } else{
            $sql = ('INSERT INTO tipo_propiedades (nombre) VALUES (:nombre)');
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
        }
    } catch (PDOException $e){
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }   
});

//2 B
$app->PUT('/tipos_propiedad/{id}/editar', function ($request, $response, $args){
    $datos= $request->getParsedBody(); //Obtengo los datos del cuerpo de la solicitud (Array asociativo)
    
    if (!isset($datos["nombre"]) | empty($datos["nombre"]) | !isset($args["id"]) | empty($args["id"])){
        $payload = json_encode(['error' => 'El campo nombre e id deben ser indicados', 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }else{
        //Corrigo el nombre si es necesario
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
    }

    try{
        $connection=getConnection();
        //Evaluo si el id existe
        $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'Debe ingresar un id que exista en la tabla', 'code'=>400]));
            return $response;
        }

        //Evaluo si el nombre existe
        $sql = "SELECT * FROM tipo_propiedades WHERE nombre = '" . $nombre . "' AND id != '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()>0){ //pregunto si se repite (nombre debe ser unico) 
            $response->getBody()->write(json_encode (['error' => 'El campo nombre no puede repetirse', 'code'=>400]));
            return $response;
        }

        $sql = ('UPDATE tipo_propiedades SET nombre = :nombre WHERE id=:id'); //Aca preparo la consulta sql
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

//2 C
$app->DELETE('/tipos_propiedad/{id}/eliminar',function ($request, $response, $args){
    $id= $args['id'];
    try{
        $connection= getConnection();
        //Evaluo si id existe
        $sql = "SELECT * FROM tipo_propiedades WHERE id = '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'Debe ingresar un id que exista en la tabla', 'code'=>400]));
        return $response;
        }
        
        $sql = "SELECT * FROM propiedades WHERE tipo_propiedad_id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { 
            $payload = json_encode(['error' => 'El tipo de propiedad tiene propiedades asociadas', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = 'DELETE FROM tipo_propiedades WHERE id = :id';
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

//2 D
$app->GET('/tipos_propiedad/listar', function (Request $request, Response $response){
    $connection = getConnection(); 
    try {
        $query = $connection->query('SELECT nombre FROM tipo_propiedades');
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
