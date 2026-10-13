<?php

class M_Modulos{

    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response){
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_MODULOS(){
        $query = "SELECT id_modulo as id, nombre_modulo as nombre, descripcion_modulo as descripcion FROM modulo ORDER BY id_modulo DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Módulos obtenidos correctamente", $result, 1);
    }

    function INSERT_MODULOS($arrayData){
        $nombre_modulo = $arrayData['nombre'];
        $descripcion_modulo = $arrayData['descripcion'];
        $query = "INSERT INTO modulo(nombre_modulo, descripcion_modulo) VALUES (:nombre, :descripcion)";
        $statement = $this->connection->prepare($query);
        $statement->execute([ 'nombre' => $nombre_modulo, 'descripcion' => $descripcion_modulo ]);
        $this->response->success("Módulo agregado correctamente", [], 1);
    }

    function UPDATE_MODULOS($arrayData){
        $id_modulo = $arrayData['id'];
        $nombre_modulo = $arrayData['nombre'];
        $descripcion_modulo = $arrayData['descripcion'];
        $query = "UPDATE modulo SET nombre_modulo = :nombre, descripcion_modulo = :descripcion WHERE id_modulo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute([ 'id' => $id_modulo, 'nombre' => $nombre_modulo, 'descripcion' => $descripcion_modulo ]);
        $this->response->success("Módulo actualizado correctamente", [], 1);
    }

    function DELETE_MODULOS($idModulo){
        $query = "DELETE FROM modulo WHERE id_modulo = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute([ 'id' => $idModulo ]);
        $this->response->success("Módulo eliminado correctamente", [], 1);
    }
}