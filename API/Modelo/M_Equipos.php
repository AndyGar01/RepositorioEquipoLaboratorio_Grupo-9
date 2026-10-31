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
        $query = "SELECT id_equipo as id, id_categoria, id_estado_equipo as id_estado, codigo_equipo as codigo, nombre_equipo as nombre, descripcion_equipo as descripcion, numero_serie, fecha_adquisicion, imagen_url FROM equipo ORDER BY id_equipo DESC";
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
        $imagen_url = $this->vacioANull($arrayData['imagen_url'] ?? null);

        $query = "INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, imagen_url) VALUES (:id_categoria, :id_estado, :codigo, :nombre, :descripcion, :numero_serie, :fecha_adquisicion, :imagen_url)";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id_categoria' => $id_categoria,
            'id_estado' => $id_estado_equipo,
            'codigo' => $codigo_equipo,
            'nombre' => $nombre_equipo,
            'descripcion' => $descripcion_equipo,
            'numero_serie' => $numero_serie,
            'fecha_adquisicion' => $fecha_adquisicion,
            'imagen_url' => $imagen_url
        ]);
        $this->response->success("Equipo agregado correctamente", [], 1);
    }

    function UPDATE_EQUIPO($arrayData)
    {
        $id_equipo = $arrayData['id'];
        $id_categoria = $arrayData['id_categoria'];
        $id_estado_equipo = $arrayData['id_estado'];
        $codigo_equipo = $arrayData['codigo'];
        $nombre_equipo = $arrayData['nombre'];
        $descripcion_equipo = $arrayData['descripcion'];
        $numero_serie = $this->vacioANull($arrayData['numero_serie'] ?? null);
        $fecha_adquisicion = $this->vacioANull($arrayData['fecha_adquisicion'] ?? null);
        $imagen_url = $this->vacioANull($arrayData['imagen_url'] ?? null);

        $query = "UPDATE equipo SET id_categoria = :id_categoria, id_estado_equipo = :id_estado, codigo_equipo = :codigo, nombre_equipo = :nombre, descripcion_equipo = :descripcion, numero_serie = :numero_serie, fecha_adquisicion = :fecha_adquisicion, imagen_url = :imagen_url WHERE id_equipo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id' => $id_equipo,
            'id_categoria' => $id_categoria,
            'id_estado' => $id_estado_equipo,
            'codigo' => $codigo_equipo,
            'nombre' => $nombre_equipo,
            'descripcion' => $descripcion_equipo,
            'numero_serie' => $numero_serie,
            'fecha_adquisicion' => $fecha_adquisicion,
            'imagen_url' => $imagen_url
        ]);
        $this->response->success("Equipo actualizado correctamente", [], 1);
    }

    function DELETE_EQUIPO($id_equipo)
    {
        $query = "DELETE FROM equipo WHERE id_equipo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_equipo]);
        $this->response->success("Equipo eliminado correctamente", [], 1);
    }
}