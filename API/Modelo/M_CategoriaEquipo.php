<?php
class M_CategoriaEquipo
{
    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_CATEGORIAS()
    {
        $consulta = $this->connection->prepare("SELECT id_categoria AS id, nombre_categoria AS nombre, descripcion_categoria AS descripcion FROM categoria_equipo ORDER BY nombre_categoria");
        $consulta->execute();
        $this->response->success("Categorías obtenidas correctamente", $consulta->fetchAll(PDO::FETCH_ASSOC), 1);
    }

    function INSERT_CATEGORIA($datos)
    {
        $consulta = $this->connection->prepare("INSERT INTO categoria_equipo (nombre_categoria, descripcion_categoria) VALUES (:nombre, :descripcion)");
        $consulta->execute(['nombre' => trim($datos['nombre'] ?? ''), 'descripcion' => trim($datos['descripcion'] ?? '')]);
        $this->response->success("Categoría agregada correctamente", [], 1);
    }

    function UPDATE_CATEGORIA($datos)
    {
        $consulta = $this->connection->prepare("UPDATE categoria_equipo SET nombre_categoria = :nombre, descripcion_categoria = :descripcion WHERE id_categoria = :id");
        $consulta->execute(['id' => $datos['id'] ?? 0, 'nombre' => trim($datos['nombre'] ?? ''), 'descripcion' => trim($datos['descripcion'] ?? '')]);
        $this->response->success("Categoría actualizada correctamente", [], 1);
    }

    function DELETE_CATEGORIA($id)
    {
        $consulta = $this->connection->prepare("DELETE FROM categoria_equipo WHERE id_categoria = :id");
        $consulta->execute(['id' => $id]);
        $this->response->success("Categoría eliminada correctamente", [], 1);
    }
}
