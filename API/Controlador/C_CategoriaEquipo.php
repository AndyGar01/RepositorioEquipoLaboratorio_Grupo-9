<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/Permisos.php");
require_once(__DIR__ . "/../Modelo/M_CategoriaEquipo.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['id_usuario'], $_SESSION['id_perfil'])) {
    $response->error("Debes iniciar sesión para acceder.", 2005, 401);
}

$permisos = new Permisos($connection);
if (!$permisos->verificar($_SESSION['id_perfil'], 6, $method)) {
    $response->error("No tienes permisos para realizar esta operación.", 2006, 403);
}

parse_str(file_get_contents("php://input"), $datos);
$modelo = new M_CategoriaEquipo($connection, $response);

try {
    switch ($method) {
        case 'GET':
            $modelo->SELECT_CATEGORIAS();
            break;
        case 'POST':
            $modelo->INSERT_CATEGORIA($_POST);
            break;
        case 'PUT':
            $modelo->UPDATE_CATEGORIA($datos);
            break;
        case 'DELETE':
            $modelo->DELETE_CATEGORIA($datos['id'] ?? 0);
            break;
        default:
            $response->error("Método no permitido.", 2007, 405);
    }
} catch (PDOException $e) {
    $response->error("No se pudo guardar o eliminar la categoría. Verifica que el nombre no esté repetido ni tenga equipos asociados.", 3101, 400);
} catch (Throwable $e) {
    $response->error("No se pudo procesar la categoría.", 3102, 400);
}
