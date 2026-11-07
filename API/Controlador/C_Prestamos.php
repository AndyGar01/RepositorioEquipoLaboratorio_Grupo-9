<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Prestamos.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, 7);

if (!isset($_SESSION['id_usuario'], $_SESSION['id_perfil'])) {
    $response->error(
        "Debes iniciar sesión para acceder.",
        2005,
        401
    );
    exit;
}

switch ($method) {
    case 'GET':
        try {
            $prestamo = new M_Prestamos($conection, $response);
            if (isset($_GET['equipos'])) {
                $prestamo->SELECT_EQUIPOS_DISPONIBLES();
            } else {
                $prestamo->SELECT_PRESTAMOS();
            }
        } catch (\Throwable $th) {
            $response->error("Error al obtener los préstamos", 4001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $id_alumno = $_POST['id_alumno'] ?? null;
        $fecha_devolucion_esperada = $_POST['fecha_devolucion_esperada'] ?? null;
        $equipos = $_POST['equipos'] ?? [];

        try {
            $prestamo = new M_Prestamos($conection, $response);
            $prestamo->INSERT_PRESTAMO($_POST, $_SESSION['id_usuario']);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el préstamo", 4002, 400);
            #$response->debug(null, $th);
        }
        break;
}
