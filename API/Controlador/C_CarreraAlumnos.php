<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_CarreraAlumnos.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, 5);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method) {
    case 'GET':
        try {
            $user = new M_CarreraAlumnos($conection, $response);
            $user->SELECT_CARRERA_ALUMNOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $nombre_carrera_alumno = $_POST['nombre'] ?? null;
        $descripcion_carrera_alumno = $_POST['descripcion'] ?? null;

        try {
            $user = new M_CarreraAlumnos($conection, $response);
            $user->INSERT_CARRERA_ALUMNO($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar la carrera del alumno", 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_CarreraAlumnos($conection, $response);
            $user->UPDATE_CARRERA_ALUMNO($_PUT);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar la carrera del alumno", 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_CarreraAlumnos($conection, $response);
            $user->DELETE_CARRERA_ALUMNO($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar la carrera del alumno", 2004, 400);
        }
        break;
}
