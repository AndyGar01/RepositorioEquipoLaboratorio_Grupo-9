<?php
class M_EstadoEquipo
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_ESTADOS()
    {
        $consulta = $this->connection->prepare("SELECT id_estado_equipo AS id, nombre_estado_equipo AS nombre FROM estado_equipo ORDER BY nombre_estado_equipo");
        $consulta->execute();
        $this->response->success("Estados obtenidos correctamente", $consulta->fetchAll(PDO::FETCH_ASSOC), 1);
    }

    function INSERT_ESTADO($datos)
    {
        $consulta = $this->connection->prepare("INSERT INTO estado_equipo (nombre_estado_equipo) VALUES (:nombre)");
        $consulta->execute(['nombre' => trim($datos['nombre'] ?? '')]);
        $this->response->success("Estado agregado correctamente", [], 1);
    }

    function UPDATE_ESTADO($datos)
    {
        $consulta = $this->connection->prepare("UPDATE estado_equipo SET nombre_estado_equipo = :nombre WHERE id_estado_equipo = :id");
        $consulta->execute(['id' => $datos['id'] ?? 0, 'nombre' => trim($datos['nombre'] ?? '')]);
        $this->response->success("Estado actualizado correctamente", [], 1);
    }

    function DELETE_ESTADO($id)
    {
        $consulta = $this->connection->prepare("DELETE FROM estado_equipo WHERE id_estado_equipo = :id");
        $consulta->execute(['id' => $id]);
        $this->response->success("Estado eliminado correctamente", [], 1);
    }
}
