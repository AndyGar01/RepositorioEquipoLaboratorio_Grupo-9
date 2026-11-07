<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Empleados.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, null, true);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method) {
    case 'GET':
        try {
            $user = new M_Empleados($conection, $response);
            $user->SELECT_EMPLE();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $nombre_empleado = $_POST['nombre'] ?? null;
        $apellido_empleado = $_POST['apellido'] ?? null;
        $telefono_empleado = $_POST['telefono'] ?? null;
        $correo_empleado = $_POST['correo'] ?? null;
        $estado_empleado = $_POST['estado'] ?? null;

        try {
            $user = new M_Empleados($conection, $response);
            $user->INSERT_EMPLE($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el empleado", 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_Empleados($conection, $response);
            $user->UPDATE_EMPLE($_PUT);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar el empleado", 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_Empleados($conection, $response);
            $user->DELETE_EMPLE($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el empleado", 2004, 400);
        }
        break;
}
