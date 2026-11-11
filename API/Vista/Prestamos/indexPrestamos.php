<div id="contenidoModulo" data-modulo="7">
    <h1 class="titulo">Mantenimiento de Préstamos</h1><br>
    <form id="formPrestamo" class="formulario">
        <div class="campos">
            <select id="id_alumno_prestamo" required>
                <option value="">Seleccione un alumno</option>
            </select>
            <input type="date" id="fecha_devolucion_prestamo" title="Fecha esperada de devolución" required>
            <input type="search" id="buscarEquipoPrestamo" placeholder="Buscar equipo (código o nombre)">
            <select id="filtroCategoriaPrestamo">
                <option value="">Todas las categorías</option>
            </select>
            <select id="filtroDisponiblePrestamo">
                <option value="">Toda disponibilidad</option>
                <option value="1">Con disponibles</option>
                <option value="0">Agotados</option>
            </select>
        </div>
        <p id="mensajeEquiposPrestamo" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="scroll_tabla">
        <table border=1 class="contenido">
            <thead>
                <tr>
                    <th>Incluir</th>
                    <th>Código</th>
                    <th>Equipo</th>
                    <th>Categoría</th>
                    <th>Existencias</th>
                    <th>Disponibles</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody id="tablaEquiposPrestamo"></tbody>
        </table>
        </div>
        <p id="resumenPrestamo" class="inventario_mensaje_estado" aria-live="polite">No has seleccionado equipo.</p>
        <div class="botones">
            <button type="submit" id="Agregar_prestamo" class="AgregarModificar" disabled>Registrar préstamo</button>
            <button type="button" id="Limpiar_prestamo">Limpiar</button>
        </div>
        <p id="mensajeFormularioPrestamo" class="inventario_mensaje_estado" aria-live="polite"></p>
    </form>
    <br>
    <h2>Préstamos registrados</h2>
    <p id="mensajePrestamos" class="inventario_mensaje_estado" aria-live="polite"></p>
    <div class="scroll_tabla">
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Id</th>
                <th>Alumno</th>
                <th>Equipos</th>
                <th>Estado</th>
                <th>Fecha de préstamo</th>
                <th>Devolución esperada</th>
            </tr>
        </thead>
        <tbody id="tablaPrestamo"></tbody>
    </table>
    </div>
    <br>
    <h2>Solicitudes de alumnos</h2>
    <p id="ayudaSolicitud" class="inventario_mensaje_estado">Selecciona una solicitud para aprobarla o rechazarla.</p>
    <div class="botones">
        <button type="button" id="Aprobar_solicitud" class="AgregarModificar" disabled>Aprobar solicitud</button>
        <button type="button" id="Rechazar_solicitud" class="Eliminar" disabled>Rechazar solicitud</button>
    </div>
    <p id="mensajeSolicitudesPendientes" class="inventario_mensaje_estado" aria-live="polite"></p>
    <div class="scroll_tabla">
    <table border=1 class="contenido" aria-describedby="ayudaSolicitud">
        <thead>
            <tr>
                <th>Solicitud</th>
                <th>Alumno</th>
                <th>Carné</th>
                <th>Equipo solicitado</th>
                <th>Fecha</th>
                <th>Devolución esperada</th>
            </tr>
        </thead>
        <tbody id="tablaSolicitudesPendientes"></tbody>
    </table>
    </div>
</div>
