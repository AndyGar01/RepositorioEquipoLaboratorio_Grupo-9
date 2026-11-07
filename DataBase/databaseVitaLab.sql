DROP DATABASE IF EXISTS vitalab;
CREATE DATABASE vitalab
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vitalab;

CREATE TABLE carrera_alumno (
    id_carrera_alumno INT NOT NULL AUTO_INCREMENT,
    nombre_carrera_alumno VARCHAR(100) NOT NULL,
    descripcion_carrera_alumno VARCHAR(255),
    activo_carrera_alumno TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_carrera_alumno PRIMARY KEY (id_carrera_alumno)
);

CREATE TABLE estado_alumno (
    id_estado_alumno INT NOT NULL AUTO_INCREMENT,
    nombre_estado_alumno VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_alumno PRIMARY KEY (id_estado_alumno)
);

CREATE TABLE categoria_equipo (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion_categoria VARCHAR(255),
    CONSTRAINT pk_categoria_equipo PRIMARY KEY (id_categoria)
);

CREATE TABLE estado_equipo (
    id_estado_equipo INT NOT NULL AUTO_INCREMENT,
    nombre_estado_equipo VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_equipo PRIMARY KEY (id_estado_equipo)
);

CREATE TABLE equipo (
    id_equipo INT NOT NULL AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    id_estado_equipo INT NOT NULL,
    codigo_equipo VARCHAR(50) NOT NULL,
    nombre_equipo VARCHAR(50) NOT NULL,
    descripcion_equipo VARCHAR(150) NOT NULL,
    numero_serie VARCHAR(100),
    fecha_adquisicion DATE,
    imagen_url VARCHAR(500),
    cantidad_existencias INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_equipo PRIMARY KEY (id_equipo),
    CONSTRAINT uq_equipo_codigo UNIQUE (codigo_equipo),
    CONSTRAINT uq_equipo_serie UNIQUE (numero_serie),
    CONSTRAINT fk_equipo_categoria FOREIGN KEY (id_categoria) REFERENCES categoria_equipo(id_categoria),
    CONSTRAINT fk_equipo_estado FOREIGN KEY (id_estado_equipo) REFERENCES estado_equipo(id_estado_equipo)
);

CREATE TABLE perfil (
    id_perfil INT NOT NULL AUTO_INCREMENT,
    nombre_perfil VARCHAR(50) NOT NULL,
    descripcion_perfil VARCHAR(255),
    activo_perfil TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_perfil PRIMARY KEY (id_perfil)
);

CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    id_perfil INT NOT NULL,
    id_alumno INT,
    carne_usuario VARCHAR(20),
    nombre_usuario VARCHAR(100) NOT NULL,
    apellido_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(150) NOT NULL,
    contrasena_usuario VARCHAR(255) NOT NULL,
    cambio_contrasena_requerido TINYINT NOT NULL DEFAULT 0,
    activo_usuario TINYINT NOT NULL DEFAULT 1,
    fecha_creacion_usuario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_usuario PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuario_alumno UNIQUE (id_alumno),
    CONSTRAINT fk_usuario_perfil FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil)
);

CREATE TABLE alumno (
    id_alumno INT NOT NULL AUTO_INCREMENT,
    id_carrera_alumno INT NOT NULL,
    id_estado_alumno INT NOT NULL,
    carne_alumno VARCHAR(10) NOT NULL,
    nombre_alumno VARCHAR(100) NOT NULL,
    apellido_alumno VARCHAR(100) NOT NULL,
    correo_alumno VARCHAR(100) NOT NULL,
    fecha_nacimiento_alumno DATE NOT NULL,
    activo_alumno TINYINT NOT NULL DEFAULT 1,
    fecha_registro_alumno DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_alumno PRIMARY KEY (id_alumno),
    CONSTRAINT uq_alumno_carne UNIQUE (carne_alumno),
    CONSTRAINT uq_alumno_correo UNIQUE (correo_alumno),
    CONSTRAINT fk_alumno_carrera FOREIGN KEY (id_carrera_alumno) REFERENCES carrera_alumno(id_carrera_alumno),
    CONSTRAINT fk_alumno_estado FOREIGN KEY (id_estado_alumno) REFERENCES estado_alumno(id_estado_alumno)
);

