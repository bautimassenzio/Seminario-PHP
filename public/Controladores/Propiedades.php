<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

include_once 'Controladores/Conexion.php';
$app->addBodyParsingMiddleware(); 
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app -> post('/propiedades', function (Request $request, Response $response){

    try{

        $connection = getConnection();
        $params = $request->getParsedBody();
        $requiredKeys = ["domicilio","localidad_id","cantidad_huespedes","fecha_inicio_disponibilidad","cantidad_dias","disponible","valor_noche","tipo_propiedad_id"];
        
        $missingKeys = [];
        
        
        foreach($requiredKeys as $key){
        if(!array_key_exists($key, $params)){
            $missingKeys[] = $key;
        } else {
            $value = $params[$key];
            if(empty($value)){
                $missingKeys[] = $key; 
            }
        }
    }
 
    
    if(empty($missingKeys)){

        $stmt = $connection->prepare("SELECT * FROM localidades WHERE id = :localidad_id");
        $stmt->bindParam(':localidad_id',$params['localidad_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $stmt = $connection->prepare("SELECT * FROM tipo_propiedades WHERE id = :tipo_propiedad_id");
            $stmt->bindParam(':tipo_propiedad_id',$params['tipo_propiedad_id']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
            
                $stmt = $connection->prepare("INSERT INTO propiedades(domicilio,localidad_id,cantidad_habitaciones,cantidad_banios,cochera,cantidad_huespedes,fecha_inicio_disponibilidad,cantidad_dias,disponible,valor_noche,tipo_propiedad_id,imagen,tipo_imagen)
                                            VALUES (:domicilio, :localidad_id, :cantidad_habitaciones, :cantidad_banios, :cochera, :cantidad_huespedes, :fecha_inicio_disponibilidad, :cantidad_dias, :disponible, :valor_noche, :tipo_propiedad_id, :imagen, :tipo_imagen)");

                $stmt->bindParam(':domicilio',$params['domicilio']);
                $stmt->bindParam(':localidad_id',$params['localidad_id']);
                $stmt->bindParam(':cantidad_habitaciones',$params['cantidad_habitaciones']);
                $stmt->bindParam(':cantidad_banios',$params['cantidad_banios']);
                $stmt->bindParam(':cochera',$params['cochera']);
                $stmt->bindParam(':cantidad_huespedes',$params['cantidad_huespedes']);
                $stmt->bindParam(':fecha_inicio_disponibilidad',$params['fecha_inicio_disponibilidad']);
                $stmt->bindParam(':cantidad_dias',$params['cantidad_dias']);
                $stmt->bindParam(':disponible',$params['disponible']);
                $stmt->bindParam(':valor_noche',$params['valor_noche']);
                $stmt->bindParam(':tipo_propiedad_id',$params['tipo_propiedad_id']);
                $stmt->bindParam(':imagen',$params['imagen']);
                $stmt->bindParam(':tipo_imagen',$params['tipo_imagen']);
                $stmt->execute();

                $payload = json_encode([
                    'message' => 'La propiedad se inserto en la base de datos correctamente.',
                    'status' => 'success',
                    'code' => 201,
                    'data' => $params
                ]);
            
            } else {
                $payload = json_encode([
                    'message' => 'El tipo de propiedad no existe.',
                    'status' => 'Error',
                    'code' => 400,
                ]);
            }
        } else {
            $payload = json_encode([
                'message' => 'La localidad no existe.',
                'status' => 'Error',
                'code' => 400,
            ]);
        }
                
    } else {
        $payload = json_encode([
            'message' => 'Falta completar los siguientes campos',
            'status' => 'Error',
            'code' => 400,
            'data' => $missingKeys
        ]);
    } 
    

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type','application/json');

    } catch (PDOException $e){
        $json = json_encode([
            'status' => 'error',
            'code' => 400,
        ]);

        $response->getBody()->write($json);
        return $response-> withHeader('Content-Type','application/json');

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