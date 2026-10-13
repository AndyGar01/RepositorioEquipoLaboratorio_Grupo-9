<?php
require_once __DIR__ . "/../Core/Response.php";
require_once __DIR__ . "/../Modelo/M_Login.php";

$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new M_Login();

switch ($metodo) {
    case 'POST':
        $correo     = trim($_POST['correo']     ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        if (empty($correo) || empty($contrasena)) {
            Response::error('Correo y contraseña son requeridos.', 'POST', 400);
        }
        $resultado = $modelo->LOGIN($correo, $contrasena);
        if (!$resultado['exito']) {
            Response::error($resultado['mensaje'], 'POST', 401);
        }
        Response::success(
            'Sesion iniciada correctamente.',
            ['usuario' => $resultado['usuario'], 'token' => $resultado['token']],
            200
        );
        break;

    case 'GET':
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            Response::error('Token no proporcionado.', 'GET', 400);
        }
        $resultado = $modelo->VERIFICAR_SESION($token);
        if (!$resultado['valido']) {
            Response::error('Sesion invalida o expirada.', 'GET', 401);
        }
        Response::success('Sesion valida.', $resultado['usuario'], 200);
        break;

    case 'DELETE':
        parse_str(file_get_contents('php://input'), $body);
        $token = trim($body['token'] ?? '');
        if (empty($token)) {
            Response::error('Token no proporcionado.', 'DELETE', 400);
        }
        $resultado = $modelo->LOGOUT($token);
        if (!$resultado['exito']) {
            Response::error('No se pudo cerrar la sesion.', 'DELETE', 500);
        }
        Response::success('Sesion cerrada correctamente.', null, 200);
        break;

    default:
        Response::error('Metodo no permitido.', $metodo, 405);
        break;
}