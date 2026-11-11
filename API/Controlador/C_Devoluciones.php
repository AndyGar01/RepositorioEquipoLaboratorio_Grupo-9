<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Devoluciones.php");
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
            $devolucion = new M_Devoluciones($conection, $response);
            if (isset($_GET['id_prestamo'])) {
                $devolucion->SELECT_DETALLE_PRESTAMO((int) $_GET['id_prestamo']);
            } else {
                $devolucion->SELECT_PRESTAMOS_PENDIENTES();
            }
        } catch (\Throwable $th) {
            $response->error("Error al obtener los préstamos pendientes", 4101, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        try {
            $devolucion = new M_Devoluciones($conection, $response);
            $devolucion->INSERT_DEVOLUCION($_POST, $_SESSION['id_usuario']);
        } catch (\Throwable $th) {
            $response->error("Error al registrar la devolución", 4102, 400);
            #$response->debug(null, $th);
        }
        break;
}