ALTER TABLE usuario
    ADD CONSTRAINT fk_usuario_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno);

CREATE TABLE estado_prestamo (
    id_estado_prestamo INT NOT NULL AUTO_INCREMENT,
    nombre_estado_prestamo VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_prestamo PRIMARY KEY (id_estado_prestamo)
);

CREATE TABLE prestamo (
    id_prestamo INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_alumno INT NOT NULL,
    id_estado_prestamo INT NOT NULL,
    fecha_prestamo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_esperada DATE NOT NULL,
    CONSTRAINT pk_prestamo PRIMARY KEY (id_prestamo),
    CONSTRAINT fk_prestamo_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_prestamo_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno),
    CONSTRAINT fk_prestamo_estado FOREIGN KEY (id_estado_prestamo) REFERENCES estado_prestamo(id_estado_prestamo)
);

CREATE TABLE estado_solicitud_prestamo (
    id_estado_solicitud INT NOT NULL AUTO_INCREMENT,
    nombre_estado_solicitud VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_solicitud PRIMARY KEY (id_estado_solicitud),
    CONSTRAINT uq_estado_solicitud UNIQUE (nombre_estado_solicitud)
);

CREATE TABLE solicitud_prestamo (
    id_solicitud INT NOT NULL AUTO_INCREMENT,
    id_alumno INT NOT NULL,
    id_estado_solicitud INT NOT NULL,
    id_usuario_resuelve INT,
    id_prestamo INT,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_esperada DATE NOT NULL,
    fecha_resolucion DATETIME,
    CONSTRAINT pk_solicitud_prestamo PRIMARY KEY (id_solicitud),
    CONSTRAINT uq_solicitud_prestamo UNIQUE (id_prestamo),
    CONSTRAINT fk_solicitud_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno),
    CONSTRAINT fk_solicitud_estado FOREIGN KEY (id_estado_solicitud) REFERENCES estado_solicitud_prestamo(id_estado_solicitud),
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (id_usuario_resuelve) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_solicitud_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamo(id_prestamo)
);

CREATE TABLE detalle_solicitud_prestamo (
    id_detalle_solicitud INT NOT NULL AUTO_INCREMENT,
    id_solicitud INT NOT NULL,
    id_equipo INT NOT NULL,
    cantidad_solicitada INT UNSIGNED NOT NULL,
    CONSTRAINT pk_detalle_solicitud PRIMARY KEY (id_detalle_solicitud),
    CONSTRAINT uq_detalle_solicitud_equipo UNIQUE (id_solicitud, id_equipo),
    CONSTRAINT fk_detalle_solicitud_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitud_prestamo(id_solicitud),
    CONSTRAINT fk_detalle_solicitud_equipo FOREIGN KEY (id_equipo) REFERENCES equipo(id_equipo)
);

CREATE TABLE detalle_prestamo (
    id_detalle INT NOT NULL AUTO_INCREMENT,
    id_prestamo INT NOT NULL,
    id_equipo INT NOT NULL,
    cantidad_prestada INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT pk_detalle_prestamo PRIMARY KEY (id_detalle),
    CONSTRAINT uq_detalle_prestamo_equipo UNIQUE (id_prestamo, id_equipo),
    CONSTRAINT fk_detprestamo_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamo(id_prestamo),
    CONSTRAINT fk_detprestamo_equipo FOREIGN KEY (id_equipo) REFERENCES equipo(id_equipo)
);

CREATE TABLE estado_condicion_devolucion (
    id_estado_condicion INT NOT NULL AUTO_INCREMENT,
    nombre_estado_condicion_devolucion VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_condicion PRIMARY KEY (id_estado_condicion)
);

CREATE TABLE devolucion (
    id_devolucion INT NOT NULL AUTO_INCREMENT,
    id_prestamo INT NOT NULL,
    id_usuario INT NOT NULL,
    id_alumno INT NOT NULL,
    fecha_devolucion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones_devolucion VARCHAR(75) NOT NULL,
    CONSTRAINT pk_devolucion PRIMARY KEY (id_devolucion),
    CONSTRAINT fk_devolucion_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamo(id_prestamo),
    CONSTRAINT fk_devolucion_encargado FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_devolucion_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno)
);

