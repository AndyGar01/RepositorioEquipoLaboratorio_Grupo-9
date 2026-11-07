# RepositorioEquipoLaboratorio_Grupo-9
Repositorio de Desarrollo Web enfocado en el Sistema de inventario, préstamos y devolución de equipo de laboratorio.

## Vista para alumnos

El ingreso se realiza desde `login.html`. El sistema consulta el perfil de la cuenta y abre el panel administrativo o el catálogo del alumno. Al registrar un alumno con estado Activo desde el mantenimiento, se crea su cuenta vinculada y se muestra una clave temporal; el alumno debe cambiarla en su primer ingreso.

Para dar acceso a alumnos que ya estaban registrados antes de esta actualización, selecciónalos en el mantenimiento de Alumnos y usa «Generar clave temporal».

Las solicitudes de equipo quedan pendientes. Al aprobar una solicitud desde el módulo de Préstamos, el sistema vuelve a revisar las existencias y registra el préstamo con el usuario encargado que la aprobó.

En el mantenimiento de Equipos, el encargado puede adjuntar una imagen al registrar o editar un producto. Se aceptan archivos JPG, PNG y WEBP de hasta 5 MB. La imagen queda en `images/equipos` y el catálogo del alumno la muestra en la ficha del equipo. Si no se adjunta una, se conserva la ilustración de referencia.

Para imágenes encontradas en internet, usa archivos propios o que permitan su reutilización. No es necesario renombrarlas ni copiarlas manualmente a la carpeta: se seleccionan desde el formulario de equipo y el sistema las guarda.

## Base de datos

Importa `DataBase/databaseVitaLab.sql` desde phpMyAdmin para crear la base completa con la vista para alumnos, las solicitudes y el inventario. Este archivo elimina y vuelve a crear la base `vitalab` antes de instalarla.

Los datos de acceso y los registros de ejemplo están en `DataBase/insertos_prueba.txt`; se ejecutan después de importar la base.
