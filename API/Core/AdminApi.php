<?php
require_once __DIR__ . "/../Modelo/M_Login.php";
require_once __DIR__ . "/Permisos.php";

class AdminApi
{
    public static function verificar(Connection $connection, Response $response, $method, $idModulo = null, $soloAdministrador = false)
    {
        $modeloLogin = new M_Login();
        $sesion = $modeloLogin->VERIFICAR_SESION($_COOKIE['vitalab_token'] ?? '');
        if (!$sesion['valido']) {
            $response->error("Inicia sesión para continuar.", 2501, 401);
        }

        $usuario = $sesion['usuario'];
        if ($usuario['perfil_nombre'] === 'Alumno') {
            $response->error("No tienes permisos para acceder a esta sección.", 2502, 403);
        }
        if ($soloAdministrador && $usuario['perfil_nombre'] !== 'Administrador') {
            $response->error("Solo el administrador puede realizar esta operación.", 2503, 403);
        }
        if ($idModulo !== null && !(new Permisos($connection))->verificar($usuario['perfil'], $idModulo, $method)) {
            $response->error("No tienes permisos para realizar esta operación.", 2504, 403);
        }

        return $usuario;
    }
}
