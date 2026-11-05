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
        $query = "SELECT p.id_prestamo AS id, p.id_alumno, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, p.id_estado_prestamo AS id_estado, ep.nombre_estado_prestamo AS estado, p.fecha_prestamo, p.fecha_devolucion_esperada, GROUP_CONCAT(e.codigo_equipo, ' x', dp.cantidad_prestada ORDER BY e.codigo_equipo SEPARATOR ', ') AS equipos FROM prestamo p INNER JOIN alumno a ON a.id_alumno = p.id_alumno INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo LEFT JOIN detalle_prestamo dp ON dp.id_prestamo = p.id_prestamo LEFT JOIN equipo e ON e.id_equipo = dp.id_equipo GROUP BY p.id_prestamo ORDER BY p.id_prestamo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Préstamos obtenidos correctamente", $result, 1);
    }

    function SELECT_EQUIPOS_DISPONIBLES()
    {
        $query = "SELECT e.id_equipo AS id, e.id_categoria, c.nombre_categoria AS categoria, e.codigo_equipo AS codigo, e.nombre_equipo AS nombre, e.cantidad_existencias, e.cantidad_existencias - COALESCE((SELECT SUM(dp.cantidad_prestada) FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso'), 0) AS cantidad_disponible FROM equipo e INNER JOIN categoria_equipo c ON c.id_categoria = e.id_categoria INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo WHERE e.activo = 1 AND LOWER(s.nombre_estado_equipo) = 'existente' ORDER BY e.nombre_equipo, e.codigo_equipo";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Equipos obtenidos correctamente", $result, 1);
    }

    function INSERT_PRESTAMO($arrayData, $id_usuario)
    {
        $id_alumno = $arrayData['id_alumno'];
        $fecha_devolucion_esperada = $arrayData['fecha_devolucion_esperada'];
        $equipos = $arrayData['equipos'];

        if (!is_array($equipos)) {
            $equipos = explode(',', $equipos);
        }
        $pedido = [];
        foreach ($equipos as $linea) {
            $partes = explode(':', $linea);
            $id_equipo = (int) $partes[0];
            $cantidad = (int) ($partes[1] ?? 0);
            if ($id_equipo > 0) {
                $pedido[$id_equipo] = ($pedido[$id_equipo] ?? 0) + $cantidad;
            }
        }

        if (empty($id_alumno) || empty($fecha_devolucion_esperada) || count($pedido) == 0) {
            $this->response->error("Alumno, fecha de devolución y equipo son obligatorios", 4010, 400);
        }
        if (count($pedido) > 10) {
            $this->response->error("Un préstamo puede tener máximo 10 equipos", 4011, 400);
        }
        foreach ($pedido as $cantidad) {
            if ($cantidad < 1) {
                $this->response->error("La cantidad de cada equipo debe ser mayor que cero", 4017, 400);
            }
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
            if (!$id_estado_proceso) {
                throw new Exception("Falta el estado 'en proceso' en la base de datos");
            }

            $ids = array_keys($pedido);
            $marcas = implode(',', array_fill(0, count($ids), '?'));
            $query = "SELECT e.id_equipo, e.codigo_equipo, e.nombre_equipo, e.activo, e.cantidad_existencias, LOWER(s.nombre_estado_equipo) AS estado, COALESCE((SELECT SUM(dp.cantidad_prestada) FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo WHERE dp.id_equipo = e.id_equipo AND p.id_estado_prestamo = ?), 0) AS prestados FROM equipo e INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo WHERE e.id_equipo IN ($marcas) FOR UPDATE";
            $statement = $this->connection->prepare($query);
            $statement->execute(array_merge([$id_estado_proceso], $ids));
            $lista = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($lista) != count($ids)) {
                $this->connection->rollBack();
                $this->response->error("Uno de los equipos seleccionados no existe", 4014, 400);
            }
            foreach ($lista as $equipo) {
                if ($equipo['activo'] != 1 || $equipo['estado'] != 'existente') {
                    $this->connection->rollBack();
                    $this->response->error("El equipo " . $equipo['codigo_equipo'] . " no está disponible", 4015, 400);
                }
                $disponibles = (int) $equipo['cantidad_existencias'] - (int) $equipo['prestados'];
                if ($pedido[(int) $equipo['id_equipo']] > $disponibles) {
                    $this->connection->rollBack();
                    $this->response->error("Solo hay " . $disponibles . " unidad(es) disponibles de " . $equipo['nombre_equipo'], 4016, 400);
                }
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

            foreach ($pedido as $id_equipo => $cantidad) {
                $query = "INSERT INTO detalle_prestamo (id_prestamo, id_equipo, cantidad_prestada) VALUES (:id_prestamo, :id_equipo, :cantidad)";
                $statement = $this->connection->prepare($query);
                $statement->execute(['id_prestamo' => $id_prestamo, 'id_equipo' => $id_equipo, 'cantidad' => $cantidad]);
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
