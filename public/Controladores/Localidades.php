<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//1 A
$app->POST('/localidades/crear', function ($request, $response, $args) {
    $datos= $request->getParsedBody();
    $errores=[];
    $campos=['nombre'];
    $tipos=['string'];
    $longitudes=[50];

    validarTipos($datos,$campos, $tipos, $longitudes, $errores);

   if (empty($errores)){
    try{
        $connection = getConnection(); 
        $nombre = $datos["nombre"];
        $sql = "SELECT * FROM localidades WHERE nombre = '" . $nombre . "'";
        $consulta_repetido = $connection->query($sql);
        
        if ($consulta_repetido->rowCount()>0){ 
            array_push($errores,"El nombre " . $nombre . " ya existe en la tabla");
        }else{
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
        }

    } catch (PDOException $e){
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }
   }

   $payload = json_encode(['error' => $errores, 'code' => 400]);
   $response->getBody()->write($payload);
   return $response; 
  
});

//1 B
$app->PUT('/localidades/{id}/editar', function ($request, $response, $args){
    $datos= $request->getParsedBody();
    $errores=[];
    $campos=['nombre'];
    $tipos=['string'];
    $longitudes=[50];

    validarTipos($datos,$campos, $tipos, $longitudes, $errores);

    if (empty($errores)){
        try{
            $connection=getConnection();
            $nombre = $datos["nombre"];
            $id = $args['id'];
            $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
            $consulta_repetido = $connection->query($sql);
            if ($consulta_repetido->rowCount()==0){
                array_push($errores, "El id " . $id  . " no existe en la tabla localidades");
            }else{
                $sql = "SELECT * FROM localidades WHERE nombre = '" . $nombre . "' AND id != '" . $id . "'";
                $consulta_repetido = $connection->query($sql);
                if ($consulta_repetido->rowCount()>0){ 
                    array_push($errores,"El nombre no puede repetirse");
                }else {
                    $sql = ('UPDATE localidades SET nombre = :nombre WHERE id=:id'); 
                    $stmt = $connection->prepare($sql); 
                    $stmt->bindParam(':nombre',$nombre); 
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
            
                    $payload = json_encode([ 
                        'status' => 'success',
                        'code' => 200,
                        'data' => 'Opeacion exitosa'
                    ]);
                    $response->getBody()->write($payload);
                    return $response;
                }
            }

        }catch (PDOException $e){ 
            $payload = json_encode([
                'status' => 'error',
                'code' => 400,
                'mensaje' => $e->getMessage()
            ]); 
            $response->getBody()->write($payload);
            return $response;
        }
    }

    $payload = json_encode(['error' => $errores, 'code' => 400]);
    $response->getBody()->write($payload);
    return $response; 

});


//1 C
$app->DELETE('/localidades/{id}/eliminar',function ($request, $response, $args){
    $id= $args['id'];
    $errores=[];
    try{
        $connection= getConnection();

        $sql = "SELECT * FROM localidades WHERE id = '" . $id . "'";
        $consulta_repetido = $connection->query($sql);
        if ($consulta_repetido->rowCount()==0){
            array_push($errores,"El id " . $id . " no existe en la tabla");
        }else{
            $sql = "SELECT * FROM propiedades WHERE localidad_id = '" . $args['id'] . "'";
            $consultaRepetido = $connection->query($sql);
    
            if ($consultaRepetido->rowCount() > 0) { 
                array_push($errores, "La localidad " . $id . " tiene propiedades asociadas");
            }else{
                $sql = 'DELETE FROM localidades WHERE id = :id';
                $stmt= $connection->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
        
                $payload = json_encode([ 
                    'status' => 'success',
                    'code' => 200,
                    'data' => 'Opeacion exitosa'
                ]);
                $response->getBody()->write($payload);
                return $response;
            }
        }
        
    }catch (PDOException $e){ 
        $payload = json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;
    }
    $payload = json_encode(['error' => $errores, 'code' => 400]);
    $response->getBody()->write($payload);
    return $response; 
});

//1 D
$app->GET('/localidades/listar', function(request $request, response $response){
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