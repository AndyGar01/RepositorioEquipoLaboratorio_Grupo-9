<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Dashboard.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];
AdminApi::verificar($conection, $response, $method, null, true);

if ($method !== 'GET') {
    $response->error("Método no permitido", 4200, 405);
}

$periodo = $_GET['periodo'] ?? 'todo';
if (!in_array($periodo, ['todo', '30', '180', '365'], true)) {
    $response->error("El periodo seleccionado no es válido", 4201, 400);
}

try {
    $dashboard = new M_Dashboard($conection, $response);
    $dashboard->SELECT_RESUMEN($periodo);
} catch (\Throwable $th) {
    $response->error("Error al obtener el resumen del dashboard", 4202, 400);
    #$response->debug(null, $th);
}
