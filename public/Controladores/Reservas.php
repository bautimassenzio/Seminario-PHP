<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


//5 A
$app->POST('/reservas', function ($request, $response, $args){
    $datos = $request->getParsedBody();
    $camposRequeridos = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];
    $tipos = ['integer', 'integer', 'datetime', 'integer'];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $errores);

    if (!empty($errores)) {
        $payload = json_encode(['error' => $errores, 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }

 
    try{
        $connection = getConnection();
        $sql = "SELECT * FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
        $consulta = $connection->query($sql);

        if ($consulta->rowCount() == 0) { // Si no existe
            $payload = json_encode(['error' => 'id de propiedad no existe', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        } else {
            $sql = "SELECT disponible FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
            $consulta = $connection->query($sql);
            if ($consulta->fetchColumn() == 0){
                $payload = json_encode(['error' => 'propiedad no disponible', 'code' => 400]);
                $response->getBody()->write($payload);
                return $response;
            }
            $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
            $consulta = $connection->query($sql);
            $valortotal= $consulta->fetchColumn() * $datos['cantidad_noches'];
        }
        
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
        $consulta = $connection->query($sql);
        
        if ($consulta->rowCount() == 0) { 
            $payload = json_encode(['error' => 'id del inquilino no existe', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        } else {
            $sql = "SELECT activo FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
            $consulta = $connection->query($sql);
            if ($consulta->fetchColumn() == 0){
                $payload = json_encode(['error' => 'inquilino no activo', 'code' => 400]);
                $response->getBody()->write($payload);
                return $response;
            }
        }

        $sql = "INSERT INTO reservas (propiedad_id, inquilino_id, fecha_desde, cantidad_noches, valor_total) 
        VALUES (:propiedad_id, :inquilino_id, :fecha_desde, :cantidad_noches, :valor_total)";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':propiedad_id', $datos['propiedad_id']);
        $stmt->bindParam(':inquilino_id', $datos['inquilino_id']);
        $stmt->bindParam(':fecha_desde', $datos['fecha_desde']);
        $stmt->bindParam(':cantidad_noches', $datos['cantidad_noches']);
        $stmt->bindParam(':valor_total', $valortotal);
        $stmt->execute();

        $payload = json_encode([
            'status' => 'success',
            'code' => 201, 
        'data' => 'Operación exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;
    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }   
});

//5 B
$app->PUT('/reservas/{id}', function ($request, $response, $args){
    $datos = $request->getParsedBody();
    $camposRequeridos = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];
    $tipos = ['integer', 'integer', 'datetime', 'integer'];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $errores);

    if (!empty($errores)) {
        $payload = json_encode(['error' => $errores, 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }
    
    try{
        $connection=getConnection();

        $sql = "SELECT * FROM reservas WHERE id = '" . $args["id"] . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'Debe ingresar un id que exista en la tabla', 'code'=>400]));
            return $response;
        }
        
        $fechaActual = date("Y-m-d");
        $sql = "SELECT * FROM reservas WHERE id= :id AND fecha_desde > :fechaActual";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':id', $args['id']);
        $stmt->bindParam(':fechaActual', $fechaActual);
        $stmt->execute();
        if ($stmt->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'La reserva ya comenzo', 'code'=>400]));
            return $response;
        }

        $sql = "SELECT * FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
        $consulta = $connection->query($sql);

        if ($consulta->rowCount() == 0) { // Si no existe
            $payload = json_encode(['error' => 'id de propiedad no existe', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        } else {
            $sql = "SELECT disponible FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
            $consulta = $connection->query($sql);
            if ($consulta->fetchColumn() == 0){
                $payload = json_encode(['error' => 'propiedad no disponible', 'code' => 400]);
                $response->getBody()->write($payload);
                return $response;
            }
            $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
            $consulta = $connection->query($sql);
            $valortotal= $consulta->fetchColumn() * $datos['cantidad_noches'];
        }
        
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
        $consulta = $connection->query($sql);
        
        if ($consulta->rowCount() == 0) { 
            $payload = json_encode(['error' => 'id del inquilino no existe', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        } else {
            $sql = "SELECT activo FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
            $consulta = $connection->query($sql);
            if ($consulta->fetchColumn() == 0){
                $payload = json_encode(['error' => 'inquilino no activo', 'code' => 400]);
                $response->getBody()->write($payload);
                return $response;
            }
        }

        $sql = "UPDATE reservas SET propiedad_id = :propiedad_id, inquilino_id = :inquilino_id, fecha_desde = :fecha_desde, cantidad_noches = :cantidad_noches, valor_total = :valor_total WHERE id = :id";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':propiedad_id', $datos['propiedad_id']);
        $stmt->bindParam(':inquilino_id', $datos['inquilino_id']);
        $stmt->bindParam(':fecha_desde', $datos['fecha_desde']);
        $stmt->bindParam(':cantidad_noches', $datos['cantidad_noches']);
        $stmt->bindParam(':valor_total', $valortotal);
        $stmt->bindParam(':id', $args["id"]);
        $stmt->execute();

        $payload = json_encode([
            'status' => 'success',
            'code' => 201, 
        'data' => 'Operación exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;

    }catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }   
});

//5 C
$app->DELETE('/reservas/{id}',function ($request, $response, $args){ //duda
    try{
        $connection = getConnection();
        
        $sql = "SELECT * FROM reservas WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() == 0) { 
            $payload = json_encode(['error' => 'Debe ingresar un ID que exista en la base de datos', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $fechaActual = date("Y-m-d");
        $sql = "SELECT * FROM reservas WHERE id= :id AND fecha_desde > :fechaActual";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':id', $args ['id']);
        $stmt->bindParam(':fechaActual', $fechaActual);
        $stmt->execute();
        if ($stmt->rowCount()==0){
            $response->getBody()->write(json_encode (['error' => 'La reserva ya comenzo', 'code'=>400]));
            return $response;
        }

        $sql = 'DELETE FROM reservas  WHERE id = :id';
        $stmt= $connection->prepare($sql);
        $stmt->bindParam(':id', $args['id']);
        $stmt->execute();
        $payload = json_encode([
            'status' => 'success',
            'code' => 201, 
            'data' => 'Operación exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response;
    
    }catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }
});

//5 D
$app->GET('/reservas', function (Request $request, Response $response){
    $connection = getConnection(); 
    try {
        $query = $connection->query('SELECT * FROM reservas');
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