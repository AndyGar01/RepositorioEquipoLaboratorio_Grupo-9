<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Perfiles.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, 2);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method) {
    case 'GET':
        try {
            $user = new M_Perfiles($conection, $response);
            $user->SELECT_PERFILES();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $nombre_perfil = $_POST['nombre'] ?? null;
        $descripcion_perfil = $_POST['descripcion'] ?? null;

        try {
            $user = new M_Perfiles($conection, $response);
            $user->INSERT_PERFIL($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el perfil", 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_Perfiles($conection, $response);
            $user->UPDATE_PERFIL($_PUT);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar el perfil", 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_Perfiles($conection, $response);
            $user->DELETE_PERFIL($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el perfil", 2004, 400);
        }
        break;
}
