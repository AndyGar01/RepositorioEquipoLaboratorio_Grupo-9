CREATE DATABASE IF NOT EXISTS VitaLab
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE VitaLab;

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

/*muestras, medicion y pesaje, analisis, preparacion, control termico, etc*/
CREATE TABLE categoria_equipo (
    id_categoria  INT NOT NULL AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion_categoria VARCHAR(255),
    CONSTRAINT pk_categoria_equipo PRIMARY KEY (id_categoria)
);

/*existente, rentado, agotado, dañado, mantenimiento*/
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
    activo TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_equipo PRIMARY KEY (id_equipo),
    CONSTRAINT uq_equipo_codigo UNIQUE (codigo_equipo),
    CONSTRAINT uq_equipo_serie UNIQUE (numero_serie),
    CONSTRAINT fk_equipo_categoria FOREIGN KEY (id_categoria) REFERENCES categoria_equipo(id_categoria),
    CONSTRAINT fk_equipo_estado FOREIGN KEY (id_estado_equipo)REFERENCES estado_equipo(id_estado_equipo)
);

CREATE TABLE perfil (
    id_perfil INT NOT NULL AUTO_INCREMENT,
    nombre_perfil VARCHAR(50)  NOT NULL,
    descripcion_perfil VARCHAR(255),
    activo_perfil TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT pk_perfil PRIMARY KEY (id_perfil)
);

CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    id_perfil INT NOT NULL,
    carne_usuario VARCHAR(20),
    nombre_usuario VARCHAR(100) NOT NULL,
    apellido_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(150) NOT NULL,
    contrasena_usuario VARCHAR(255) NOT NULL,
    activo_usuario TINYINT NOT NULL DEFAULT 1,
    fecha_creacion_usuario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_usuario PRIMARY KEY (id_usuario),
    CONSTRAINT fk_usuario_perfil FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil)
);

/*proceso, completo, incompleto, no pago*/
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

CREATE TABLE detalle_prestamo (
    id_detalle INT NOT NULL AUTO_INCREMENT,
    id_prestamo INT NOT NULL,
    id_equipo INT NOT NULL,
    CONSTRAINT pk_detalle_prestamo PRIMARY KEY (id_detalle),
    CONSTRAINT uq_detalle_prestamo_equipo UNIQUE (id_prestamo, id_equipo),
    CONSTRAINT fk_detprestamo_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamo(id_prestamo),
    CONSTRAINT fk_detprestamo_equipo FOREIGN KEY (id_equipo) REFERENCES equipo(id_equipo)
);

/*dañado, intacto, incompleto, completo*/
CREATE TABLE estado_condicion_devolucion (
    id_estado_condicion INT NOT NULL AUTO_INCREMENT,
    nombre_estado_condicion_devolucion VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estado_condicion PRIMARY KEY (id_estado_condicion)
);

/*agregar id alumno, quien hizo la devolucion*/
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

CREATE TABLE permiso (
    id_permiso INT NOT NULL AUTO_INCREMENT,
    nombre_permiso VARCHAR(100) NOT NULL,
    descripcion_permiso VARCHAR(255),
    CONSTRAINT pk_permiso PRIMARY KEY (id_permiso)
);

CREATE TABLE modulo (
    id_modulo INT NOT NULL AUTO_INCREMENT,
    nombre_modulo VARCHAR(100) NOT NULL,
    descripcion_modulo VARCHAR(255),
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
    CONSTRAINT fk_perfilmodulo_perfil FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    CONSTRAINT fk_perfilmodulo_modulo FOREIGN KEY (id_modulo) REFERENCES modulo(id_modulo)
);

CREATE TABLE perfil_permiso ( 
    id_perfil INT NOT NULL,
    id_permiso INT NOT NULL,
    CONSTRAINT pk_perfil_permiso PRIMARY KEY  (id_perfil, id_permiso),
    CONSTRAINT fk_perfilpermiso_perfil FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil),
    CONSTRAINT fk_perfilpermiso_permiso FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso)
);

CREATE TABLE sesion (
    id_sesion INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    /*llave de acceso*/
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
    accion_bitacora VARCHAR(50)  NOT NULL,
    tabla_afectada_bitacora VARCHAR(100) NOT NULL,
    id_registro_afectado_bitacora INT,
    descripción_bitacora VARCHAR(75) NOT NULL,
    fecha_hora_bitacora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_bitacora VARCHAR(45),
    CONSTRAINT pk_bitacora PRIMARY KEY (id_bitacora),
    CONSTRAINT fk_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);