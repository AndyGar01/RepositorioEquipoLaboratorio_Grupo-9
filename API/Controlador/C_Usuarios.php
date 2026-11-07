<?php

require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Modelo/M_Usuarios.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($connection, $response, $method, 1);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method){
    case 'GET':
        try {
            $user = new M_Usuarios($connection, $response);
            $user->SELECT_USUARIOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los usuarios", 2001, 400);
        }
        break;

    case 'POST':
        $id_perfil = $_POST['perfil'] ?? NULL;
        $carne_usuario = $_POST['carne'] ?? NULL;
        $nombre_usuario = $_POST['nombre'] ?? NULL;
        $apellido_usuario = $_POST['apellido'] ?? NULL;
        $correo_usuario = $_POST['correo'] ?? NULL;
        $contrasena_usuario = $_POST['contrasena'] ?? NULL; 
        $activo_usuario = $_POST['activo'] ?? NULL;
        try{
            $user = new M_Usuarios($connection, $response);
            $user->INSERT_USUARIO($_POST);
        } catch (\Throwable $th){
            $response->error("Error al agregar el usuario: ". $th->getMessage(), 2002, 401);
        }
        break;

    case 'PUT':
        try {
            $user = new M_Usuarios($connection, $response);
            $user->UPDATE_USUARIO($_PUT);
        } catch (\Throwable $th){
            $response->error("Error al actualizar el usuario", 2003, 402);
        }
        break;

    case 'DELETE':
        try {
            $user = new M_Usuarios($connection, $response);
            $user->DELETE_USUARIO($_DELETE['id_usu']);
        } catch (\Throwable $th){
            $response->error("Error al eliminar el usuario", 2004, 403);
        }
        break;
}
