<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Devoluciones.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, 8);

if (!isset($_SESSION['id_usuario'], $_SESSION['id_perfil'])) {
    $response->error(
        "Debes iniciar sesión para acceder.",
        2005,
        401
    );
    exit;
}

parse_str(file_get_contents("php://input"), $_PUT);

switch ($method) {
    case 'GET':
        try {
            $devolucion = new M_Devoluciones($conection, $response);
            if (isset($_GET['id_prestamo'])) {
                $devolucion->SELECT_DETALLE_PRESTAMO((int) $_GET['id_prestamo']);
            } elseif (isset($_GET['reparaciones'])) {
                $devolucion->SELECT_PENDIENTES_REPARACION();
            } elseif (isset($_GET['notificaciones'])) {
                $devolucion->SELECT_NOTIFICACIONES($_SESSION['id_usuario']);
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
    case 'PUT':
        try {
            $devolucion = new M_Devoluciones($conection, $response);
            if (isset($_PUT['id_notificacion'])) {
                $devolucion->UPDATE_NOTIFICACION_LEIDA((int) $_PUT['id_notificacion'], $_SESSION['id_usuario']);
            } else {
                $devolucion->UPDATE_REPARACION($_PUT['id_detalle_devolucion'] ?? 0, $_PUT['cantidad'] ?? 0);
            }
        } catch (\Throwable $th) {
            $response->error("Error al actualizar la reparación", 4103, 400);
            #$response->debug(null, $th);
        }
        break;
}
