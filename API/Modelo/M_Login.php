<?php
require_once __DIR__ . "/../Core/Connection.php";

class M_Login extends Connection {

    // Verificar credenciales e iniciar sesion
    public function LOGIN($correo, $contrasena) {
        try {
            $sql = "SELECT id_usuario, id_perfil, nombre_usuario, apellido_usuario,
                           correo_usuario, contrasena_usuario, activo_usuario
                    FROM usuario
                    WHERE correo_usuario = :correo
                    AND activo_usuario = 1";

            $stmt = $this->prepare($sql);
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Usuario no existe o esta inactivo
            if (!$usuario) {
                return ['exito' => false, 'mensaje' => 'Credenciales incorrectas.'];
            }

            // Verificar contrasena con el hash guardado
            if (!password_verify($contrasena, $usuario['contrasena_usuario'])) {
                return ['exito' => false, 'mensaje' => 'Credenciales incorrectas.'];
            }

            // Generar token unico e impredecible
            $token      = bin2hex(random_bytes(32));
            $ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $expiracion = date('Y-m-d H:i:s', strtotime('+8 hours'));

            // Invalidar sesiones previas del mismo usuario
            $sqlInvalidar = "UPDATE sesion SET activo_sesion = 0
                             WHERE id_usuario = :id AND activo_sesion = 1";
            $stmtInvalidar = $this->prepare($sqlInvalidar);
            $stmtInvalidar->execute([':id' => $usuario['id_usuario']]);

            // Registrar nueva sesion en la base de datos
            $sqlSesion = "INSERT INTO sesion
                            (id_usuario, token_sesion, fecha_inicio_sesion,
                             fecha_expiracion_sesion, ip_acceso_sesion, activo_sesion)
                          VALUES
                            (:id_usuario, :token, NOW(), :expiracion, :ip, 1)";
            $stmtSesion = $this->prepare($sqlSesion);
            $stmtSesion->execute([
                ':id_usuario' => $usuario['id_usuario'],
                ':token'      => $token,
                ':expiracion' => $expiracion,
                ':ip'         => $ip
            ]);

            // Guardar datos basicos en la sesion PHP (server-side)
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre_usuario'];
            $_SESSION['apellido']   = $usuario['apellido_usuario'];
            $_SESSION['id_perfil']  = $usuario['id_perfil'];
            $_SESSION['token']      = $token;

            return [
                'exito'   => true,
                'token'   => $token,
                'usuario' => [
                    'id'      => $usuario['id_usuario'],
                    'nombre'  => $usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario'],
                    'correo'  => $usuario['correo_usuario'],
                    'perfil'  => $usuario['id_perfil']
                ]
            ];

        } catch (PDOException $e) {
            return ['exito' => false, 'mensaje' => 'Error interno del servidor.'];
        }
    }

    // Comprobar si un token sigue siendo valido
    public function VERIFICAR_SESION($token) {
        try {
            $sql = "SELECT s.id_usuario, u.nombre_usuario, u.apellido_usuario,
                           u.correo_usuario, u.id_perfil
                    FROM sesion s
                    INNER JOIN usuario u ON s.id_usuario = u.id_usuario
                    WHERE s.token_sesion      = :token
                    AND   s.activo_sesion     = 1
                    AND   s.fecha_expiracion_sesion > NOW()
                    AND   u.activo_usuario    = 1";

            $stmt = $this->prepare($sql);
            $stmt->execute([':token' => $token]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sesion) {
                return ['valido' => false];
            }

            return [
                'valido'  => true,
                'usuario' => [
                    'id'     => $sesion['id_usuario'],
                    'nombre' => $sesion['nombre_usuario'] . ' ' . $sesion['apellido_usuario'],
                    'correo' => $sesion['correo_usuario'],
                    'perfil' => $sesion['id_perfil']
                ]
            ];

        } catch (PDOException $e) {
            return ['valido' => false];
        }
    }

    // Cerrar sesion: invalidar token en BD y limpiar sesion PHP
    public function LOGOUT($token) {
        try {
            $sql  = "UPDATE sesion SET activo_sesion = 0 WHERE token_sesion = :token";
            $stmt = $this->prepare($sql);
            $stmt->execute([':token' => $token]);

            $_SESSION = [];
            session_destroy();

            return ['exito' => true];

        } catch (PDOException $e) {
            return ['exito' => false];
        }
    }
}