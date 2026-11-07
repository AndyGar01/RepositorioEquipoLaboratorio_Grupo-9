<?php

class M_Equipos
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    private function vacioANull($valor)
    {
        $valor = trim((string) $valor);
        return $valor === '' ? null : $valor;
    }

    function SELECT_EQUIPOS()
    {
        $query = "SELECT e.id_equipo AS id, e.id_categoria, e.id_estado_equipo AS id_estado, c.nombre_categoria AS categoria, s.nombre_estado_equipo AS estado, e.codigo_equipo AS codigo, e.nombre_equipo AS nombre, e.descripcion_equipo AS descripcion, e.numero_serie, e.fecha_adquisicion, e.cantidad_existencias, e.imagen_url, e.activo, CASE WHEN e.activo = 1 AND LOWER(s.nombre_estado_equipo) = 'existente' AND NOT EXISTS (SELECT 1 FROM detalle_prestamo dp INNER JOIN prestamo p ON p.id_prestamo = dp.id_prestamo INNER JOIN estado_prestamo ep ON ep.id_estado_prestamo = p.id_estado_prestamo WHERE dp.id_equipo = e.id_equipo AND LOWER(ep.nombre_estado_prestamo) = 'en proceso') THEN 1 ELSE 0 END AS disponible FROM equipo e INNER JOIN categoria_equipo c ON c.id_categoria = e.id_categoria INNER JOIN estado_equipo s ON s.id_estado_equipo = e.id_estado_equipo WHERE e.activo = 1 ORDER BY e.id_equipo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Equipos obtenidos correctamente", $result, 1);
    }

    function INSERT_EQUIPO($arrayData)
    {
        $id_categoria = $arrayData['id_categoria'];
        $id_estado_equipo = $arrayData['id_estado'];
        $codigo_equipo = $arrayData['codigo'];
        $nombre_equipo = $arrayData['nombre'];
        $descripcion_equipo = $arrayData['descripcion'];
        $numero_serie = $this->vacioANull($arrayData['numero_serie'] ?? null);
        $fecha_adquisicion = $this->vacioANull($arrayData['fecha_adquisicion'] ?? null);
        $cantidad_existencias = filter_var($arrayData['cantidad_existencias'] ?? null, FILTER_VALIDATE_INT);
        if ($cantidad_existencias === false || $cantidad_existencias < 1) {
            throw new InvalidArgumentException("Las existencias deben ser un número entero mayor que cero.");
        }

        $query = "INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, cantidad_existencias, imagen_url) VALUES (:id_categoria, :id_estado, :codigo, :nombre, :descripcion, :numero_serie, :fecha_adquisicion, :cantidad_existencias, :imagen_url)";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id_categoria' => $id_categoria,
            'id_estado' => $id_estado_equipo,
            'codigo' => $codigo_equipo,
            'nombre' => $nombre_equipo,
            'descripcion' => $descripcion_equipo,
            'numero_serie' => $numero_serie,
            'fecha_adquisicion' => $fecha_adquisicion,
            'cantidad_existencias' => $cantidad_existencias,
            'imagen_url' => $arrayData['_imagen_url'] ?? null
        ]);
    }

    function UPDATE_EQUIPO($arrayData)
    {
        $id_equipo = $arrayData['id'];
        $consulta = $this->connection->prepare("SELECT activo FROM equipo WHERE id_equipo = :id");
        $consulta->execute(['id' => $id_equipo]);
        if ((int) $consulta->fetchColumn() !== 1) {
            throw new InvalidArgumentException("No se puede modificar un equipo dado de baja.");
        }

        $id_categoria = $arrayData['id_categoria'];
        $id_estado_equipo = $arrayData['id_estado'];
        $codigo_equipo = $arrayData['codigo'];
        $nombre_equipo = $arrayData['nombre'];
        $descripcion_equipo = $arrayData['descripcion'];
        $numero_serie = $this->vacioANull($arrayData['numero_serie'] ?? null);
        $fecha_adquisicion = $this->vacioANull($arrayData['fecha_adquisicion'] ?? null);
        $cantidad_existencias = filter_var($arrayData['cantidad_existencias'] ?? null, FILTER_VALIDATE_INT);
        if ($cantidad_existencias === false || $cantidad_existencias < 1) {
            throw new InvalidArgumentException("Las existencias deben ser un número entero mayor que cero.");
        }

        $query = "UPDATE equipo SET id_categoria = :id_categoria, id_estado_equipo = :id_estado, codigo_equipo = :codigo, nombre_equipo = :nombre, descripcion_equipo = :descripcion, numero_serie = :numero_serie, fecha_adquisicion = :fecha_adquisicion, cantidad_existencias = :cantidad_existencias";
        if (array_key_exists('_imagen_url', $arrayData)) {
            $query .= ", imagen_url = :imagen_url";
        }
        $query .= " WHERE id_equipo = :id";
        $statement = $this->connection->prepare($query);
        $parametros = [
            'id' => $id_equipo,
            'id_categoria' => $id_categoria,
            'id_estado' => $id_estado_equipo,
            'codigo' => $codigo_equipo,
            'nombre' => $nombre_equipo,
            'descripcion' => $descripcion_equipo,
            'numero_serie' => $numero_serie,
            'fecha_adquisicion' => $fecha_adquisicion,
            'cantidad_existencias' => $cantidad_existencias
        ];
        if (array_key_exists('_imagen_url', $arrayData)) {
            $parametros['imagen_url'] = $arrayData['_imagen_url'];
        }
        $statement->execute($parametros);
    }

    function DELETE_EQUIPO($id_equipo)
    {
        $query = "UPDATE equipo SET activo = 0 WHERE id_equipo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_equipo]);
        $this->response->success("Equipo dado de baja correctamente", [], 1);
    }
}
