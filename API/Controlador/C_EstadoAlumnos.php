<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_EstadoAlumnos.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, 4);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method) {
    case 'GET':
        try {
            $user = new M_EstadoAlumnos($conection, $response);
            $user->SELECT_ESTADO_ALUMNOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $nombre_estado_alumno = $_POST['nombre'] ?? null;

        try {
            $user = new M_EstadoAlumnos($conection, $response);
            $user->INSERT_ESTADO_ALUMNO($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el estado del alumno", 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_EstadoAlumnos($conection, $response);
            $user->UPDATE_ESTADO_ALUMNO($_PUT);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar el estado del alumno", 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_EstadoAlumnos($conection, $response);
            $user->DELETE_ESTADO_ALUMNO($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el estado del alumno", 2004, 400);
        }
        break;
}
