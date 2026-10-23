<?php

class M_Perfiles
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_PERFILES()
    {
        $query = "SELECT id_perfil as id, nombre_perfil as nombre, descripcion_perfil as descripcion, activo_perfil as estado FROM perfil ORDER BY id_perfil DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Perfiles obtenidos correctamente", $result, 1);
    }

    function INSERT_PERFIL($arrayData)
    {
        $nombre_perfil = $arrayData['nombre'];
        $descripcion_perfil = $arrayData['descripcion'];
        $activo_perfil = $arrayData['estado'];

        $query = "INSERT INTO perfil (nombre_perfil, descripcion_perfil, activo_perfil) VALUES (:nombre, :descripcion, :estado)";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'nombre' => $nombre_perfil,
            'descripcion' => $descripcion_perfil,
            'estado' => $activo_perfil
        ]);
        $this->response->success("Perfil agregado correctamente", [], 1);
    }

    function UPDATE_PERFIL($arrayData)
    {
        $id_perfil = $arrayData['id'];
        $nombre_perfil = $arrayData['nombre'];
        $descripcion_perfil = $arrayData['descripcion'];
        $activo_perfil = $arrayData['estado'];

        $query = "UPDATE perfil SET nombre_perfil = :nombre, descripcion_perfil = :descripcion, activo_perfil = :estado WHERE id_perfil = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id' => $id_perfil,
            'nombre' => $nombre_perfil,
            'descripcion' => $descripcion_perfil,
            'estado' => $activo_perfil
        ]);
        $this->response->success("Perfil actualizado correctamente", [], 1);
    }

    function DELETE_PERFIL($id_perfil)
    {
        $query = "DELETE FROM perfil WHERE id_perfil = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id_perfil]);
        $this->response->success("Perfil eliminado correctamente", [], 1);
    }
}