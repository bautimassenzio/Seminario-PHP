<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


//5 A
$app->POST('/reservas/crear', function ($request, $response, $args){
    $datos = $request->getParsedBody();
    $camposRequeridos = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];
    $tipos = ['integer', 'integer', 'datetime', 'integer'];
    $longitudes=[0,0,0,0];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $longitudes, $errores);

    if (empty($errores)){
        try{
            $connection = getConnection();
            $sql = "SELECT * FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
            $consulta = $connection->query($sql);
    
            if ($consulta->rowCount() == 0) { 
                array_push($errores, "El id " . $datos['propiedad_id'] . " no existe en la tabla propiedades");
            } else {
                $sql = "SELECT disponible FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
                $consulta = $connection->query($sql);
                if ($consulta->fetchColumn() == 0){
                    array_push($errores,"La propiedad " . $datos['propiedad_id'] . " no esta disponible");
                }else{
                    $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
                    $consulta = $connection->query($sql);
                    $valortotal= $consulta->fetchColumn() * $datos['cantidad_noches'];
                }
            }
            
            $sql = "SELECT * FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
            $consulta = $connection->query($sql);
            
            if ($consulta->rowCount() == 0) { 
                array_push($errores,"El inquilino " .$datos['inquilino_id'] . " no existe");
            } else {
                $sql = "SELECT activo FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
                $consulta = $connection->query($sql);
                if ($consulta->fetchColumn() == 0){
                    array_push($errores, "El inquilino " . $datos['inquilino_id'] . " no esta disponible");
                }
            }
            
            if (empty($errores)){
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
            }

        } catch (PDOException $e) {
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

//5 B
$app->PUT('/reservas/{id}/editar', function ($request, $response, $args){
    $datos = $request->getParsedBody();
    $camposRequeridos = ['propiedad_id', 'inquilino_id', 'fecha_desde', 'cantidad_noches'];
    $tipos = ['integer', 'integer', 'datetime', 'integer'];
    $longitudes=[0,0,0,0];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $longitudes, $errores);

    if (empty($errores)){
        try{
            $connection=getConnection();
    
            $sql = "SELECT * FROM reservas WHERE id = '" . $args["id"] . "'";
            $consulta_repetido = $connection->query($sql);
            if ($consulta_repetido->rowCount()==0){
                array_push($errores, "La reserva " . $args["id"] . " no existe");
            }else{
                $fechaActual = date("Y-m-d");
                $sql = "SELECT * FROM reservas WHERE id= :id AND fecha_desde > :fechaActual";
                $stmt = $connection->prepare($sql);
                $stmt->bindParam(':id', $args['id']);
                $stmt->bindParam(':fechaActual', $fechaActual);
                $stmt->execute();
                if ($stmt->rowCount()==0){
                    array_push($errores, "La reserva " . $args['id'] . " ya comenzo");
                }

                if (empty($errores)){
                    $sql = "SELECT * FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
                    $consulta = $connection->query($sql);
            
                    if ($consulta->rowCount() == 0) { 
                        array_push($errores, "La propiedad " . $datos['propiedad_id'] . " no existe");
                    } else {
                        $sql = "SELECT disponible FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
                        $consulta = $connection->query($sql);
                        if ($consulta->fetchColumn() == 0){
                            array_push($errores,"La propiedad " . $datos['propiedad_id'] . " no esta disponible");
                        }else{
                            $sql = "SELECT valor_noche FROM propiedades WHERE id = '" . $datos['propiedad_id'] . "'";
                            $consulta = $connection->query($sql);
                            $valortotal= $consulta->fetchColumn() * $datos['cantidad_noches'];
                        }
                    }
                    
                    $sql = "SELECT * FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
                    $consulta = $connection->query($sql);
                    
                    if ($consulta->rowCount() == 0) { 
                        array_push($errores, "El inquilino " . $datos['inquilino_id'] . " no existe");
                    } else {
                        $sql = "SELECT activo FROM inquilinos WHERE id = '" . $datos['inquilino_id'] . "'";
                        $consulta = $connection->query($sql);
                        if ($consulta->fetchColumn() == 0){
                            array_push($errores, "El inquilono " . $datos['inquilino_id'] . " no esta activo");
                        }
                    }
                    if (empty($errores)){
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
                        'data' => 'Operacion exitosa'
                        ]);
                        $response->getBody()->write($payload);
                        return $response;
                
                    }
                }
                }

        }catch (PDOException $e) {
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

//5 C
$app->DELETE('/reservas/{id}/eliminar',function ($request, $response, $args){ 
    $errores=[];
    try{
        $connection = getConnection();
        
        $sql = "SELECT * FROM reservas WHERE id = '" . $args['id'] . "'";
        $consultaRepetido = $connection->query($sql);

        if ($consultaRepetido->rowCount() == 0) { 
            array_push($errores, "El id " . $args['id'] . " no existe en la tabla reservas");
        }else{
            $fechaActual = date("Y-m-d");
            $sql = "SELECT * FROM reservas WHERE id= :id AND fecha_desde > :fechaActual";
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':id', $args ['id']);
            $stmt->bindParam(':fechaActual', $fechaActual);
            $stmt->execute();
            if ($stmt->rowCount()==0){
                array_push($errores, "La reserva " . $args['id'] . " ya comenzo");
            } else{
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
            }
    
        }

    }catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]); 
        $response->getBody()->write($payload);
        return $response;      
    }

    $payload = json_encode(['error' => $errores, 'code' => 400]);
    $response->getBody()->write($payload);
    return $response; 

});

//5 D
$app->GET('/reservas/listar', function (Request $request, Response $response){
    $connection = getConnection(); 
    try {
        $query = $connection->query('SELECT r.*, i.apellido AS apellido_inquilino, i.nombre AS nombre_inquilino, l.nombre AS localidad, t.nombre AS tipo_de_propiedad FROM reservas r 
        INNER JOIN inquilinos i ON r.inquilino_id = i.id 
        INNER JOIN propiedades p 
        INNER JOIN localidades l ON p.localidad_id = l.id 
        INNER JOIN tipo_propiedades t ON p.tipo_propiedad_id = t.id');
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