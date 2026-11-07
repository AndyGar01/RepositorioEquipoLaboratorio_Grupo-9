<?php
require_once(__DIR__ . "/../Core/Connection.php");
require_once(__DIR__ . "/../Modelo/M_Equipos.php");
require_once(__DIR__ . "/../Core/Response.php");
require_once(__DIR__ . "/../Core/AdminApi.php");

function subirImagenEquipo($archivo)
{
    if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException("No se pudo recibir la imagen. Revisa su tamaño e inténtalo de nuevo.");
    }
    if (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException("La imagen no debe superar los 5 MB.");
    }

    $tipos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $informacion = new finfo(FILEINFO_MIME_TYPE);
    $tipo = $informacion->file($archivo['tmp_name']);
    if (!isset($tipos[$tipo]) || !getimagesize($archivo['tmp_name'])) {
        throw new InvalidArgumentException("Elige una imagen JPG, PNG o WEBP válida.");
    }

    $carpeta = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'equipos';
    if (!is_dir($carpeta) || !is_writable($carpeta)) {
        throw new RuntimeException("No se puede guardar la imagen en la carpeta de equipos.");
    }

    $nombre = 'equipo_' . bin2hex(random_bytes(16)) . '.' . $tipos[$tipo];
    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . DIRECTORY_SEPARATOR . $nombre)) {
        throw new RuntimeException("No se pudo guardar la imagen.");
    }
    return 'images/equipos/' . $nombre;
}

function eliminarImagenEquipo($ruta)
{
    if (!preg_match('/^images\/equipos\/equipo_[a-f0-9]{32}\.(jpg|png|webp)$/', (string) $ruta)) {
        return;
    }
    $archivo = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
    if (is_file($archivo)) {
        unlink($archivo);
    }
}

$conection = new Connection();
$response = new Response();
$methodOriginal = $_SERVER['REQUEST_METHOD'];
$method = $methodOriginal === 'POST' && ($_POST['_method'] ?? '') === 'PUT' ? 'PUT' : $methodOriginal;
AdminApi::verificar($conection, $response, $method, 6);

parse_str(file_get_contents("php://input"), $_PUT);
parse_str(file_get_contents("php://input"), $_DELETE);

switch ($method) {
    case 'GET':
        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->SELECT_EQUIPOS();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los equipos", 3001, 400);
        }
        break;
    case 'POST':
        $imagenNueva = null;
        try {
            $imagenNueva = subirImagenEquipo($_FILES['imagen'] ?? null);
            $datos = $_POST;
            if ($imagenNueva !== null) {
                $datos['_imagen_url'] = $imagenNueva;
            }
            $equipo = new M_Equipos($conection, $response);
            $equipo->INSERT_EQUIPO($datos);
            $response->success("Equipo agregado correctamente", [], 1);
        } catch (\Throwable $th) {
            if ($imagenNueva !== null) {
                eliminarImagenEquipo($imagenNueva);
            }
            if ($th instanceof InvalidArgumentException) {
                $response->error($th->getMessage(), 3006, 400);
            }
            if ($th instanceof PDOException && ($th->errorInfo[1] ?? 0) == 1062) {
                $response->error("El código o el número de serie ya existe", 3005, 409);
            }
            $response->error("Error al agregar el equipo", 3002, 400);
        }
        break;
    case 'PUT':
        $imagenNueva = null;
        $imagenAnterior = null;
        try {
            $datos = $methodOriginal === 'POST' ? $_POST : $_PUT;
            $id = filter_var($datos['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                throw new InvalidArgumentException("Selecciona un equipo válido para editar.");
            }
            $consulta = $conection->prepare("SELECT imagen_url FROM equipo WHERE id_equipo = :id AND activo = 1");
            $consulta->execute(['id' => $id]);
            $imagenAnterior = $consulta->fetchColumn();
            if ($imagenAnterior === false) {
                throw new InvalidArgumentException("No se encontró el equipo seleccionado.");
            }

            if ($methodOriginal === 'POST') {
                $imagenNueva = subirImagenEquipo($_FILES['imagen'] ?? null);
                if ($imagenNueva !== null) {
                    $datos['_imagen_url'] = $imagenNueva;
                }
            }
            $equipo = new M_Equipos($conection, $response);
            $equipo->UPDATE_EQUIPO($datos);
            if ($imagenNueva !== null && $imagenAnterior !== $imagenNueva) {
                eliminarImagenEquipo($imagenAnterior);
            }
            $response->success("Equipo actualizado correctamente", [], 1);
        } catch (\Throwable $th) {
            if ($imagenNueva !== null) {
                eliminarImagenEquipo($imagenNueva);
            }
            if ($th instanceof InvalidArgumentException) {
                $response->error($th->getMessage(), 3006, 400);
            }
            if ($th instanceof PDOException && ($th->errorInfo[1] ?? 0) == 1062) {
                $response->error("El código o el número de serie ya existe", 3005, 409);
            }
            $response->error("Error al actualizar el equipo", 3003, 400);
        }
        break;
    case 'DELETE':
        try {
            $equipo = new M_Equipos($conection, $response);
            $equipo->DELETE_EQUIPO($_DELETE['id']);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el equipo", 3004, 400);
        }
        break;
}
