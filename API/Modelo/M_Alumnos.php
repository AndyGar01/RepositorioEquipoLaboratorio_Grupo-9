<?php

class M_Alumnos
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    private function contraseñaTemporal()
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    private function crearCuenta($alumno, $idAlumno, $reemplazar = false)
    {
        $correoOcupado = $this->connection->prepare("SELECT 1 FROM usuario WHERE correo_usuario = :correo AND (id_alumno IS NULL OR id_alumno <> :id_alumno) LIMIT 1");
        $correoOcupado->execute(['correo' => $alumno['correo'], 'id_alumno' => $idAlumno]);
        if ($correoOcupado->fetchColumn()) {
            throw new RuntimeException("El correo ya está asociado a otra cuenta del sistema.");
        }

        $perfil = $this->connection->prepare("SELECT id_perfil FROM perfil WHERE nombre_perfil = 'Alumno' AND activo_perfil = 1 LIMIT 1");
        $perfil->execute();
        $idPerfil = $perfil->fetchColumn();
        if (!$idPerfil) {
            throw new RuntimeException("No está configurado el perfil Alumno.");
        }

        $clave = $this->contraseñaTemporal();
        $hash = password_hash($clave, PASSWORD_BCRYPT);
        $cuenta = $this->connection->prepare("SELECT id_usuario FROM usuario WHERE id_alumno = :id_alumno LIMIT 1");
        $cuenta->execute(['id_alumno' => $idAlumno]);
        $idUsuario = $cuenta->fetchColumn();

        if ($idUsuario) {
            if (!$reemplazar) {
                return null;
            }
            $actualizar = $this->connection->prepare("UPDATE usuario SET id_perfil = :perfil, carne_usuario = :carne, nombre_usuario = :nombre, apellido_usuario = :apellido, correo_usuario = :correo, contrasena_usuario = :contrasena, cambio_contrasena_requerido = 1, activo_usuario = 1 WHERE id_usuario = :id");
            $actualizar->execute([
                'id' => $idUsuario,
                'perfil' => $idPerfil,
                'carne' => $alumno['carne'],
                'nombre' => $alumno['nombre'],
                'apellido' => $alumno['apellido'],
                'correo' => $alumno['correo'],
                'contrasena' => $hash
            ]);
            return $clave;
        }

        $insertar = $this->connection->prepare("INSERT INTO usuario (id_perfil, id_alumno, carne_usuario, nombre_usuario, apellido_usuario, correo_usuario, contrasena_usuario, cambio_contrasena_requerido, activo_usuario) VALUES (:perfil, :id_alumno, :carne, :nombre, :apellido, :correo, :contrasena, 1, 1)");
        $insertar->execute([
            'perfil' => $idPerfil,
            'id_alumno' => $idAlumno,
            'carne' => $alumno['carne'],
            'nombre' => $alumno['nombre'],
            'apellido' => $alumno['apellido'],
            'correo' => $alumno['correo'],
            'contrasena' => $hash
        ]);
        return $clave;
    }

    private function datosCuenta($datos)
    {
        return [
            'carne' => trim($datos['carne']),
            'nombre' => trim($datos['nombre']),
            'apellido' => trim($datos['apellido']),
            'correo' => trim($datos['correo'])
        ];
    }

    private function estadoActivo($idEstado)
    {
        $consulta = $this->connection->prepare("SELECT LOWER(nombre_estado_alumno) FROM estado_alumno WHERE id_estado_alumno = :id");
        $consulta->execute(['id' => $idEstado]);
        return $consulta->fetchColumn() === 'activo';
    }

    function SELECT_ALUMNOS()
    {
        $query = "SELECT id_alumno as id, id_carrera_alumno as id_carrera, id_estado_alumno as id_estado, carne_alumno as carne, nombre_alumno as nombre, apellido_alumno as apellido, correo_alumno as correo, fecha_nacimiento_alumno as fecha_nacimiento FROM alumno ORDER BY id_alumno DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->response->success("Alumnos obtenidos correctamente", $statement->fetchAll(PDO::FETCH_ASSOC), 1);
    }

    function INSERT_ALUMNO($arrayData)
    {
        $this->connection->beginTransaction();
        try {
            $query = "INSERT INTO alumno (id_carrera_alumno, id_estado_alumno, carne_alumno, nombre_alumno, apellido_alumno, correo_alumno, fecha_nacimiento_alumno) VALUES (:id_carrera, :id_estado, :carne, :nombre, :apellido, :correo, :fecha_nacimiento)";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'id_carrera' => $arrayData['id_carrera'],
                'id_estado' => $arrayData['id_estado'],
                'carne' => trim($arrayData['carne']),
                'nombre' => trim($arrayData['nombre']),
                'apellido' => trim($arrayData['apellido']),
                'correo' => trim($arrayData['correo']),
                'fecha_nacimiento' => $arrayData['fecha_nacimiento']
            ]);
            $idAlumno = (int) $this->connection->lastInsertId();
            $clave = null;
            if ($this->estadoActivo($arrayData['id_estado'])) {
                $clave = $this->crearCuenta($this->datosCuenta($arrayData), $idAlumno);
            }
            $this->connection->commit();
            $datos = $clave ? ['correo' => trim($arrayData['correo']), 'contrasena_temporal' => $clave] : [];
            $this->response->success($clave ? "Alumno agregado y acceso creado correctamente" : "Alumno agregado correctamente", $datos, 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    function UPDATE_ALUMNO($arrayData)
    {
        $this->connection->beginTransaction();
        try {
            $idAlumno = (int) $arrayData['id'];
            $existe = $this->connection->prepare("SELECT activo_alumno FROM alumno WHERE id_alumno = :id FOR UPDATE");
            $existe->execute(['id' => $idAlumno]);
            if ((int) $existe->fetchColumn() !== 1) {
                throw new RuntimeException("No se puede modificar un alumno dado de baja.");
            }
            $query = "UPDATE alumno SET id_carrera_alumno = :id_carrera, id_estado_alumno = :id_estado, carne_alumno = :carne, nombre_alumno = :nombre, apellido_alumno = :apellido, correo_alumno = :correo, fecha_nacimiento_alumno = :fecha_nacimiento WHERE id_alumno = :id AND activo_alumno = 1";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'id' => $idAlumno,
                'id_carrera' => $arrayData['id_carrera'],
                'id_estado' => $arrayData['id_estado'],
                'carne' => trim($arrayData['carne']),
                'nombre' => trim($arrayData['nombre']),
                'apellido' => trim($arrayData['apellido']),
                'correo' => trim($arrayData['correo']),
                'fecha_nacimiento' => $arrayData['fecha_nacimiento']
            ]);

            $clave = null;
            $datos = $this->datosCuenta($arrayData);
            if ($this->estadoActivo($arrayData['id_estado'])) {
                $clave = $this->crearCuenta($datos, $idAlumno);
                $actualizar = $this->connection->prepare("UPDATE usuario SET nombre_usuario = :nombre, apellido_usuario = :apellido, correo_usuario = :correo, carne_usuario = :carne, activo_usuario = 1 WHERE id_alumno = :id_alumno");
                $actualizar->execute([
                    'id_alumno' => $idAlumno,
                    'nombre' => $datos['nombre'],
                    'apellido' => $datos['apellido'],
                    'correo' => $datos['correo'],
                    'carne' => $datos['carne']
                ]);
            } else {
                $desactivar = $this->connection->prepare("UPDATE usuario SET activo_usuario = 0 WHERE id_alumno = :id_alumno");
                $desactivar->execute(['id_alumno' => $idAlumno]);
            }

            $this->connection->commit();
            $datosRespuesta = $clave ? ['correo' => $datos['correo'], 'contrasena_temporal' => $clave] : [];
            $this->response->success($clave ? "Alumno actualizado y acceso creado correctamente" : "Alumno actualizado correctamente", $datosRespuesta, 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    function GENERAR_ACCESO_ALUMNO($idAlumno)
    {
        $consulta = $this->connection->prepare("SELECT a.carne_alumno AS carne, a.nombre_alumno AS nombre, a.apellido_alumno AS apellido, a.correo_alumno AS correo, a.id_estado_alumno, a.activo_alumno FROM alumno a WHERE a.id_alumno = :id");
        $consulta->execute(['id' => $idAlumno]);
        $alumno = $consulta->fetch(PDO::FETCH_ASSOC);
        if (!$alumno || (int) $alumno['activo_alumno'] !== 1 || !$this->estadoActivo($alumno['id_estado_alumno'])) {
            $this->response->error("Solo los alumnos activos pueden tener acceso al catálogo.", 2105, 400);
        }

        $this->connection->beginTransaction();
        try {
            $clave = $this->crearCuenta($alumno, (int) $idAlumno, true);
            $this->connection->commit();
            $this->response->success("Acceso temporal generado correctamente", ['correo' => $alumno['correo'], 'contrasena_temporal' => $clave], 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    function DELETE_ALUMNO($idAlumno)
    {
        $this->connection->beginTransaction();
        try {
            $baja = $this->connection->prepare("UPDATE alumno SET activo_alumno = 0 WHERE id_alumno = :id");
            $baja->execute(['id' => $idAlumno]);
            $cuenta = $this->connection->prepare("UPDATE usuario SET activo_usuario = 0 WHERE id_alumno = :id_alumno");
            $cuenta->execute(['id_alumno' => $idAlumno]);
            $this->connection->commit();
            $this->response->success("Alumno dado de baja correctamente", [], 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }
}
