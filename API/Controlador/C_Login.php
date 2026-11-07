<?php
require_once __DIR__ . "/../Core/Response.php";
require_once __DIR__ . "/../Modelo/M_Login.php";

$metodo = $_SERVER['REQUEST_METHOD'];
$modelo = new M_Login();
$nombreCookie = 'vitalab_token';
$opcionesCookie = [
    'expires' => time() + 28800,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict'
];

switch ($metodo) {
    case 'POST':
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        if ($correo === '' || $contrasena === '') {
            Response::error('Correo y contraseña son requeridos.', 2001, 400);
        }

        $resultado = $modelo->LOGIN($correo, $contrasena);
        if (!$resultado['exito']) {
            Response::error($resultado['mensaje'], 2002, 401);
        }

        setcookie($nombreCookie, $resultado['token'], $opcionesCookie);
        Response::success('Sesión iniciada correctamente.', $resultado['usuario'], 1);
        break;

    case 'GET':
        $token = $_COOKIE[$nombreCookie] ?? '';
        if ($token === '') {
            Response::error('Debes iniciar sesión.', 2003, 401);
        }
        $resultado = $modelo->VERIFICAR_SESION($token);
        if (!$resultado['valido']) {
            setcookie($nombreCookie, '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Strict', 'httponly' => true, 'secure' => $opcionesCookie['secure']]);
            Response::error('La sesión no es válida o ya venció.', 2004, 401);
        }
        Response::success('Sesión válida.', $resultado['usuario'], 1);
        break;

    case 'PUT':
        $token = $_COOKIE[$nombreCookie] ?? '';
        $sesion = $modelo->VERIFICAR_SESION($token);
        if (!$sesion['valido']) {
            Response::error('Debes iniciar sesión.', 2003, 401);
        }
        parse_str(file_get_contents('php://input'), $datos);
        $actual = $datos['contrasena_actual'] ?? '';
        $nueva = $datos['contrasena_nueva'] ?? '';
        if (strlen($nueva) < 8) {
            Response::error('La nueva contraseña debe tener al menos 8 caracteres.', 2005, 400);
        }
        if (!$modelo->CAMBIAR_CONTRASENA($sesion['usuario']['id'], $actual, $nueva)) {
            Response::error('La contraseña actual no es correcta.', 2006, 400);
        }
        Response::success('Contraseña actualizada correctamente.', [], 1);
        break;

    case 'DELETE':
        $token = $_COOKIE[$nombreCookie] ?? '';
        if ($token !== '') {
            $modelo->LOGOUT($token);
        }
        setcookie($nombreCookie, '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Strict', 'httponly' => true, 'secure' => $opcionesCookie['secure']]);
        Response::success('Sesión cerrada correctamente.', [], 1);
        break;

    default:
        Response::error('Método no permitido.', 2007, 405);
}