CREATE TABLE detalle_devolucion (
    id_detalle_devolucion INT NOT NULL AUTO_INCREMENT,
    id_devolucion INT NOT NULL,
    id_detalle_prestamo INT NOT NULL,
    id_estado_condicion INT NOT NULL,
    observaciones TEXT,
    CONSTRAINT pk_detalle_devolucion PRIMARY KEY (id_detalle_devolucion),
    CONSTRAINT uq_detdev_detprestamo UNIQUE (id_devolucion, id_detalle_prestamo),
    CONSTRAINT fk_detdev_devolucion FOREIGN KEY (id_devolucion) REFERENCES devolucion(id_devolucion),
    CONSTRAINT fk_detdev_detalle_prestamo FOREIGN KEY (id_detalle_prestamo) REFERENCES detalle_prestamo(id_detalle),
    CONSTRAINT fk_detdev_condicion FOREIGN KEY (id_estado_condicion) REFERENCES estado_condicion_devolucion(id_estado_condicion)
);

CREATE TABLE modulo (
    id_modulo INT NOT NULL AUTO_INCREMENT,
    nombre_modulo VARCHAR(50) NOT NULL,
    descripcion_modulo VARCHAR(255) NOT NULL,
    CONSTRAINT pk_modulo PRIMARY KEY (id_modulo)
);

CREATE TABLE perfil_modulo (
    id_perfil INT NOT NULL,
    id_modulo INT NOT NULL,
    permiso_ingresar TINYINT NOT NULL DEFAULT 0,
    permiso_actualizar TINYINT NOT NULL DEFAULT 0,
    permiso_eliminar TINYINT NOT NULL DEFAULT 0,
    permiso_consultar TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT pk_perfil_modulo PRIMARY KEY (id_perfil, id_modulo),
    CONSTRAINT fk_perfil_modulo_pef FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    CONSTRAINT fk_perfil_modulo_mod FOREIGN KEY (id_modulo) REFERENCES modulo(id_modulo)
);

