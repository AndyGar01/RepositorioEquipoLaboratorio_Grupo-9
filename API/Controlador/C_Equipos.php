<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Equipos.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/Permisos.php");

$conection = new Connection();
$response = new Response();
$permisos = new Permisos($conection);
$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['id_usuario'], $_SESSION['id_perfil'])) {
    $response->error(
        "Debes iniciar sesión para acceder.",
        2005,
        401
    );
    exit;
}

$id_perfil = $_SESSION['id_perfil'];
$id_modulo = 8;

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

if (!$permisos->verificar($id_perfil, $id_modulo, $method)) {
    $response->error(
        "No tienes permisos para realizar esta operación.",
        2006,
        403
    );
    exit;
}

switch ($method) {
    case 'GET':
        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->SELECT_EQUIPOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los equipos", 3001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $id_categoria = $_POST['id_categoria'] ?? null;
        $id_estado_equipo = $_POST['id_estado'] ?? null;
        $codigo_equipo = $_POST['codigo'] ?? null;
        $nombre_equipo = $_POST['nombre'] ?? null;
        $descripcion_equipo = $_POST['descripcion'] ?? null;
        $numero_serie = $_POST['numero_serie'] ?? null;
        $fecha_adquisicion = $_POST['fecha_adquisicion'] ?? null;
        $imagen_url = $_POST['imagen_url'] ?? null;

        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->INSERT_EQUIPO($_POST);
        } catch (\Throwable $th) {
            if ($th instanceof PDOException && ($th->errorInfo[1] ?? 0) == 1062) {
                $response->error("El código o el número de serie ya existe", 3005, 409);
            } else {
                $response->error("Error al agregar el equipo", 3002, 400);
                #$response->debug(null, $th);
            }
        }
        break;
    case 'PUT':
        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->UPDATE_EQUIPO($_PUT);
        } catch (\Throwable $th) {
            if ($th instanceof PDOException && ($th->errorInfo[1] ?? 0) == 1062) {
                $response->error("El código o el número de serie ya existe", 3005, 409);
            } else {
                $response->error("Error al actualizar el equipo", 3003, 400);
            }
        }
        break;
    case 'DELETE':
        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->DELETE_EQUIPO($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el equipo", 3004, 400);
        }
        break;
}