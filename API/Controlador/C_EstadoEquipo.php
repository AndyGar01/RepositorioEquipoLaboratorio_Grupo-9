<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Modelo/M_EstadoEquipo.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($connection, $response, $method, 6);

parse_str(file_get_contents("php://input"), $datos);
$modelo = new M_EstadoEquipo($connection, $response);

try {
    switch ($method) {
        case 'GET':
            $modelo->SELECT_ESTADOS();
            break;
        case 'POST':
            $modelo->INSERT_ESTADO($_POST);
            break;
        case 'PUT':
            $modelo->UPDATE_ESTADO($datos);
            break;
        case 'DELETE':
            $modelo->DELETE_ESTADO($datos['id'] ?? 0);
            break;
        default:
            $response->error("Método no permitido.", 2007, 405);
    }
} catch (PDOException $e) {
    $response->error("No se pudo guardar o eliminar el estado. Verifica que el nombre no esté repetido ni tenga equipos asociados.", 3201, 400);
} catch (Throwable $e) {
    $response->error("No se pudo procesar el estado.", 3202, 400);
}
