<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Alumnos.php");
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
            $user = new M_Alumnos($conection, $response);
            $user->SELECT_ALUMNOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los alumnos", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        try {
            $user = new M_Alumnos($conection, $response);
            if (($_POST['accion'] ?? '') === 'generar_acceso') {
                $user->GENERAR_ACCESO_ALUMNO($_POST['id'] ?? 0);
            }
            $user->INSERT_ALUMNO($_POST);
        } catch (PDOException $th) {
            if (($th->errorInfo[1] ?? 0) === 1062) {
                $response->error("El carné o el correo ya está registrado.", 2007, 409);
            }
            $response->error("No se pudo guardar el alumno o crear su acceso.", 2002, 400);
        } catch (\Throwable $th) {
            $response->error($th->getMessage(), 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_Alumnos($conection, $response);
            $user->UPDATE_ALUMNO($_PUT);
        } catch (PDOException $th) {
            if (($th->errorInfo[1] ?? 0) === 1062) {
                $response->error("El carné o el correo ya está registrado.", 2007, 409);
            }
            $response->error("No se pudo actualizar el alumno o su cuenta.", 2003, 400);
        } catch (\Throwable $th) {
            $response->error($th->getMessage(), 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $user = new M_Alumnos($conection, $response);
            $user->DELETE_ALUMNO($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el alumno", 2004, 400);
        }
        break;
}
