<?php

class M_Dashboard
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    private function consultar($query, $parametros = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($parametros);
        return $statement;
    }

    private function inicioPeriodo($periodo)
    {
        if ($periodo == '30') {
            return new DateTime('-29 days midnight');
        }
        if ($periodo == '180') {
            return new DateTime('first day of -5 months midnight');
        }
        if ($periodo == '365') {
            return new DateTime('first day of -11 months midnight');
        }
        return null;
    }

    private function filtro($columna, $desde)
    {
        return $desde ? " AND $columna >= :desde" : "";
    }

    private function parametros($desde)
    {
        return $desde ? ['desde' => $desde] : [];
    }

    function SELECT_RESUMEN($periodo)
    {
        $inicio = $this->inicioPeriodo($periodo);
        $desde = $inicio ? $inicio->format('Y-m-d H:i:s') : null;
        $inventario = $this->INVENTARIO();

        $resumen = [
            'periodo' => $periodo,
            'agrupacion' => $periodo == '30' ? 'dia' : 'mes',
            'equipos' => $inventario['equipos'],
            'categorias' => $inventario['categorias'],
            'prestamos' => $this->PRESTAMOS_ACTUALES(),
            'estados' => $this->ESTADOS_PRESTAMO($desde),
            'serie' => $this->SERIE($periodo, $inicio),
            'mas_prestados' => $this->MAS_PRESTADOS($desde),
            'vencimientos' => $this->VENCIMIENTOS(),
            'solicitudes' => $this->SOLICITUDES($desde),
            'pendientes' => $this->SOLICITUDES_PENDIENTES(),
            'cobros' => $this->COBROS($desde),
            'devoluciones' => $this->DEVOLUCIONES($desde),
            'alumnos' => $this->ALUMNOS()
        ];
        $this->response->success("Resumen obtenido correctamente", $resumen, 1);
    }

    private function INVENTARIO()
    {
        $query = "SELECT c.nombre_categoria AS categoria, LOWER(s.nombre_estado_equipo) AS estado, e.cantidad_existencias AS existencias, COALESCE((SELECT SUM(dp.cantidad_prestada) FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso'), 0) AS prestadas, COALESCE((SELECT SUM(dd.cantidad_afectada - dd.cantidad_reparada) FROM detalle_devolucion dd INNER JOIN detalle_prestamo dpr ON dpr.id_detalle = dd.id_detalle_prestamo WHERE dpr.id_equipo = e.id_equipo), 0) AS reparacion FROM equipo e INNER JOIN categoria_equipo c ON c.id_categoria = e.id_categoria INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo WHERE e.activo = 1 ORDER BY c.nombre_categoria";
        $lista = $this->consultar($query)->fetchAll(PDO::FETCH_ASSOC);

        $equipos = ['tipos' => 0, 'unidades' => 0, 'disponibles' => 0, 'prestadas' => 0, 'reparacion' => 0, 'fuera' => 0, 'agotados' => 0];
        $categorias = [];
        foreach ($lista as $equipo) {
            $existencias = (int) $equipo['existencias'];
            $prestadas = min((int) $equipo['prestadas'], $existencias);
            $reparacion = min((int) $equipo['reparacion'], $existencias - $prestadas);
            $libres = $existencias - $prestadas - $reparacion;
            $disponibles = $equipo['estado'] == 'existente' ? $libres : 0;
            $fuera = $libres - $disponibles;

            $equipos['tipos']++;
            $equipos['unidades'] += $existencias;
            $equipos['disponibles'] += $disponibles;
            $equipos['prestadas'] += $prestadas;
            $equipos['reparacion'] += $reparacion;
            $equipos['fuera'] += $fuera;
            if ($equipo['estado'] == 'existente' && $disponibles < 1) {
                $equipos['agotados']++;
            }

            $nombre = $equipo['categoria'];
            if (!isset($categorias[$nombre])) {
                $categorias[$nombre] = ['nombre' => $nombre, 'disponibles' => 0, 'prestadas' => 0, 'reparacion' => 0, 'fuera' => 0];
            }
            $categorias[$nombre]['disponibles'] += $disponibles;
            $categorias[$nombre]['prestadas'] += $prestadas;
            $categorias[$nombre]['reparacion'] += $reparacion;
            $categorias[$nombre]['fuera'] += $fuera;
        }
        return ['equipos' => $equipos, 'categorias' => array_values($categorias)];
    }

    private function PRESTAMOS_ACTUALES()
    {
        $query = "SELECT COUNT(*) AS en_proceso, COALESCE(SUM(p.fecha_devolucion_esperada < CURDATE()), 0) AS vencidos, COALESCE(SUM(p.fecha_devolucion_esperada >= CURDATE() AND p.fecha_devolucion_esperada <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)), 0) AS por_vencer FROM prestamo p INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE LOWER(ep.nombre_estado_prestamo) = 'en proceso'";
        $fila = $this->consultar($query)->fetch(PDO::FETCH_ASSOC);
        return [
            'en_proceso' => (int) $fila['en_proceso'],
            'vencidos' => (int) $fila['vencidos'],
            'por_vencer' => (int) $fila['por_vencer']
        ];
    }

    private function ESTADOS_PRESTAMO($desde)
    {
        $query = "SELECT ep.nombre_estado_prestamo AS estado, COUNT(p.id_prestamo) AS total FROM estado_prestamo ep LEFT JOIN prestamo p ON p.id_estado_prestamo = ep.id_estado_prestamo" . $this->filtro('p.fecha_prestamo', $desde) . " GROUP BY ep.id_estado_prestamo, ep.nombre_estado_prestamo ORDER BY ep.id_estado_prestamo";
        $lista = $this->consultar($query, $this->parametros($desde))->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lista as $i => $fila) {
            $lista[$i]['total'] = (int) $fila['total'];
        }
        return $lista;
    }

    private function SERIE($periodo, $inicio)
    {
        $porDia = $periodo == '30';
        $inicioSerie = $inicio ?? new DateTime('first day of -11 months midnight');
        $formatoSql = $porDia ? '%Y-%m-%d' : '%Y-%m';
        $formatoPhp = $porDia ? 'Y-m-d' : 'Y-m';
        $cantidad = $porDia ? 30 : ($periodo == '180' ? 6 : 12);
        $parametros = ['desde' => $inicioSerie->format('Y-m-d H:i:s')];

        $query = "SELECT DATE_FORMAT(fecha_prestamo, '$formatoSql') AS clave, COUNT(*) AS total FROM prestamo WHERE fecha_prestamo >= :desde GROUP BY clave";
        $prestamos = $this->consultar($query, $parametros)->fetchAll(PDO::FETCH_KEY_PAIR);
        $query = "SELECT DATE_FORMAT(fecha_devolucion, '$formatoSql') AS clave, COUNT(*) AS total FROM devolucion WHERE fecha_devolucion >= :desde GROUP BY clave";
        $devoluciones = $this->consultar($query, $parametros)->fetchAll(PDO::FETCH_KEY_PAIR);

        $serie = [];
        $fecha = clone $inicioSerie;
        for ($i = 0; $i < $cantidad; $i++) {
            $clave = $fecha->format($formatoPhp);
            $serie[] = [
                'clave' => $clave,
                'prestamos' => (int) ($prestamos[$clave] ?? 0),
                'devoluciones' => (int) ($devoluciones[$clave] ?? 0)
            ];
            $fecha->modify($porDia ? '+1 day' : '+1 month');
        }
        return $serie;
    }

    private function MAS_PRESTADOS($desde)
    {
        $query = "SELECT e.codigo_equipo AS codigo, e.nombre_equipo AS nombre, SUM(dp.cantidad_prestada) AS total FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo INNER JOIN equipo e ON e.id_equipo = dp.id_equipo WHERE 1 = 1" . $this->filtro('p.fecha_prestamo', $desde) . " GROUP BY e.id_equipo, e.codigo_equipo, e.nombre_equipo ORDER BY total DESC, e.nombre_equipo LIMIT 5";
        $lista = $this->consultar($query, $this->parametros($desde))->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lista as $i => $fila) {
            $lista[$i]['total'] = (int) $fila['total'];
        }
        return $lista;
    }

    private function VENCIMIENTOS()
    {
        $query = "SELECT p.id_prestamo AS id, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, p.fecha_devolucion_esperada AS fecha_esperada, DATEDIFF(CURDATE(), p.fecha_devolucion_esperada) AS dias, GROUP_CONCAT(e.codigo_equipo, ' x', dp.cantidad_prestada ORDER BY e.codigo_equipo SEPARATOR ', ') AS equipos FROM prestamo p INNER JOIN alumno a ON a.id_alumno = p.id_alumno INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo LEFT JOIN detalle_prestamo dp ON dp.id_prestamo = p.id_prestamo LEFT JOIN equipo e ON e.id_equipo = dp.id_equipo WHERE LOWER(ep.nombre_estado_prestamo) = 'en proceso' AND p.fecha_devolucion_esperada <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) GROUP BY p.id_prestamo, a.nombre_alumno, a.apellido_alumno, p.fecha_devolucion_esperada ORDER BY p.fecha_devolucion_esperada ASC, p.id_prestamo ASC LIMIT 8";
        $lista = $this->consultar($query)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lista as $i => $fila) {
            $lista[$i]['dias'] = (int) $fila['dias'];
        }
        return $lista;
    }

    private function SOLICITUDES($desde)
    {
        $query = "SELECT es.nombre_estado_solicitud AS estado, COUNT(sp.id_solicitud) AS total FROM estado_solicitud_prestamo es LEFT JOIN solicitud_prestamo sp ON sp.id_estado_solicitud = es.id_estado_solicitud" . $this->filtro('sp.fecha_solicitud', $desde) . " GROUP BY es.id_estado_solicitud, es.nombre_estado_solicitud";
        $estados = $this->consultar($query, $this->parametros($desde))->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'aprobadas' => (int) ($estados['Aprobada'] ?? 0),
            'rechazadas' => (int) ($estados['Rechazada'] ?? 0)
        ];
    }

    private function SOLICITUDES_PENDIENTES()
    {
        $query = "SELECT COUNT(*) FROM solicitud_prestamo sp INNER JOIN estado_solicitud_prestamo es ON es.id_estado_solicitud = sp.id_estado_solicitud WHERE es.nombre_estado_solicitud = 'Pendiente'";
        $total = (int) $this->consultar($query)->fetchColumn();

        $query = "SELECT sp.id_solicitud AS id, CONCAT(a.nombre_alumno, ' ', a.apellido_alumno) AS alumno, sp.fecha_solicitud AS fecha, (SELECT GROUP_CONCAT(CONCAT(e.nombre_equipo, ' x', d.cantidad_solicitada) ORDER BY e.nombre_equipo SEPARATOR ', ') FROM detalle_solicitud_prestamo d INNER JOIN equipo e ON e.id_equipo = d.id_equipo WHERE d.id_solicitud = sp.id_solicitud) AS equipos FROM solicitud_prestamo sp INNER JOIN alumno a ON a.id_alumno = sp.id_alumno INNER JOIN estado_solicitud_prestamo es ON es.id_estado_solicitud = sp.id_estado_solicitud WHERE es.nombre_estado_solicitud = 'Pendiente' ORDER BY sp.fecha_solicitud ASC, sp.id_solicitud ASC LIMIT 8";
        $lista = $this->consultar($query)->fetchAll(PDO::FETCH_ASSOC);
        return ['total' => $total, 'lista' => $lista];
    }

    private function COBROS($desde)
    {
        $query = "SELECT COUNT(*) AS cantidad, COALESCE(SUM(m.total_mora), 0) AS total, COALESCE(SUM(CASE WHEN m.pagado_mora = 0 THEN m.total_mora ELSE 0 END), 0) AS pendiente FROM mora m WHERE 1 = 1" . $this->filtro('m.fecha_calculo_mora', $desde);
        $fila = $this->consultar($query, $this->parametros($desde))->fetch(PDO::FETCH_ASSOC);
        return [
            'cantidad' => (int) $fila['cantidad'],
            'total' => (float) $fila['total'],
            'pendiente' => (float) $fila['pendiente']
        ];
    }

    private function DEVOLUCIONES($desde)
    {
        $query = "SELECT COUNT(DISTINCT d.id_devolucion) AS cantidad, COALESCE(SUM(dd.cantidad_devuelta), 0) AS devueltas, COALESCE(SUM(dd.cantidad_afectada), 0) AS afectadas FROM devolucion d LEFT JOIN detalle_devolucion dd ON dd.id_devolucion = d.id_devolucion WHERE 1 = 1" . $this->filtro('d.fecha_devolucion', $desde);
        $fila = $this->consultar($query, $this->parametros($desde))->fetch(PDO::FETCH_ASSOC);
        return [
            'cantidad' => (int) $fila['cantidad'],
            'devueltas' => (int) $fila['devueltas'],
            'afectadas' => (int) $fila['afectadas']
        ];
    }

    private function ALUMNOS()
    {
        $query = "SELECT (SELECT COUNT(*) FROM alumno WHERE activo_alumno = 1) AS activos, (SELECT COUNT(DISTINCT p.id_alumno) FROM prestamo p INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE LOWER(ep.nombre_estado_prestamo) = 'en proceso') AS con_prestamo";
        $fila = $this->consultar($query)->fetch(PDO::FETCH_ASSOC);
        return [
            'activos' => (int) $fila['activos'],
            'con_prestamo' => (int) $fila['con_prestamo']
        ];
    }
}
