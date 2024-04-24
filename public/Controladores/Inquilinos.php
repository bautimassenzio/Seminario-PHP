<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';
include_once 'Controladores/Conexion.php';

$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//3 A
$app->POST('/inquilinos/crear', function ($request, $response, $args) {
    $datos = $request->getParsedBody(); //Obtenemos los datos del PostMan
    
    // Verificamos que se hayan proporcionado todos los datos necesarios
    $camposRequeridos = ['nombre', 'apellido', 'documento', 'email', 'activo'];
    $errores=[];
    $tipos=['string', 'string', 'integer', 'string', 'string'];
    validarTipos($datos,$camposRequeridos, $tipos, $errores);
    if (!empty($errores)){
        $payload = json_encode(['error' => $errores, 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }

    $datos['activo'] = filter_var($datos['activo'], FILTER_VALIDATE_BOOLEAN); // Convertimos a boolean
    
    try {
        $connection = getConnection(); 
        $sql = "SELECT * FROM inquilinos WHERE documento = '" . $datos['documento'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { // Si el nombre ya existe
            $payload = json_encode(['error' => 'El documento no puede repetirse', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        } else {
            $sql = "INSERT INTO inquilinos (nombre, apellido, documento, email, activo) 
                    VALUES (:nombre, :apellido, :documento, :email, :activo)";
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':documento', $datos['documento']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':activo', $datos['activo'], PDO::PARAM_BOOL);
            $stmt->execute();
    
            $payload = json_encode([
                'status' => 'success',
                'code' => 201, 
                'data' => 'Operación exitosa'
            ]);
            $response->getBody()->write($payload);
            return $response;
        }
    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }   
});

//3 B
$app->PUT('/inquilinos/{id}/editar', function ($request, $response, $args){
    $datos = $request->getParsedBody();

    $camposRequeridos = ['nombre', 'apellido', 'documento', 'email', 'activo'];
    $errores=[];
    $tipos=['string', 'string', 'integer', 'string', 'string'];
    validarTipos($datos,$camposRequeridos, $tipos, $errores);
    if (!empty($errores)){
        $payload = json_encode(['error' => $errores, 'code' => 400]);
        $response->getBody()->write($payload);
        return $response;
    }

    $datos['activo'] = filter_var($datos['activo'], FILTER_VALIDATE_BOOLEAN); // Convertimos a boolean
    
    try{
        $connection = getConnection();

        $sql = "SELECT * FROM inquilinos WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() == 0) { 
            $payload = json_encode(['error' => 'Debe ingresar un ID que exista en la base de datos', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = "SELECT * FROM inquilinos WHERE documento = '" . $datos['documento'] . "' AND id != '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { 
            $payload = json_encode(['error' => 'El documento no puede repetirse', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = "UPDATE inquilinos SET apellido = :apellido, nombre = :nombre, documento = :documento, activo = :activo, email = :email WHERE id = :id";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':documento', $datos['documento']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':activo', $datos['activo'], PDO::PARAM_BOOL);
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

//3 C
$app->DELETE('/inquilinos/{id}/eliminar',function ($request, $response, $args){
    try{
        $connection = getConnection();

        $sql = "SELECT * FROM inquilinos WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() == 0) { 
            $payload = json_encode(['error' => 'Debe ingresar un ID que exista en la base de datos', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = "SELECT * FROM reservas WHERE inquilino_id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { 
            $payload = json_encode(['error' => 'El inquilino tiene reservas asociadas', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response;
        }

        $sql = 'DELETE FROM inquilinos WHERE id = :id';
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

//3 D
$app->GET('/inquilinos/listar', function (Request $request, Response $response){
    $connection = getConnection(); 
    try {
        $query = $connection->query('SELECT * FROM inquilinos');
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
            'data' => $e->getMessage()
        ]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
});

//3 E
$app->GET('/inquilinos/{id}/ver-inquilino', function (Request $request, Response $response, $args){
    $connection = getConnection(); 
    try {
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() > 0) { 
            $tabla = $consultaRepetido->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode(['status' => 'success', 'code' => 200, 'data' => $tabla]);
            $response->getBody()->write($payload);
            return $response;
        }
        
        $payload = json_encode([
            'status' => 'error',
            'code' => 400, 
            'data' => 'No se encontro el id en la tabla inquilinos'
        ]);
        $response->getBody()->write($payload);
        return $response;


    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'success',
            'code' => 400,
            'data' => $e->getMessage()
        ]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
});

//3 F
$app->GET('/inquilinos/{id}/reservas/historial', function (Request $request, Response $response, $args){
    $connection = getConnection(); 
    try {
        $sql = "SELECT * FROM inquilinos WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() == 0) { 
            $payload = json_encode(['status' => 'error', 'code' => 400, 'data' => 'Debe ingresar un id que existan en la tabla inquilinos']);
            $response->getBody()->write($payload);
            return $response;
        }
        
        $sql = "SELECT * FROM reservas WHERE inquilino_id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);
        $tabla = $consultaRepetido->fetchAll(PDO::FETCH_ASSOC);

        $payload = json_encode([
            'status' => 'success',
            'code' => 200, 
            'data' => $tabla
        ]);
        $response->getBody()->write($payload);
        return $response;


    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'success',
            'code' => 400,
            'data' => $e->getMessage()
        ]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
});