<?php

require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Modelo/M_Modulos.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method){
    case 'GET':
        try {
            $user = new M_Modulos($connection, $response);
            $user->SELECT_MODULOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los módulos", 2001, 400);
        }
        break;

    case 'POST':
        $nombre_modulo = $_POST['nombre'] ?? NULL;
        $descripcion_modulo = $_POST['descripcion'] ?? NULL;
        try{
            $user = new M_Modulos($connection, $response);
            $user->INSERT_MODULOS($_POST);
        } catch (\Throwable $th){
            $response->error("Error al ingresar el módulo: ". $th->getMessage(), 2002, 400);
        }
        break;

    case 'PUT':
        try {
            $user = new M_Modulos($connection, $response);
            $user->UPDATE_MODULOS($_PUT);
        } catch (\Throwable $th){
            $response->error("Error al actualizar el módulo", 2003, 400);
        }
        break;

    case 'DELETE':
        try {
            $user = new M_Modulos($connection, $response);
            $user->DELETE_MODULOS($_DELETE['id']);
        } catch (\Throwable $th){
            $response->error("Error al eliminar el módulo", 2004, 400);
        }
        break;
}