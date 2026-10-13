<?php

class M_PerfilModulo{

    private Connection $connection;
    private Response $response;

    public function __construct(Connection $connection, Response $response){
        $this->connection = $connection;
        $this->response = $response;
    }

    function SELECT_PERFIL_MODULO(){
        $query = "SELECT id_perfil as id_per, id_modulo as id_mod, permiso_ingresar as ingresar, permiso_actualizar as actualizar, permiso_eliminar as eliminar, permiso_consultar as consultar FROM perfil_modulo ORDER BY id_perfil DESC";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->response->success("Permisos obtenidos exitosamente", $result, 1);
    }

    function INSERT_PERFIL_MODULO($arraydata){
        $id_perfil = $arraydata['id_per'];
        $id_modulo = $arraydata['id_mod'];
        $permiso_ingresar = $arraydata['ingresar'];
        $permiso_actualizar = $arraydata['actualizar'];
        $permiso_eliminar = $arraydata['eliminar'];
        $permiso_consultar = $arraydata['consultar'];
        $query = "INSERT INTO perfil_modulo(id_perfil, id_modulo, permiso_ingresar, permiso_actualizar, permiso_eliminar, permiso_consultar) VALUES (:id_per, :id_mod, :ingresar, :actualizar, :eliminar, :consultar) ON DUPLICATE KEY UPDATE permiso_ingresar = VALUES(permiso_ingresar), permiso_actualizar = VALUES(permiso_actualizar), permiso_eliminar = VALUES(permiso_eliminar), permiso_consultar = VALUES(permiso_consultar)";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id_per' => $id_perfil,
            'id_mod' => $id_modulo,
            'ingresar' => $permiso_ingresar,
            'actualizar' => $permiso_actualizar,
            'eliminar' => $permiso_eliminar,
            'consultar' => $permiso_consultar]);
        $this->response->success("Permisos agregados exitosamente", [], 1);
    }

    function UPDATE_PERFIL_MODULO($arraydata){
        $id_perfil = $arraydata['id_per'];
        $id_modulo = $arraydata['id_mod'];
        $permiso_ingresar = $arraydata['ingresar'];
        $permiso_actualizar = $arraydata['actualizar'];
        $permiso_eliminar = $arraydata['eliminar'];
        $permiso_consultar = $arraydata['consultar'];
        $query = "UPDATE perfil_modulo SET permiso_ingresar = :ingresar, permiso_actualizar = :actualizar, permiso_eliminar = :eliminar, permiso_consultar = :consultar WHERE id_perfil = :id_per AND id_modulo = :id_mod";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'id_per' => $id_perfil,
            'id_mod' => $id_modulo,
            'ingresar' => $permiso_ingresar,
            'actualizar' => $permiso_actualizar,
            'eliminar' => $permiso_eliminar,
            'consultar' => $permiso_consultar]);
        $this->response->success("Permisos actualizados exitosamente", [], 1);
    }

    function DELETE_PERFIL_MODULO($idPerfil, $idModulo){
        $query = "DELETE FROM perfil_modulo WHERE id_perfil = :id_per AND id_modulo = :id_mod";
        $statement = $this->connection->prepare($query);
        $statement->execute([ 'id_per' => $idPerfil, 'id_mod' => $idModulo ]);
        $this->response->success("Permisos eliminados correctamente", [], 1);
    }
}