CREATE TABLE sesion (
    id_sesion INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token_sesion VARCHAR(255) NOT NULL,
    fecha_inicio_sesion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion_sesion DATETIME NOT NULL,
    ip_acceso_sesion VARCHAR(45),
    activo_sesion TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_sesion PRIMARY KEY (id_sesion),
    CONSTRAINT uq_sesion_token UNIQUE (token_sesion),
    CONSTRAINT fk_sesion_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE restablecimiento_contrasena (
    id_restablecimiento INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token_restablecimiento_contrasena VARCHAR(255) NOT NULL,
    fecha_solicitud_restablecimiento_contrasena DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion_restablecimiento_contrasena DATETIME NOT NULL,
    usado_restablecimiento_contrasena TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT pk_restablecimiento PRIMARY KEY (id_restablecimiento),
    CONSTRAINT uq_restablecimiento_token UNIQUE (token_restablecimiento_contrasena),
    CONSTRAINT fk_restablecimiento_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE configuracion_mora (
    id_configuracion_mora INT NOT NULL AUTO_INCREMENT,
    valor_mora_por_dia DECIMAL(10,2) NOT NULL,
    descripcion_configuracion_mora VARCHAR(255),
    fecha_inicio_vigencia_mora DATE NOT NULL,
    fecha_fin_vigencia_mora DATE NOT NULL,
    activo_configuracion_mora TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_configurarmora PRIMARY KEY (id_configuracion_mora)
);

CREATE TABLE mora (
    id_mora INT NOT NULL AUTO_INCREMENT,
    id_prestamo INT NOT NULL,
    id_configuracion_mora INT NOT NULL,
    dias_retraso_mora INT NOT NULL,
    total_mora DECIMAL(10,2) NOT NULL,
    fecha_calculo_mora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    pagado_mora TINYINT NOT NULL DEFAULT 0,
    fecha_pago_mora DATETIME,
    CONSTRAINT pk_mora PRIMARY KEY (id_mora),
    CONSTRAINT uq_mora_prestamo UNIQUE (id_prestamo),
    CONSTRAINT fk_mora_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamo(id_prestamo),
    CONSTRAINT fk_mora_config FOREIGN KEY (id_configuracion_mora) REFERENCES configuracion_mora(id_configuracion_mora)
);

CREATE TABLE bitacora (
    id_bitacora BIGINT NOT NULL AUTO_INCREMENT,
    id_usuario INT,
    accion_bitacora VARCHAR(50) NOT NULL,
    tabla_afectada_bitacora VARCHAR(100) NOT NULL,
    id_registro_afectado_bitacora INT,
    descripcion_bitacora VARCHAR(75) NOT NULL,
    fecha_hora_bitacora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_bitacora VARCHAR(45),
    CONSTRAINT pk_bitacora PRIMARY KEY (id_bitacora),
    CONSTRAINT fk_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

INSERT INTO carrera_alumno (id_carrera_alumno, nombre_carrera_alumno, descripcion_carrera_alumno, activo_carrera_alumno)
VALUES (1, 'Medicina', 'Carrera enfocada en el área de la salud.', 1);

INSERT INTO estado_alumno (id_estado_alumno, nombre_estado_alumno)
VALUES (1, 'Activo'), (2, 'Inactivo'), (3, 'Suspendido'), (4, 'Graduado');

INSERT INTO categoria_equipo (id_categoria, nombre_categoria, descripcion_categoria) VALUES
(1, 'Muestras', 'Equipo para la recolección y manejo de muestras.'),
(2, 'Medición y pesaje', 'Equipo para medir variables y determinar peso.'),
(3, 'Análisis', 'Equipo utilizado en análisis de laboratorio.'),
(4, 'Preparación', 'Equipo para preparar materiales y muestras.'),
(5, 'Control térmico', 'Equipo para controlar temperatura.');

INSERT INTO estado_equipo (id_estado_equipo, nombre_estado_equipo) VALUES
(1, 'Existente'),
(2, 'Rentado'),
(3, 'Agotado'),
(4, 'Dañado'),
(5, 'Mantenimiento');

INSERT INTO estado_prestamo (id_estado_prestamo, nombre_estado_prestamo) VALUES
(1, 'En proceso'),
(2, 'Completo'),
(3, 'Incompleto'),
(4, 'No pagó');

INSERT INTO estado_solicitud_prestamo (id_estado_solicitud, nombre_estado_solicitud) VALUES
(1, 'Pendiente'),
(2, 'Aprobada'),
(3, 'Rechazada');

INSERT INTO estado_condicion_devolucion (id_estado_condicion, nombre_estado_condicion_devolucion) VALUES
(1, 'Dañado'),
(2, 'Intacto'),
(3, 'Incompleto'),
(4, 'Completo');

INSERT INTO perfil (id_perfil, nombre_perfil, descripcion_perfil, activo_perfil) VALUES
(1, 'Administrador', 'Acceso a todos los módulos del sistema.', 1),
(2, 'Alumno', 'Acceso al catálogo y solicitudes de equipo.', 1),
(3, 'Auditor', 'Perfil de auditoría.', 0),
(4, 'Pruebas', 'Perfil reservado para pruebas.', 0);

INSERT INTO modulo (id_modulo, nombre_modulo, descripcion_modulo) VALUES
(1, 'Usuarios', 'Administración de usuarios.'),
(2, 'Perfiles', 'Administración de perfiles.'),
(3, 'Permisos', 'Administración de permisos.'),
(4, 'Alumnos', 'Administración de alumnos.'),
(5, 'Carreras', 'Administración de carreras.'),
(6, 'Equipos', 'Administración del inventario de equipos.'),
(7, 'Préstamos', 'Administración de préstamos y solicitudes.');

INSERT INTO perfil_modulo (id_perfil, id_modulo, permiso_ingresar, permiso_actualizar, permiso_eliminar, permiso_consultar)
SELECT 1, id_modulo, 1, 1, 1, 1 FROM modulo;

INSERT INTO usuario (id_usuario, id_perfil, carne_usuario, nombre_usuario, apellido_usuario, correo_usuario, contrasena_usuario, activo_usuario, fecha_creacion_usuario)
VALUES (2, 1, '9959-23-3070', 'Oscar', 'Morales', 'oscar@miumg.edu.gt ', '$2y$10$Jjfpkw.CCO7vPuZEkDD7HuPNRxF3fC1d7aopOlux3muq.oUhPLSCO', 1, '2026-10-07 08:44:37');

INSERT INTO alumno (id_alumno, id_carrera_alumno, id_estado_alumno, carne_alumno, nombre_alumno, apellido_alumno, correo_alumno, fecha_nacimiento_alumno, activo_alumno, fecha_registro_alumno) VALUES
(4, 1, 1, '9959233071', 'Oscarito', 'Morales', 'oscar@miumg.edu.ght', '2026-10-20', 1, '2026-10-20 10:07:48'),
(5, 1, 1, '9959233117', 'Andre', 'Gonzales', 'andre@gmail.com', '2026-10-22', 1, '2026-10-22 22:30:04'),
(8, 1, 1, '9959233070', 'Prueba', 'Pruebita', 'prueba@gmail.com', '2026-10-23', 1, '2026-10-23 16:38:51');

INSERT INTO equipo (id_categoria, id_estado_equipo, codigo_equipo, nombre_equipo, descripcion_equipo, numero_serie, fecha_adquisicion, cantidad_existencias, activo) VALUES
(1, 1, 'EQ-006', 'Pipeta', 'Pipeta automática de 100 a 1000 microlitros', 'PP-6001', '2025-06-10', 12, 1),
(2, 1, 'EQ-007', 'Probeta', 'Probeta graduada de vidrio de 100 ml', 'PB-7001', '2025-06-18', 20, 1),
(2, 1, 'EQ-008', 'Matraz Erlenmeyer', 'Matraz de 250 ml con tapón', 'MZ-8001', '2025-07-02', 15, 1),
(3, 1, 'EQ-009', 'Mechero Bunsen', 'Mechero de gas para laboratorio', 'MB-9001', '2025-07-20', 6, 1),
(3, 1, 'EQ-010', 'Agitador magnético', 'Agitador con placa calefactora', 'AG-1001', '2025-08-05', 4, 1),
(4, 1, 'EQ-011', 'Termómetro digital', 'Termómetro de sonda, rango de -50 a 300 °C', 'TD-1101', '2025-08-22', 10, 1),
(4, 1, 'EQ-012', 'Cronómetro', 'Cronómetro digital con memoria de vueltas', 'CR-1201', '2025-09-03', 7, 1),
(5, 1, 'EQ-013', 'Autoclave', 'Autoclave de sobremesa de 18 litros', 'AC-1301', '2025-09-15', 1, 1),
(5, 1, 'EQ-014', 'Gradilla', 'Gradilla de metal para 24 tubos de ensayo', 'GR-1401', '2025-10-01', 9, 1);

UPDATE equipo SET imagen_url = 'images/equipos/pipeta.svg' WHERE codigo_equipo = 'EQ-006';
UPDATE equipo SET imagen_url = 'images/equipos/probeta.svg' WHERE codigo_equipo = 'EQ-007';
UPDATE equipo SET imagen_url = 'images/equipos/matraz.svg' WHERE codigo_equipo = 'EQ-008';
UPDATE equipo SET imagen_url = 'images/equipos/mechero.svg' WHERE codigo_equipo = 'EQ-009';
UPDATE equipo SET imagen_url = 'images/equipos/agitador.svg' WHERE codigo_equipo = 'EQ-010';
UPDATE equipo SET imagen_url = 'images/equipos/termometro.svg' WHERE codigo_equipo = 'EQ-011';
UPDATE equipo SET imagen_url = 'images/equipos/cronometro.svg' WHERE codigo_equipo = 'EQ-012';
UPDATE equipo SET imagen_url = 'images/equipos/autoclave.svg' WHERE codigo_equipo = 'EQ-013';
UPDATE equipo SET imagen_url = 'images/equipos/gradilla.svg' WHERE codigo_equipo = 'EQ-014';
