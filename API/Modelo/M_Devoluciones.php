<?php

class M_Devoluciones
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_PRESTAMOS_PENDIENTES()
    {
        $query = "SELECT p.id_prestamo AS id, p.id_alumno, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, p.fecha_prestamo, p.fecha_devolucion_esperada, GROUP_CONCAT(e.codigo_equipo, ' x', dp.cantidad_prestada ORDER BY e.codigo_equipo SEPARATOR ', ') AS equipos FROM prestamo p INNER JOIN alumno a ON a.id_alumno = p.id_alumno INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo LEFT JOIN detalle_prestamo dp ON dp.id_prestamo = p.id_prestamo LEFT JOIN equipo e ON e.id_equipo = dp.id_equipo WHERE LOWER(ep.nombre_estado_prestamo) = 'en proceso' GROUP BY p.id_prestamo ORDER BY p.id_prestamo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Préstamos pendientes obtenidos correctamente", $result, 1);
    }

    function SELECT_DETALLE_PRESTAMO($id_prestamo)
    {
        $query = "SELECT p.id_prestamo AS id, p.id_alumno, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, p.fecha_prestamo, p.fecha_devolucion_esperada, LOWER(ep.nombre_estado_prestamo) AS estado, GREATEST(DATEDIFF(CURDATE(), p.fecha_devolucion_esperada), 0) AS dias_retraso FROM prestamo p INNER JOIN alumno a ON a.id_alumno = p.id_alumno INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE p.id_prestamo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_prestamo]);
        $prestamo = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$prestamo) {
            $this->response->error("El préstamo no existe", 4111, 404);
        }
        if ($prestamo['estado'] != 'en proceso') {
            $this->response->error("El préstamo ya no está en proceso", 4112, 400);
        }

        $query = "SELECT dp.id_detalle, dp.id_equipo, e.codigo_equipo AS codigo, e.nombre_equipo AS nombre, dp.cantidad_prestada FROM detalle_prestamo dp INNER JOIN equipo e ON e.id_equipo = dp.id_equipo WHERE dp.id_prestamo = :id ORDER BY e.codigo_equipo";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_prestamo]);
        $equipos = $statement->fetchAll(PDO::FETCH_ASSOC);

        $tarifas = $this->SELECT_TARIFAS();
        $this->response->success("Préstamo obtenido correctamente", [
            'prestamo' => $prestamo,
            'equipos' => $equipos,
            'tarifa_mora' => $tarifas['mora'],
            'tarifa_dano' => $tarifas['dano']
        ], 1);
    }

    private function SELECT_TARIFAS()
    {
        $query = "SELECT LOWER(concepto_tarifa) AS concepto, monto_tarifa FROM tarifa_cobro WHERE activo_tarifa = 1";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $filas = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!isset($filas['mora'], $filas['daño'])) {
            $this->response->error("Faltan las tarifas de cobro en la base de datos", 4115, 500);
        }
        return ['mora' => (float) $filas['mora'], 'dano' => (float) $filas['daño']];
    }

    function INSERT_DEVOLUCION($arrayData, $id_usuario)
    {
        $id_prestamo = (int) ($arrayData['id_prestamo'] ?? 0);
        $observaciones = trim($arrayData['observaciones'] ?? '');
        $lineas = $arrayData['equipos'] ?? [];

        if (!is_array($lineas)) {
            $lineas = explode(',', $lineas);
        }
        $revision = [];
        foreach ($lineas as $linea) {
            $partes = explode(':', $linea);
            $id_detalle = (int) $partes[0];
            if ($id_detalle > 0) {
                $revision[$id_detalle] = [
                    'afectada' => (int) ($partes[1] ?? 0),
                    'condicion' => (int) ($partes[2] ?? 0)
                ];
            }
        }

        if ($id_prestamo < 1 || count($revision) == 0) {
            $this->response->error("El préstamo y la revisión de los equipos son obligatorios", 4110, 400);
        }
        if ($observaciones === '') {
            $observaciones = 'Sin observaciones';
        }
        $observaciones = mb_substr($observaciones, 0, 75);

        $this->connection->beginTransaction();
        try {
            $query = "SELECT p.id_alumno, LOWER(ep.nombre_estado_prestamo) AS estado, DATEDIFF(CURDATE(), p.fecha_devolucion_esperada) AS dias FROM prestamo p INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE p.id_prestamo = :id FOR UPDATE";
            $statement = $this->connection->prepare($query);
            $statement->execute(['id' => $id_prestamo]);
            $prestamo = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$prestamo) {
                $this->connection->rollBack();
                $this->response->error("El préstamo no existe", 4111, 404);
            }
            if ($prestamo['estado'] != 'en proceso') {
                $this->connection->rollBack();
                $this->response->error("El préstamo ya no está en proceso", 4112, 400);
            }

            $query = "SELECT id_detalle, cantidad_prestada FROM detalle_prestamo WHERE id_prestamo = :id";
            $statement = $this->connection->prepare($query);
            $statement->execute(['id' => $id_prestamo]);
            $detalles = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
            if (count($detalles) != count($revision) || array_diff_key($detalles, $revision)) {
                $this->connection->rollBack();
                $this->response->error("La devolución debe incluir todos los equipos del préstamo", 4113, 400);
            }

            $query = "SELECT id_estado_condicion, LOWER(nombre_estado_condicion_devolucion) AS nombre FROM estado_condicion_devolucion";
            $statement = $this->connection->prepare($query);
            $statement->execute();
            $condiciones = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
            $id_intacto = array_search('intacto', $condiciones);
            if ($id_intacto === false) {
                throw new Exception("Falta la condición 'Intacto' en la base de datos");
            }

            $total_afectadas = 0;
            foreach ($revision as $id_detalle => $datos) {
                $afectada = $datos['afectada'];
                if ($afectada < 0 || $afectada > (int) $detalles[$id_detalle]) {
                    $this->connection->rollBack();
                    $this->response->error("La cantidad dañada o incompleta no puede superar la cantidad prestada", 4114, 400);
                }
                if ($afectada > 0) {
                    $nombre = $condiciones[$datos['condicion']] ?? '';
                    if ($nombre != 'dañado' && $nombre != 'incompleto') {
                        $this->connection->rollBack();
                        $this->response->error("Selecciona si el equipo está dañado o incompleto", 4116, 400);
                    }
                    $total_afectadas += $afectada;
                } else {
                    $revision[$id_detalle]['condicion'] = $id_intacto;
                }
            }

            $tarifas = $this->SELECT_TARIFAS();
            $dias_retraso = max((int) $prestamo['dias'], 0);
            $total_mora = $dias_retraso > 0 ? $tarifas['mora'] * count($detalles) : 0;
            $total_dano = $tarifas['dano'] * $total_afectadas;
            $total = $total_mora + $total_dano;

            $query = "INSERT INTO devolucion (id_prestamo, id_usuario, id_alumno, observaciones_devolucion) VALUES (:id_prestamo, :id_usuario, :id_alumno, :observaciones)";
            $statement = $this->connection->prepare($query);
            $statement->execute([
                'id_prestamo' => $id_prestamo,
                'id_usuario' => $id_usuario,
                'id_alumno' => $prestamo['id_alumno'],
                'observaciones' => $observaciones
            ]);
            $id_devolucion = $this->connection->lastInsertId();

            foreach ($revision as $id_detalle => $datos) {
                $query = "INSERT INTO detalle_devolucion (id_devolucion, id_detalle_prestamo, id_estado_condicion, cantidad_devuelta, cantidad_afectada) VALUES (:id_devolucion, :id_detalle, :condicion, :devuelta, :afectada)";
                $statement = $this->connection->prepare($query);
                $statement->execute([
                    'id_devolucion' => $id_devolucion,
                    'id_detalle' => $id_detalle,
                    'condicion' => $datos['condicion'],
                    'devuelta' => $detalles[$id_detalle],
                    'afectada' => $datos['afectada']
                ]);
            }

            if ($total > 0) {
                $query = "INSERT INTO mora (id_prestamo, dias_retraso_mora, total_mora) VALUES (:id_prestamo, :dias, :total)";
                $statement = $this->connection->prepare($query);
                $statement->execute(['id_prestamo' => $id_prestamo, 'dias' => $dias_retraso, 'total' => $total]);
            }

            $nuevo_estado = $total > 0 ? 'no pagó' : 'completo';
            $query = "UPDATE prestamo SET id_estado_prestamo = (SELECT id_estado_prestamo FROM estado_prestamo WHERE LOWER(nombre_estado_prestamo) = :estado) WHERE id_prestamo = :id";
            $statement = $this->connection->prepare($query);
            $statement->execute(['estado' => $nuevo_estado, 'id' => $id_prestamo]);

            $this->connection->commit();
        } catch (\Throwable $th) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $th;
        }
        $this->response->success("Devolución registrada correctamente", [
            'id_devolucion' => $id_devolucion,
            'total_mora' => $total_mora,
            'total_dano' => $total_dano,
            'total_cobro' => $total
        ], 1);
    }
}
