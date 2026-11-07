<?php

require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Modelo/M_Permisos.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($connection, $response, $method, 3);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method){
    case 'GET':
        try {
            $user = new M_Permisos($connection, $response);
            $user->SELECT_PERMISOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los permisos", 2001, 400);
        }
        break;

    case 'POST':
        $nombre_permiso = $_POST['nombre'] ?? NULL;
        $descripcion_permiso = $_POST['descripcion'] ?? NULL;
        try{
            $user = new M_Permisos($connection, $response);
            $user->INSERT_PERMISO($_POST);
        } catch (\Throwable $th){
            $response->error("Error al ingresar el permiso: ". $th->getMessage(), 2002, 400);
        }
        break;

    case 'PUT':
        try {
            $user = new M_Permisos($connection, $response);
            $user->UPDATE_PERMISO($_PUT);
        } catch (\Throwable $th){
            $response->error("Error al actualizar el permiso", 2003, 400);
        }
        break;

    case 'DELETE':
        try {
            $user = new M_Permisos($connection, $response);
            $user->DELETE_PERMISO($_DELETE['id']);
        } catch (\Throwable $th){
            $response->error("Error al eliminar el permiso", 2004, 400);
        }
        break;
}
