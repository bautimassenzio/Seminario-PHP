<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//4 A
$app->POST('/propiedades/crear', function ($request, $response, $args){
    $datos = $request->getParsedBody();
    $camposRequeridos = ["domicilio","localidad_id","cantidad_huespedes","fecha_inicio_disponibilidad","cantidad_dias","disponible","valor_noche","tipo_propiedad_id"];
    $tipos = ['string', 'integer', 'integer', 'datetime', 'integer', 'boolean', 'integer', 'integer'];
    $longitudes=[0,0,0,0,0,0,0,0];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $longitudes, $errores);

    $camposNoRequeridos=['cantidad_habitaciones', 'cantidad_banios', 'cochera', 'imagen', 'tipo_imagen'];
    $tipos = ['integer', 'integer', 'boolean', 'string', 'string'];
    $longitudes = [0,0,0,0,0];

    validarNoRequeridos($datos,$camposNoRequeridos,$tipos,$longitudes,$errores);

    if (empty($errores)){
        try{
            $connection = getConnection();
            $consulta = $connection->prepare("SELECT * FROM localidades WHERE id = :localidad_id");     
            $consulta->bindParam(':localidad_id',$datos['localidad_id']);
            $consulta->execute();
    
            if ($consulta->rowCount() == 0) {
                array_push($errores, "El id de la localidad " . $datos['localidad_id'] . " no existe");
            }else{
                $consulta = $connection->prepare("SELECT * FROM tipo_propiedades WHERE id = :tipo_propiedad_id");
                $consulta->bindParam(':tipo_propiedad_id',$datos['tipo_propiedad_id']);
                $consulta->execute();
        
                if ($consulta->rowCount() == 0) {
                    array_push($errores,"El id de tipo propiedades " . $datos['tipo_propiedad_id'] . " no existe");
                }else{
                    $sql = "INSERT INTO propiedades (domicilio,localidad_id,cantidad_habitaciones,cantidad_banios,cochera,cantidad_huespedes,fecha_inicio_disponibilidad,cantidad_dias,disponible,valor_noche,tipo_propiedad_id,imagen,tipo_imagen)  
                    VALUES (:domicilio,:localidad_id,:cantidad_habitaciones,:cantidad_banios,:cochera,:cantidad_huespedes,:fecha_inicio_disponibilidad,:cantidad_dias,:disponible,:valor_noche,:tipo_propiedad_id,:imagen,:tipo_imagen)";

                    $consulta = $connection->prepare($sql);
                    
                    $consulta->bindParam(':domicilio', $datos['domicilio']);
                    $consulta->bindParam(':localidad_id', $datos['localidad_id']);
                    $consulta->bindParam(':cantidad_habitaciones', $datos['cantidad_habitaciones']);
                    $consulta->bindParam(':cantidad_banios', $datos['cantidad_banios']);
                    $consulta->bindParam(':cochera', $datos['cochera']);
                    $consulta->bindParam(':cantidad_huespedes', $datos['cantidad_huespedes']);
                    $consulta->bindParam(':fecha_inicio_disponibilidad', $datos['fecha_inicio_disponibilidad']);
                    $consulta->bindParam(':cantidad_dias', $datos['cantidad_dias']);
                    $consulta->bindParam(':disponible', $datos['disponible']);
                    $consulta->bindParam(':valor_noche', $datos['valor_noche']);
                    $consulta->bindParam(':tipo_propiedad_id', $datos['tipo_propiedad_id']);
                    $consulta->bindParam(':imagen', $datos['imagen']);
                    $consulta->bindParam(':tipo_imagen', $datos['tipo_imagen']);
                    
                    $consulta->execute();
          
                   $payload = json_encode([
                      'status' => 'success',
                      'code' => 201, 
                  'data' => 'Operación exitosa'
                  ]);
                  $response->getBody()->write($payload);
                  return $response;
                }

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

//4 B
$app->PUT('/propiedades/{id}/editar', function ($request, $response, $args) {
    $datos = $request->getParsedBody();
    $camposRequeridos = ["domicilio","localidad_id","cantidad_huespedes","fecha_inicio_disponibilidad","cantidad_dias","disponible","valor_noche","tipo_propiedad_id"];
    $tipos = ['string', 'integer', 'integer', 'datetime', 'integer', 'boolean', 'integer', 'integer'];
    $longitudes=[0,0,0,0,0,0,0,0];
    $errores=[];
    
    validarTipos($datos,$camposRequeridos, $tipos, $longitudes, $errores);

    $camposNoRequeridos=['cantidad_habitaciones', 'cantidad_banios', 'cochera', 'imagen', 'tipo_imagen'];
    $tipos = ['integer', 'integer', 'boolean', 'string', 'string'];
    $longitudes = [0,0,0,0,0];

    validarNoRequeridos($datos,$camposNoRequeridos,$tipos,$longitudes,$errores);

    if (empty($errores)){
        try{
            $connection = getConnection();
            $consulta = $connection->prepare("SELECT * FROM propiedades WHERE id = :id");     
            $consulta->bindParam(':id',$args['id']);
            $consulta->execute();

            if ($consulta->rowCount() == 0) {
                array_push($errores, "El id " . $args['id'] . " no existe en la tabla propiedades");
            }else{
                $consulta = $connection->prepare("SELECT * FROM localidades WHERE id = :localidad_id");     
                $consulta->bindParam(':localidad_id',$datos['localidad_id']);
                $consulta->execute();
        
                if ($consulta->rowCount() == 0) {
                    array_push($errores, "El id de la localidad " . $datos['localidad_id'] . " no existe");
                }else{
                    $consulta = $connection->prepare("SELECT * FROM tipo_propiedades WHERE id = :tipo_propiedad_id");
                    $consulta->bindParam(':tipo_propiedad_id',$datos['tipo_propiedad_id']);
                    $consulta->execute();
            
                    if ($consulta->rowCount() == 0) {
                        array_push($errores,"El id de tipo propiedades " . $datos['tipo_propiedad_id'] . " no existe");
                    }else{
                        $sql = "UPDATE propiedades 
                        SET domicilio = :domicilio, localidad_id = :localidad_id, cantidad_habitaciones = :cantidad_habitaciones, cantidad_banios = :cantidad_banios, cochera = :cochera, cantidad_huespedes = :cantidad_huespedes, fecha_inicio_disponibilidad = :fecha_inicio_disponibilidad, cantidad_dias = :cantidad_dias, disponible = :disponible, valor_noche = :valor_noche, tipo_propiedad_id = :tipo_propiedad_id, imagen = :imagen, tipo_imagen = :tipo_imagen
                        WHERE id = :id";
    
                        $consulta = $connection->prepare($sql);
                        
                        $consulta->bindParam(':domicilio', $datos['domicilio']);
                        $consulta->bindParam(':localidad_id', $datos['localidad_id']);
                        $consulta->bindParam(':cantidad_habitaciones', $datos['cantidad_habitaciones']);
                        $consulta->bindParam(':cantidad_banios', $datos['cantidad_banios']);
                        $consulta->bindParam(':cochera', $datos['cochera']);
                        $consulta->bindParam(':cantidad_huespedes', $datos['cantidad_huespedes']);
                        $consulta->bindParam(':fecha_inicio_disponibilidad', $datos['fecha_inicio_disponibilidad']);
                        $consulta->bindParam(':cantidad_dias', $datos['cantidad_dias']);
                        $consulta->bindParam(':disponible', $datos['disponible']);
                        $consulta->bindParam(':valor_noche', $datos['valor_noche']);
                        $consulta->bindParam(':tipo_propiedad_id', $datos['tipo_propiedad_id']);
                        $consulta->bindParam(':imagen', $datos['imagen']);
                        $consulta->bindParam(':tipo_imagen', $datos['tipo_imagen']);
                        $consulta->bindParam(':id', $args['id']);
                        
                        $consulta->execute();
              
                       $payload = json_encode([
                          'status' => 'success',
                          'code' => 201, 
                      'data' => 'Operación exitosa'
                      ]);
                      $response->getBody()->write($payload);
                      return $response;
                    }
                }
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

//4 C
$app->DELETE('/propiedades/{id}/eliminar', function ($request, $response, $args) {
    $id = $args['id']; 
    $errores = [];
    try {
        $connection = getConnection();

        $consulta = $connection->prepare("SELECT * FROM propiedades WHERE id = :id");
        $consulta->bindParam(':id', $id);
        $consulta->execute();

        if ($consulta->rowCount() == 0) {
            array_push ($errores,"El id " . $id . " no existe en la tabla propiedades");
        }else{

            $consulta = $connection->prepare("SELECT * FROM reservas WHERE propiedad_id = :id");
            $consulta->bindParam(':id', $id);
            $consulta->execute();

            if ($consulta->rowCount() > 0){
                array_push($errores, "La propiedad " . $id . " tiene reservas asociadas");
            }else{

                $sql = 'DELETE FROM propiedades WHERE id = :id';
                $stmt = $connection->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
        
                $payload = json_encode([
                    'status' => 'success',
                    'code' => 200,
                    'data' => 'Operacion exitosa'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(200);
            }
        }

    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    }

    $payload = json_encode(['error' => $errores, 'code' => 400]);
    $response->getBody()->write($payload);
    return $response;
});

$app->GET('/propiedades', function (Request $request, Response $response){
    $connection = getConnection(); 
    try {
        $query = $connection->query('SELECT * FROM propiedades');
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