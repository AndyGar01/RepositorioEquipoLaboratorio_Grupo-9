<?php
require_once __DIR__ . "/../Core/Connection.php";
require_once __DIR__ . "/../Core/Response.php";
require_once __DIR__ . "/../Modelo/M_Login.php";
require_once __DIR__ . "/../Modelo/M_Cliente.php";

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
$token = $_COOKIE['vitalab_token'] ?? '';
$login = new M_Login();
$sesion = $login->VERIFICAR_SESION($token);

if (!$sesion['valido']) {
    $response->error("Inicia sesión para continuar.", 2301, 401);
}
if ($sesion['usuario']['perfil_nombre'] !== 'Alumno' || empty($sesion['usuario']['id_alumno'])) {
    $response->error("Esta sección es exclusiva para alumnos.", 2302, 403);
}
if ((int) $sesion['usuario']['cambio_contrasena_requerido'] === 1) {
    $response->error("Cambia tu contraseña temporal para continuar.", 2303, 403);
}

$cliente = new M_Cliente($connection, $response);

try {
    if ($method === 'GET') {
        if (isset($_GET['solicitudes'])) {
            $cliente->SELECT_MIS_SOLICITUDES($sesion['usuario']['id_alumno']);
        }
        $cliente->SELECT_CATALOGO();
    }

    if ($method === 'POST') {
        $cliente->CREAR_SOLICITUD($_POST, $sesion['usuario']['id_alumno']);
    }

    $response->error("Método no permitido.", 2304, 405);
} catch (InvalidArgumentException $e) {
    $response->error($e->getMessage(), 2305, 400);
} catch (Throwable $e) {
    $response->error("No se pudo completar la operación.", 2306, 400);
}
