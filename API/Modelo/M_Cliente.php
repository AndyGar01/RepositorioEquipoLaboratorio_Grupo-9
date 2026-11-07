<?php

class M_Cliente
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_CATALOGO()
    {
        $query = "SELECT e.id_equipo AS id, e.codigo_equipo AS codigo, e.nombre_equipo AS nombre,
                         e.descripcion_equipo AS descripcion, c.nombre_categoria AS categoria,
                         e.imagen_url,
                         e.cantidad_existencias - COALESCE((SELECT SUM(dp.cantidad_prestada)
                             FROM detalle_prestamo dp
                             INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo
                             INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                             WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso'), 0) AS disponibles
                  FROM equipo e
                  INNER JOIN categoria_equipo c ON c.id_categoria = e.id_categoria
                  INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo
                  WHERE e.activo = 1 AND LOWER(s.nombre_estado_equipo) = 'existente'
                  ORDER BY c.nombre_categoria, e.nombre_equipo";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $equipos = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($equipos as &$equipo) {
            $equipo['disponibles'] = max(0, (int) $equipo['disponibles']);
            if (empty($equipo['imagen_url'])) {
                $equipo['imagen_url'] = $this->imagenCategoria($equipo['categoria']);
            }
        }
        unset($equipo);
        $this->response->success("Catálogo obtenido correctamente", $equipos, 1);
    }

    private function imagenCategoria($categoria)
    {
        if (stripos($categoria, 'medición') !== false || stripos($categoria, 'pesaje') !== false) return 'images/equipos/probeta.svg';
        if (stripos($categoria, 'análisis') !== false) return 'images/equipos/mechero.svg';
        if (stripos($categoria, 'preparación') !== false) return 'images/equipos/matraz.svg';
        if (stripos($categoria, 'térmico') !== false) return 'images/equipos/termometro.svg';
        return 'images/equipos/gradilla.svg';
    }

    private function armarPedido($lineas)
    {
        if (is_string($lineas)) {
            $lineas = explode(',', $lineas);
        }
        if (!is_array($lineas)) {
            return [];
        }
        $pedido = [];
        foreach ($lineas as $linea) {
            $partes = explode(':', (string) $linea);
            $id = filter_var($partes[0] ?? null, FILTER_VALIDATE_INT);
            $cantidad = filter_var($partes[1] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1 || $cantidad === false || $cantidad < 1) {
                throw new InvalidArgumentException("Revisa los equipos y las cantidades de la solicitud.");
            }
            $pedido[$id] = ($pedido[$id] ?? 0) + $cantidad;
        }
        if (count($pedido) < 1 || count($pedido) > 10) {
            throw new InvalidArgumentException("Selecciona de 1 a 10 tipos de equipo.");
        }
        return $pedido;
    }

    private function validarFecha($valor)
    {
        $hoy = new DateTime('today');
        $fecha = DateTime::createFromFormat('!Y-m-d', (string) $valor);
        if (!$fecha || $fecha < $hoy || $fecha > (clone $hoy)->modify('+30 days')) {
            throw new InvalidArgumentException("La devolución debe programarse entre hoy y los próximos 30 días.");
        }
        return $fecha->format('Y-m-d');
    }

    private function disponiblesParaPedido($pedido)
    {
        $ids = array_keys($pedido);
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT e.id_equipo, e.codigo_equipo, e.nombre_equipo, e.activo,
                         e.cantidad_existencias, LOWER(s.nombre_estado_equipo) AS estado,
                         COALESCE((SELECT SUM(dp.cantidad_prestada)
                             FROM detalle_prestamo dp
                             INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo
                             INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo
                             WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso'), 0) AS prestados
                  FROM equipo e
                  INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo
                  WHERE e.id_equipo IN ($marcas) FOR UPDATE";
        $statement = $this->connection->prepare($query);
        $statement->execute($ids);
        $lista = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (count($lista) !== count($pedido)) {
            throw new InvalidArgumentException("Uno de los equipos ya no está disponible.");
        }
        foreach ($lista as $equipo) {
            $disponibles = (int) $equipo['cantidad_existencias'] - (int) $equipo['prestados'];
            if ((int) $equipo['activo'] !== 1 || $equipo['estado'] !== 'existente' || $pedido[(int) $equipo['id_equipo']] > $disponibles) {
                throw new InvalidArgumentException("La disponibilidad cambió para " . $equipo['nombre_equipo'] . ". Actualiza el catálogo e inténtalo de nuevo.");
            }
        }
    }

    function CREAR_SOLICITUD($datos, $idAlumno)
    {
        $pedido = $this->armarPedido($datos['equipos'] ?? []);
        $fecha = $this->validarFecha($datos['fecha_devolucion_esperada'] ?? '');
        $this->connection->beginTransaction();
        try {
            $this->disponiblesParaPedido($pedido);
            $estado = $this->connection->prepare("SELECT id_estado_solicitud FROM estado_solicitud_prestamo WHERE nombre_estado_solicitud = 'Pendiente' LIMIT 1");
            $estado->execute();
            $idEstado = $estado->fetchColumn();
            if (!$idEstado) {
                throw new RuntimeException("No está configurado el estado pendiente.");
            }

            $insertar = $this->connection->prepare("INSERT INTO solicitud_prestamo (id_alumno, id_estado_solicitud, fecha_devolucion_esperada) VALUES (:alumno, :estado, :fecha)");
            $insertar->execute(['alumno' => $idAlumno, 'estado' => $idEstado, 'fecha' => $fecha]);
            $idSolicitud = (int) $this->connection->lastInsertId();
            $detalle = $this->connection->prepare("INSERT INTO detalle_solicitud_prestamo (id_solicitud, id_equipo, cantidad_solicitada) VALUES (:solicitud, :equipo, :cantidad)");
            foreach ($pedido as $idEquipo => $cantidad) {
                $detalle->execute(['solicitud' => $idSolicitud, 'equipo' => $idEquipo, 'cantidad' => $cantidad]);
            }
            $this->connection->commit();
            $this->response->success("Solicitud enviada. El encargado debe aprobarla antes de retirar el equipo.", ['id' => $idSolicitud], 1);
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            throw $e;
        }
    }

    function SELECT_MIS_SOLICITUDES($idAlumno)
    {
        $query = "SELECT sp.id_solicitud AS id, sp.fecha_solicitud, sp.fecha_devolucion_esperada,
                         es.nombre_estado_solicitud AS estado, sp.id_prestamo,
                         (SELECT GROUP_CONCAT(CONCAT(e.nombre_equipo, ' x', dsp.cantidad_solicitada) ORDER BY e.nombre_equipo SEPARATOR ', ')
                          FROM detalle_solicitud_prestamo dsp
                          INNER JOIN equipo e ON e.id_equipo = dsp.id_equipo
                          WHERE dsp.id_solicitud = sp.id_solicitud) AS equipos
                  FROM solicitud_prestamo sp
                  INNER JOIN estado_solicitud_prestamo es ON es.id_estado_solicitud = sp.id_estado_solicitud
                  WHERE sp.id_alumno = :alumno
                  ORDER BY sp.fecha_solicitud DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute(['alumno' => $idAlumno]);
        $this->response->success("Solicitudes obtenidas correctamente", $statement->fetchAll(PDO::FETCH_ASSOC), 1);
    }
}
