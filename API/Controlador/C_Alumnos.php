<?php
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Alumnos.php");
require_once(__DIR__ . "/../core/Response.php");
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
$id_modulo = 5;

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
            $user = new M_Alumnos($conection, $response);
            $user->SELECT_ALUMNOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los alumnos", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $id_carrera_alumno = $_POST['id_carrera'] ?? null;
        $id_estado_alumno = $_POST['id_estado'] ?? null;
        $carne_alumno = $_POST['carne'] ?? null;
        $nombre_alumno = $_POST['nombre'] ?? null;
        $apellido_alumno = $_POST['apellido'] ?? null;
        $correo_alumno = $_POST['correo'] ?? null;
        $fecha_nacimiento_alumno = $_POST['fecha_nacimiento'] ?? null;

        try {
            $user = new M_Alumnos($conection, $response);
            $user->INSERT_ALUMNO($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el alumno" . $th->getMessage(), 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
        try {
            $user = new M_Alumnos($conection, $response);
            $user->UPDATE_ALUMNO($_PUT);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar el alumno", 2003, 400);
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