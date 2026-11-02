<?php

class M_Prestamos
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_PRESTAMOS()
    {
        $query = "SELECT p.id_prestamo AS id, p.id_alumno, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, p.id_estado_prestamo AS id_estado, ep.nombre_estado_prestamo AS estado, p.fecha_prestamo, p.fecha_devolucion_esperada, GROUP_CONCAT(e.codigo_equipo ORDER BY e.codigo_equipo SEPARATOR ', ') AS equipos FROM prestamo p INNER JOIN alumno a ON a.id_alumno = p.id_alumno INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo LEFT JOIN detalle_prestamo dp ON dp.id_prestamo = p.id_prestamo LEFT JOIN equipo e ON e.id_equipo = dp.id_equipo GROUP BY p.id_prestamo ORDER BY p.id_prestamo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Préstamos obtenidos correctamente", $result, 1);
    }

    function INSERT_PRESTAMO($arrayData, $id_usuario)
    {
        $id_alumno = $arrayData['id_alumno'];
        $fecha_devolucion_esperada = $arrayData['fecha_devolucion_esperada'];
        $equipos = $arrayData['equipos'];

        if (!is_array($equipos)) {
            $equipos = explode(',', $equipos);
        }
        $equipos = array_values(array_unique(array_filter($equipos)));

        if (empty($id_alumno) || empty($fecha_devolucion_esperada) || count($equipos) == 0) {
            $this->response->error("Alumno, fecha de devolución y equipo son obligatorios", 4010, 400);
        }
        if (count($equipos) > 10) {
            $this->response->error("Un préstamo puede tener máximo 10 equipos", 4011, 400);
        }

        $hoy = new DateTime('today');
        $fecha = DateTime::createFromFormat('!Y-m-d', $fecha_devolucion_esperada);
        if (!$fecha || $fecha < $hoy || $fecha > (clone $hoy)->modify('+30 days')) {
            $this->response->error("La fecha de devolución debe estar entre hoy y 30 días", 4012, 400);
        }

        $query = "SELECT activo_alumno FROM alumno WHERE id_alumno = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_alumno]);
        $alumno = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$alumno || $alumno['activo_alumno'] != 1) {
            $this->response->error("El alumno no existe o está inactivo", 4013, 400);
        }

        $this->connection->beginTransaction();
        try {
            $query = "SELECT id_estado_prestamo FROM estado_prestamo WHERE LOWER(nombre_estado_prestamo) = 'en proceso'";
            $statement = $this->connection->prepare($query);
            $statement->execute();
            $id_estado_proceso = $statement->fetchColumn();

            $query = "SELECT id_estado_equipo FROM estado_equipo WHERE LOWER(nombre_estado_equipo) = 'rentado'";
            $statement = $this->connection->prepare($query);
            $statement->execute();
            $id_estado_rentado = $statement->fetchColumn();
            if (!$id_estado_proceso || !$id_estado_rentado) {
                throw new Exception("Faltan los estados 'en proceso' o 'rentado' en la base de datos");
            }

            $marcas = implode(',', array_fill(0, count($equipos), '?'));
            $query = "SELECT e.id_equipo, e.codigo_equipo, e.activo, LOWER(s.nombre_estado_equipo) AS estado FROM equipo e INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo WHERE e.id_equipo IN ($marcas) FOR UPDATE";
            $statement = $this->connection->prepare($query);
            $statement->execute($equipos);
            $lista = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($lista) != count($equipos)) {
                $this->connection->rollBack();
                $this->response->error("Uno de los equipos seleccionados no existe", 4014, 400);
            }
            foreach ($lista as $equipo) {
                if ($equipo['activo'] != 1 || $equipo['estado'] != 'existente') {
                    $this->connection->rollBack();
                    $this->response->error("El equipo " . $equipo['codigo_equipo'] . " no está disponible", 4015, 400);
                }
            }

            $query = "SELECT e.codigo_equipo FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo INNER JOIN equipo e ON e.id_equipo = dp.id_equipo WHERE p.id_estado_prestamo = ? AND dp.id_equipo IN ($marcas)";
            $statement = $this->connection->prepare($query);
            $statement->execute(array_merge([$id_estado_proceso], $equipos));
            $ocupado = $statement->fetchColumn();
            if ($ocupado) {
                $this->connection->rollBack();
                $this->response->error("El equipo " . $ocupado . " ya está prestado", 4016, 400);
            }

            $query = "INSERT INTO prestamo (id_usuario, id_alumno, id_estado_prestamo, fecha_devolucion_esperada) VALUES (:id_usuario, :id_alumno, :id_estado, :fecha_devolucion_esperada)";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'id_usuario' => $id_usuario,
                'id_alumno' => $id_alumno,
                'id_estado' => $id_estado_proceso,
                'fecha_devolucion_esperada' => $fecha->format('Y-m-d')
            ]);
            $id_prestamo = $this->connection->lastInsertId();

            foreach ($equipos as $id_equipo) {
                $query = "INSERT INTO detalle_prestamo (id_prestamo, id_equipo) VALUES (:id_prestamo, :id_equipo)";
                $statement = $this->connection->prepare($query);
                $statement->execute(['id_prestamo' => $id_prestamo, 'id_equipo' => $id_equipo]);

                $query = "UPDATE equipo SET id_estado_equipo = :id_estado WHERE id_equipo = :id_equipo";
                $statement = $this->connection->prepare($query);
                $statement->execute(['id_estado' => $id_estado_rentado, 'id_equipo' => $id_equipo]);
            }

            $this->connection->commit();
        } catch (\Throwable $th) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $th;
        }
        $this->response->success("Préstamo agregado correctamente", ['id_prestamo' => $id_prestamo], 1);
    }
}
