<?php

require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Modelo/M_PerfilModulo.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method){
    case 'GET':
        try {
            $user = new M_PerfilModulo($connection, $response);
            $user->SELECT_PERFIL_MODULO();
        } catch (\Throwable $th){
            $response->error("Error al obtener los permisos", 2001, 400);
        }
        break;
    case 'POST':
        try {
            $permiso = new M_PerfilModulo($connection, $response);
            $permiso->INSERT_PERFIL_MODULO($_POST);
        } catch (\Throwable $th){
            $response->error("Error al insertar los permisos", 2001, 400);
        }
        break;
    case 'PUT':
        try {
            $user = new M_PerfilModulo($connection, $response);
            $user->UPDATE_PERFIL_MODULO($_PUT);
        } catch (\Throwable $th){
            $response->error("Error al actualizar los permisos", 2001, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_PerfilModulo($connection, $response);
            $user->DELETE_PERFIL_MODULO($_DELETE['id_per'],$_DELETE['id_mod']);
        } catch (\Throwable $th){
            $response->error("Error al eliminar los permisos", 2001, 400);
        }
        break;
}