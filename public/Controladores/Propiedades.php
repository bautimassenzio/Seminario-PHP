<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

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
                    $sql = "INSERT INTO propiedades (id,domicilio,localidad_id,cantidad_habitaciones,cantidad_banios,cochera,cantidad_huespedes,fecha_inicio_disponibilidad,cantidad_dias,disponible,valor_noche,tipo_propiedad_id,imagen,tipo_imagen)  
                    VALUES (:id,:domicilio,:localidad_id,:cantidad_habitaciones,:cantidad_banios,:cochera,:cantidad_huespedes,:fecha_inicio_disponibilidad,:cantidad_dias,:disponible,:valor_noche,:tipo_propiedad_id,:imagen,:tipo_imagen)";

                    $consulta = $connection->prepare($sql);
                    
                    $consulta->bindParam(':id', $datos['id']);
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

$app->PUT('/propiedades/{id}', function ($request, $response, $args) {
    $datos = $request->getParsedBody();
    $camposRequeridos = ["domicilio","localidad_id","cantidad_huespedes","fecha_inicio_disponibilidad","cantidad_dias","disponible","valor_noche","tipo_propiedad_id"];// var campos requeiso, con los campos requeridos xd

    $errores='';
    foreach($camposRequeridos as $campo){// pregunto si existe d3entro de los datos del postman $campo es una variable que representa cada elemento del array en cada iteración del bucle.
        if (!isset($datos[$campo]) || empty($datos[$campo])) {//!isset($datos[$campo]): Esto verifica si el campo $campo no está presente en el array $datos. La función isset() devuelve true si la variable está definida y no es nula, y false de lo contrario. El ! al principio niega esta condición, por lo que esta negado
           //empty($datos[$campo]): Esto verifica si el valor del campo $campo en el array $datos está vacío. La función empty() devuelve true si la variable está definida y es vacía (por ejemplo, una cadena vacía '', un array vacío [], un número 0, o null), y false de lo contrario.se cumple si el campo $campo no está presente en el array $datos o si está presente pero su valor está vacío.
            $errores = $errores . $campo . ", ";    //agrega el campo que (arriva) a la var errores que contiene los campos que no (arriva)
        }
    }
    if ($errores!=''){// si errores no es vacio mando el error
        $payload = json_encode(['error' => "{$errores} es requerido", 'code' => 400]);//arma la respuesta
        $response->getBody()->write($payload);//toma el contenido contenido en la variable $payload y lo escribe en el cuerpo de la respuesta HTTP que se enviará al cliente
        return $response;
    }
    $date = DateTime::createFromFormat('Y-m-d', $datos['fecha_inicio_disponibilidad']);
    if ($date === false || $date->format('Y-m-d') != $datos['fecha_inicio_disponibilidad']) {
        $payload = json_encode(['error' => "El campo fecha_inicio_disponibilidad no es correcto", 'code' => 400]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    }

    try {
        $connection = getConnection();
        $consulta = $connection->prepare("SELECT * FROM propiedades WHERE id = :id");
        $consulta->bindParam(':id', $args['id']);
        $consulta->execute();

        if ($consulta->rowCount() == 0) {
            $payload = json_encode(['error' => 'El id a actualizar no existe en la tabla', 'code' => 400]);
            $response->getBody()->write($payload);
            return $response->withStatus(400);
        }

        $sql = "UPDATE propiedades SET domicilio=:domicilio, localidad_id=:localidad_id, cantidad_huespedes=:cantidad_huespedes, fecha_inicio_disponibilidad=:fecha_inicio_disponibilidad, cantidad_dias=:cantidad_dias, disponible=:disponible, valor_noche=:valor_noche, tipo_propiedad_id=:tipo_propiedad_id WHERE id = :id";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':domicilio', $datos['domicilio']);
        $stmt->bindParam(':localidad_id', $datos['localidad_id']);
        $stmt->bindParam(':cantidad_huespedes', $datos['cantidad_huespedes']);
        $stmt->bindParam(':fecha_inicio_disponibilidad', $datos['fecha_inicio_disponibilidad']);
        $stmt->bindParam(':cantidad_dias', $datos['cantidad_dias']);
        $stmt->bindParam(':disponible', $datos['disponible']);
        $stmt->bindParam(':valor_noche', $datos['valor_noche']);
        $stmt->bindParam(':tipo_propiedad_id', $datos['tipo_propiedad_id']);
        $stmt->bindParam(':id', $args["id"]);
        $stmt->execute();

        $payload = json_encode([
            'status' => 'success',
            'code' => 201,
            'data' => 'Operación exitosa'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(201);
    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(500);
    }
});

$app->delete('/propiedades/{id}', function ($request, $response, $args) {
    $id = (int) $args['id']; // Obtener el ID de la URL correctamente

    if (empty($id)) { // Verificar si el ID está presente y no es nulo
        $payload = json_encode(['error' => "El ID es requerido", 'code' => 400]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    }

    try {
        $connection = getConnection();

        $consulta = $connection->prepare("SELECT * FROM propiedades WHERE id = :id");
        $consulta->bindParam(':id', $id);
        $consulta->execute();

        if ($consulta->rowCount() == 0) {
            $payload = json_encode(['error' => "El ID de propiedad no existe", 'code' => 404]);
            $response->getBody()->write($payload);
            return $response->withStatus(404);
        }

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
    } catch (PDOException $e) {
        $payload = json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => $e->getMessage()
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(400);
    }
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