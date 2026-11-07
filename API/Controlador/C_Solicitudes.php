<?php
require_once __DIR__ . "/../Core/Connection.php";
require_once __DIR__ . "/../Core/Response.php";
require_once __DIR__ . "/../Core/Permisos.php";
require_once __DIR__ . "/../Modelo/M_Login.php";
require_once __DIR__ . "/../Modelo/M_Solicitudes.php";

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
$login = new M_Login();
$sesion = $login->VERIFICAR_SESION($_COOKIE['vitalab_token'] ?? '');

if (!$sesion['valido']) {
    $response->error("Inicia sesión para continuar.", 2401, 401);
}
if ($sesion['usuario']['perfil_nombre'] === 'Alumno') {
    $response->error("No tienes permisos para administrar solicitudes.", 2402, 403);
}

$permisos = new Permisos($connection);
if (!$permisos->verificar($sesion['usuario']['perfil'], 7, $method)) {
    $response->error("No tienes permisos para realizar esta operación.", 2403, 403);
}

$solicitudes = new M_Solicitudes($connection, $response);
try {
    if ($method === 'GET') {
        $solicitudes->SELECT_PENDIENTES();
    }
    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $datos);
        $solicitudes->RESOLVER($datos, $sesion['usuario']['id']);
    }
    $response->error("Método no permitido.", 2404, 405);
} catch (InvalidArgumentException $e) {
    $response->error($e->getMessage(), 2405, 400);
} catch (Throwable $e) {
    $response->error("No se pudo procesar la solicitud.", 2406, 400);
}
