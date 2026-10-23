<?php

class Permisos{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function verificar($id_perfil, $id_modulo, $permiso)
    {
        $columnas = [
            'POST' => 'permiso_ingresar',
            'PUT' => 'permiso_actualizar',
            'DELETE' => 'permiso_eliminar',
            'GET' => 'permiso_consultar'
        ];

        if (!isset($columnas[$permiso])) {
            return false;
        }

        $columna = $columnas[$permiso];
        $query = "SELECT $columna FROM perfil_modulo WHERE id_perfil = :id_perfil AND id_modulo = :id_modulo";
        $statement = $this->connection->prepare($query);
        $statement->execute(['id_perfil' => $id_perfil, 'id_modulo' => $id_modulo]);
        return (int) $statement->fetchColumn() === 1;
    }
}