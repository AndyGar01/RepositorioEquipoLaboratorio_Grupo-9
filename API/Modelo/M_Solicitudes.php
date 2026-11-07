<?php

class M_Solicitudes
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_PENDIENTES()
    {
        $query = "SELECT sp.id_solicitud AS id, sp.id_alumno, a.carne_alumno AS carne,
                         CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno,
                         sp.fecha_solicitud, sp.fecha_devolucion_esperada,
                         (SELECT GROUP_CONCAT(CONCAT(e.nombre_equipo, ' x', dsp.cantidad_solicitada) ORDER BY e.nombre_equipo SEPARATOR ', ')
                          FROM detalle_solicitud_prestamo dsp
                          INNER JOIN equipo e ON e.id_equipo = dsp.id_equipo
                          WHERE dsp.id_solicitud = sp.id_solicitud) AS equipos
                  FROM solicitud_prestamo sp
                  INNER JOIN alumno a ON a.id_alumno = sp.id_alumno
                  INNER JOIN estado_solicitud_prestamo es ON es.id_estado_solicitud = sp.id_estado_solicitud
                  WHERE es.nombre_estado_solicitud = 'Pendiente'
                  ORDER BY sp.fecha_solicitud";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->response->success("Solicitudes pendientes obtenidas correctamente", $statement->fetchAll(PDO::FETCH_ASSOC), 1);
    }

    function RESOLVER($datos, $idUsuario)
    {
        $idSolicitud = filter_var($datos['id'] ?? null, FILTER_VALIDATE_INT);
        $accion = $datos['accion'] ?? '';
        if (!$idSolicitud || !in_array($accion, ['aprobar', 'rechazar'], true)) {
            throw new InvalidArgumentException("Selecciona una solicitud y una decisión válida.");
        }

        $this->connection->beginTransaction();
        try {
            $consulta = $this->connection->prepare("SELECT sp.id_alumno, sp.fecha_devolucion_esperada, es.nombre_estado_solicitud FROM solicitud_prestamo sp INNER JOIN estado_solicitud_prestamo es ON es.id_estado_solicitud = sp.id_estado_solicitud WHERE sp.id_solicitud = :id FOR UPDATE");
            $consulta->execute(['id' => $idSolicitud]);
            $solicitud = $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$solicitud || $solicitud['nombre_estado_solicitud'] !== 'Pendiente') {
                throw new InvalidArgumentException("La solicitud ya fue atendida o no existe.");
            }

            if ($accion === 'rechazar') {
                $estado = $this->idEstado('Rechazada');
                $actualizar = $this->connection->prepare("UPDATE solicitud_prestamo SET id_estado_solicitud = :estado, id_usuario_resuelve = :usuario, fecha_resolucion = NOW() WHERE id_solicitud = :id");
                $actualizar->execute(['estado' => $estado, 'usuario' => $idUsuario, 'id' => $idSolicitud]);
                $this->connection->commit();
                $this->response->success("Solicitud rechazada.", [], 1);
            }

            $alumno = $this->connection->prepare("SELECT a.activo_alumno, LOWER(ea.nombre_estado_alumno) AS estado FROM alumno a INNER JOIN estado_alumno ea ON ea.id_estado_alumno = a.id_estado_alumno WHERE a.id_alumno = :id FOR UPDATE");
            $alumno->execute(['id' => $solicitud['id_alumno']]);
            $estadoAlumno = $alumno->fetch(PDO::FETCH_ASSOC);
            if (!$estadoAlumno || (int) $estadoAlumno['activo_alumno'] !== 1 || $estadoAlumno['estado'] !== 'activo') {
                throw new InvalidArgumentException("El alumno ya no está activo.");
            }

            $fechaEsperada = DateTime::createFromFormat('!Y-m-d', $solicitud['fecha_devolucion_esperada']);
            if (!$fechaEsperada || $fechaEsperada < new DateTime('today')) {
                throw new InvalidArgumentException("La fecha esperada de devolución ya pasó. Contacta al alumno para que envíe una nueva solicitud.");
            }

            $lineas = $this->connection->prepare("SELECT id_equipo, cantidad_solicitada FROM detalle_solicitud_prestamo WHERE id_solicitud = :id");
            $lineas->execute(['id' => $idSolicitud]);
            $pedido = $lineas->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!$pedido) {
                throw new InvalidArgumentException("La solicitud no contiene equipos.");
            }

            $ids = array_map('intval', array_keys($pedido));
            $marcas = implode(',', array_fill(0, count($ids), '?'));
            $query = "SELECT e.id_equipo, e.nombre_equipo, e.activo, e.cantidad_existencias,
                             LOWER(s.nombre_estado_equipo) AS estado,
                             COALESCE((SELECT SUM(dp.cantidad_prestada)
                                 FROM detalle_prestamo dp
                                 INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo
                                 INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                                 WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso'), 0) AS prestados
                      FROM equipo e INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo
                      WHERE e.id_equipo IN ($marcas) FOR UPDATE";
            $equipos = $this->connection->prepare($query);
            $equipos->execute($ids);
            $lista = $equipos->fetchAll(PDO::FETCH_ASSOC);
            if (count($lista) !== count($pedido)) {
                throw new InvalidArgumentException("Uno de los equipos de la solicitud ya no existe.");
            }
            foreach ($lista as $equipo) {
                $disponibles = (int) $equipo['cantidad_existencias'] - (int) $equipo['prestados'];
                if ((int) $equipo['activo'] !== 1 || $equipo['estado'] !== 'existente' || (int) $pedido[$equipo['id_equipo']] > $disponibles) {
                    throw new InvalidArgumentException("No hay existencias suficientes de " . $equipo['nombre_equipo'] . ". Rechaza la solicitud o revisa con el alumno.");
                }
            }

            $idEstadoPrestamo = $this->idEstadoPrestamo('En proceso');
            $insertarPrestamo = $this->connection->prepare("INSERT INTO prestamo (id_usuario, id_alumno, id_estado_prestamo, fecha_devolucion_esperada) VALUES (:usuario, :alumno, :estado, :fecha)");
            $insertarPrestamo->execute([
                'usuario' => $idUsuario,
                'alumno' => $solicitud['id_alumno'],
                'estado' => $idEstadoPrestamo,
                'fecha' => $solicitud['fecha_devolucion_esperada']
            ]);
            $idPrestamo = (int) $this->connection->lastInsertId();
            $insertarDetalle = $this->connection->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_equipo, cantidad_prestada) VALUES (:prestamo, :equipo, :cantidad)");
            foreach ($pedido as $idEquipo => $cantidad) {
                $insertarDetalle->execute(['prestamo' => $idPrestamo, 'equipo' => $idEquipo, 'cantidad' => $cantidad]);
            }

            $estado = $this->idEstado('Aprobada');
            $actualizar = $this->connection->prepare("UPDATE solicitud_prestamo SET id_estado_solicitud = :estado, id_usuario_resuelve = :usuario, id_prestamo = :prestamo, fecha_resolucion = NOW() WHERE id_solicitud = :id");
            $actualizar->execute(['estado' => $estado, 'usuario' => $idUsuario, 'prestamo' => $idPrestamo, 'id' => $idSolicitud]);
            $this->connection->commit();
            $this->response->success("Solicitud aprobada y préstamo registrado.", ['id_prestamo' => $idPrestamo], 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw $e;
        }
    }

    private function idEstado($nombre)
    {
        $consulta = $this->connection->prepare("SELECT id_estado_solicitud FROM estado_solicitud_prestamo WHERE nombre_estado_solicitud = :nombre LIMIT 1");
        $consulta->execute(['nombre' => $nombre]);
        $id = $consulta->fetchColumn();
        if (!$id) throw new RuntimeException("Falta configurar el estado de solicitud " . $nombre . ".");
        return $id;
    }

    private function idEstadoPrestamo($nombre)
    {
        $consulta = $this->connection->prepare("SELECT id_estado_prestamo FROM estado_prestamo WHERE LOWER(nombre_estado_prestamo) = LOWER(:nombre) LIMIT 1");
        $consulta->execute(['nombre' => $nombre]);
        $id = $consulta->fetchColumn();
        if (!$id) throw new RuntimeException("Falta configurar el estado de préstamo " . $nombre . ".");
        return $id;
    }
}
