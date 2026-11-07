<?php
require_once __DIR__ . "/../Core/Connection.php";

class M_Login extends Connection
{
    public function LOGIN($correo, $contrasena)
    {
        try {
            $sql = "SELECT u.id_usuario, u.id_perfil, u.id_alumno, u.nombre_usuario,
                           u.apellido_usuario, u.correo_usuario, u.contrasena_usuario,
                           u.cambio_contrasena_requerido, p.nombre_perfil
                    FROM usuario u
                    INNER JOIN perfil p ON p.id_perfil = u.id_perfil
                    LEFT JOIN alumno a ON a.id_alumno = u.id_alumno
                    LEFT JOIN estado_alumno ea ON ea.id_estado_alumno = a.id_estado_alumno
                    WHERE u.correo_usuario = :correo
                      AND u.activo_usuario = 1
                      AND p.activo_perfil = 1
                      AND (p.nombre_perfil <> 'Alumno' OR (a.activo_alumno = 1 AND LOWER(ea.nombre_estado_alumno) = 'activo'))";
            $stmt = $this->prepare($sql);
            $stmt->execute(['correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($contrasena, $usuario['contrasena_usuario'])) {
                return ['exito' => false, 'mensaje' => 'Credenciales incorrectas o cuenta inactiva.'];
            }
            if ($usuario['nombre_perfil'] === 'Alumno' && empty($usuario['id_alumno'])) {
                return ['exito' => false, 'mensaje' => 'La cuenta de alumno no está vinculada a un registro activo.'];
            }

            $token = bin2hex(random_bytes(32));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $expiracion = date('Y-m-d H:i:s', strtotime('+8 hours'));

            $invalidar = $this->prepare("UPDATE sesion SET activo_sesion = 0 WHERE id_usuario = :id AND activo_sesion = 1");
            $invalidar->execute(['id' => $usuario['id_usuario']]);

            $insertar = $this->prepare("INSERT INTO sesion (id_usuario, token_sesion, fecha_inicio_sesion, fecha_expiracion_sesion, ip_acceso_sesion, activo_sesion) VALUES (:id_usuario, :token, NOW(), :expiracion, :ip, 1)");
            $insertar->execute([
                'id_usuario' => $usuario['id_usuario'],
                'token' => $token,
                'expiracion' => $expiracion,
                'ip' => $ip
            ]);

            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre_usuario'];
            $_SESSION['apellido'] = $usuario['apellido_usuario'];
            $_SESSION['id_perfil'] = $usuario['id_perfil'];
            $_SESSION['perfil_nombre'] = $usuario['nombre_perfil'];
            $_SESSION['id_alumno'] = $usuario['id_alumno'];
            $_SESSION['token'] = $token;

            return [
                'exito' => true,
                'token' => $token,
                'usuario' => [
                    'id' => (int) $usuario['id_usuario'],
                    'nombre' => $usuario['nombre_usuario'] . ' ' . $usuario['apellido_usuario'],
                    'correo' => $usuario['correo_usuario'],
                    'perfil' => (int) $usuario['id_perfil'],
                    'perfil_nombre' => $usuario['nombre_perfil'],
                    'id_alumno' => $usuario['id_alumno'] === null ? null : (int) $usuario['id_alumno'],
                    'cambio_contrasena_requerido' => (int) $usuario['cambio_contrasena_requerido']
                ]
            ];
        } catch (PDOException $e) {
            return ['exito' => false, 'mensaje' => 'Error interno del servidor.'];
        }
    }

    public function VERIFICAR_SESION($token)
    {
        try {
            $sql = "SELECT s.id_usuario, u.id_perfil, u.id_alumno, u.nombre_usuario,
                           u.apellido_usuario, u.correo_usuario, u.cambio_contrasena_requerido,
                           p.nombre_perfil
                    FROM sesion s
                    INNER JOIN usuario u ON u.id_usuario = s.id_usuario
                    INNER JOIN perfil p ON p.id_perfil = u.id_perfil
                    LEFT JOIN alumno a ON a.id_alumno = u.id_alumno
                    LEFT JOIN estado_alumno ea ON ea.id_estado_alumno = a.id_estado_alumno
                    WHERE s.token_sesion = :token
                      AND s.activo_sesion = 1
                      AND s.fecha_expiracion_sesion > NOW()
                      AND u.activo_usuario = 1
                      AND p.activo_perfil = 1
                      AND (p.nombre_perfil <> 'Alumno' OR (a.activo_alumno = 1 AND LOWER(ea.nombre_estado_alumno) = 'activo'))";
            $stmt = $this->prepare($sql);
            $stmt->execute(['token' => $token]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sesion || ($sesion['nombre_perfil'] === 'Alumno' && empty($sesion['id_alumno']))) {
                return ['valido' => false];
            }

            return [
                'valido' => true,
                'usuario' => [
                    'id' => (int) $sesion['id_usuario'],
                    'nombre' => $sesion['nombre_usuario'] . ' ' . $sesion['apellido_usuario'],
                    'correo' => $sesion['correo_usuario'],
                    'perfil' => (int) $sesion['id_perfil'],
                    'perfil_nombre' => $sesion['nombre_perfil'],
                    'id_alumno' => $sesion['id_alumno'] === null ? null : (int) $sesion['id_alumno'],
                    'cambio_contrasena_requerido' => (int) $sesion['cambio_contrasena_requerido']
                ]
            ];
        } catch (PDOException $e) {
            return ['valido' => false];
        }
    }

    public function CAMBIAR_CONTRASENA($idUsuario, $actual, $nueva)
    {
        $consulta = $this->prepare("SELECT contrasena_usuario FROM usuario WHERE id_usuario = :id AND activo_usuario = 1");
        $consulta->execute(['id' => $idUsuario]);
        $hash = $consulta->fetchColumn();
        if (!$hash || !password_verify($actual, $hash)) {
            return false;
        }

        $actualizar = $this->prepare("UPDATE usuario SET contrasena_usuario = :contrasena, cambio_contrasena_requerido = 0 WHERE id_usuario = :id");
        $actualizar->execute([
            'id' => $idUsuario,
            'contrasena' => password_hash($nueva, PASSWORD_BCRYPT)
        ]);
        return true;
    }

    public function LOGOUT($token)
    {
        try {
            $stmt = $this->prepare("UPDATE sesion SET activo_sesion = 0 WHERE token_sesion = :token");
            $stmt->execute(['token' => $token]);
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
