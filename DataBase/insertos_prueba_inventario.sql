USE vitalab;

INSERT INTO perfil (nombre_perfil, descripcion_perfil, activo_perfil)
SELECT 'Encargado de inventario - Pruebas', 'Perfil de prueba para administrar el inventario.', 1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM perfil WHERE nombre_perfil = 'Encargado de inventario - Pruebas'
);

INSERT INTO modulo (nombre_modulo, descripcion_modulo)
SELECT 'Equipos', 'Administración del inventario de equipos.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM modulo WHERE nombre_modulo = 'Equipos'
);

INSERT INTO perfil_modulo (id_perfil, id_modulo, permiso_ingresar, permiso_actualizar, permiso_eliminar, permiso_consultar)
SELECT p.id_perfil, m.id_modulo, 1, 1, 1, 1
FROM perfil p
CROSS JOIN modulo m
WHERE p.nombre_perfil = 'Encargado de inventario - Pruebas'
  AND m.nombre_modulo = 'Equipos'
ON DUPLICATE KEY UPDATE
    permiso_ingresar = 1,
    permiso_actualizar = 1,
    permiso_eliminar = 1,
    permiso_consultar = 1;

INSERT INTO usuario (id_perfil, carne_usuario, nombre_usuario, apellido_usuario, correo_usuario, contrasena_usuario, activo_usuario)
SELECT p.id_perfil, 'LAB-PRUEBA', 'Usuario Inventario', 'Pruebas', 'inventario.pruebas@vitalab.test', '$2y$10$xAA2uaEbo/oO3xtAjKW3H.q/KBaWbmZRfsmHIhURGrSxPzQ692l3q', 1
FROM perfil p
WHERE p.nombre_perfil = 'Encargado de inventario - Pruebas'
  AND NOT EXISTS (
      SELECT 1 FROM usuario WHERE correo_usuario = 'inventario.pruebas@vitalab.test'
  );

UPDATE usuario
SET id_perfil = (SELECT id_perfil FROM perfil WHERE nombre_perfil = 'Encargado de inventario - Pruebas' LIMIT 1),
    contrasena_usuario = '$2y$10$xAA2uaEbo/oO3xtAjKW3H.q/KBaWbmZRfsmHIhURGrSxPzQ692l3q',
    activo_usuario = 1
WHERE correo_usuario = 'inventario.pruebas@vitalab.test';

INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, cantidad_existencias, activo)
SELECT c.id_categoria, s.id_estado_equipo, 'EQ-PRU-001', 'Microscopio de prueba', 'Microscopio binocular para verificar el inventario.', 'PRU-MIC-001', '2025-01-15', 1, 1
FROM categoria_equipo c
CROSS JOIN estado_equipo s
WHERE c.nombre_categoria = 'Análisis'
  AND s.nombre_estado_equipo = 'Existente'
  AND NOT EXISTS (SELECT 1 FROM equipo WHERE codigo_equipo = 'EQ-PRU-001');

INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, cantidad_existencias, activo)
SELECT c.id_categoria, s.id_estado_equipo, 'EQ-PRU-002', 'Centrífuga de prueba', 'Centrífuga de mesa para prácticas de laboratorio.', 'PRU-CEN-002', '2025-03-10', 1, 1
FROM categoria_equipo c
CROSS JOIN estado_equipo s
WHERE c.nombre_categoria = 'Análisis'
  AND s.nombre_estado_equipo = 'Existente'
  AND NOT EXISTS (SELECT 1 FROM equipo WHERE codigo_equipo = 'EQ-PRU-002');

INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, cantidad_existencias, activo)
SELECT c.id_categoria, s.id_estado_equipo, 'EQ-PRU-003', 'Pipetas de prueba', 'Lote de pipetas para validar el campo de existencias.', NULL, '2025-05-20', 5, 1
FROM categoria_equipo c
CROSS JOIN estado_equipo s
WHERE c.nombre_categoria = 'Muestras'
  AND s.nombre_estado_equipo = 'Existente'
  AND NOT EXISTS (SELECT 1 FROM equipo WHERE codigo_equipo = 'EQ-PRU-003');
