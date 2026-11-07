<div id="contenidoModulo" data-modulo="7" class="modulo_prestamos">
    <header class="prestamos_encabezado">
        <h1 class="titulo">Préstamos</h1>
        <p>Registra préstamos presenciales y revisa las solicitudes enviadas por los alumnos.</p>
    </header>

    <section class="prestamo_panel" aria-labelledby="tituloRegistroPrestamo">
        <div class="prestamo_panel_encabezado">
            <h2 id="tituloRegistroPrestamo">Registrar préstamo presencial</h2>
            <p>Indica quién recibe el equipo y cuándo debe devolverlo.</p>
        </div>
        <form id="formPrestamo" class="formulario">
            <div class="prestamo_datos">
                <div class="prestamo_campo">
                    <label for="id_alumno_prestamo">Alumno</label>
                    <select id="id_alumno_prestamo" required>
                        <option value="">Seleccione un alumno</option>
                    </select>
                </div>
                <div class="prestamo_campo">
                    <label for="fecha_devolucion_prestamo">Fecha esperada de devolución</label>
                    <input type="date" id="fecha_devolucion_prestamo" required>
                </div>
            </div>

            <section class="prestamo_seccion_equipo" aria-labelledby="tituloSeleccionEquipo">
                <div class="prestamo_subencabezado">
                    <h3 id="tituloSeleccionEquipo">Equipo que se entregará</h3>
                    <p>Marca cada equipo e indica cuántas unidades se prestarán.</p>
                </div>
                <div class="inventario_filtros prestamo_filtros">
                    <div class="inventario_filtro"><label for="buscarEquipoPrestamo">Buscar equipo</label><input type="search" id="buscarEquipoPrestamo" placeholder="Código o nombre"></div>
                    <div class="inventario_filtro"><label for="filtroCategoriaPrestamo">Categoría</label><select id="filtroCategoriaPrestamo"><option value="">Todas</option></select></div>
                    <div class="inventario_filtro"><label for="filtroDisponiblePrestamo">Disponibilidad</label><select id="filtroDisponiblePrestamo"><option value="">Todos</option><option value="1">Con disponibles</option><option value="0">Agotados</option></select></div>
                </div>
                <p id="mensajeEquiposPrestamo" class="inventario_mensaje_estado" aria-live="polite"></p>
                <div class="scroll_tabla prestamo_tabla_contenedor">
                    <table class="contenido prestamo_tabla">
                        <caption class="solo_lectores">Equipos disponibles para el préstamo presencial</caption>
                        <thead>
                            <tr>
                                <th scope="col">Incluir</th>
                                <th scope="col">Código</th>
                                <th scope="col">Equipo</th>
                                <th scope="col">Categoría</th>
                                <th scope="col">Existencias</th>
                                <th scope="col">Disponibles</th>
                                <th scope="col">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEquiposPrestamo"></tbody>
                    </table>
                </div>
            </section>

            <div class="prestamo_pie_formulario">
                <p id="resumenPrestamo" aria-live="polite">No has seleccionado equipo.</p>
                <div class="botones">
                    <button type="submit" id="Agregar_prestamo" class="AgregarModificar" disabled>Registrar préstamo</button>
                    <button type="button" id="Limpiar_prestamo">Limpiar selección</button>
                </div>
            </div>
            <p id="mensajeFormularioPrestamo" class="prestamo_mensaje_formulario" aria-live="polite"></p>
        </form>
    </section>

    <section class="prestamo_panel" aria-labelledby="tituloHistorialPrestamos">
        <div class="prestamo_panel_encabezado">
            <h2 id="tituloHistorialPrestamos">Préstamos registrados</h2>
            <p>Consulta los préstamos creados desde el mostrador y las solicitudes aprobadas.</p>
        </div>
        <p id="mensajePrestamos" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="scroll_tabla prestamo_tabla_contenedor prestamo_tabla_historial">
            <table class="contenido prestamo_tabla">
                <caption class="solo_lectores">Historial de préstamos registrados</caption>
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Alumno</th>
                        <th scope="col">Equipos</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Fecha de préstamo</th>
                        <th scope="col">Devolución esperada</th>
                    </tr>
                </thead>
                <tbody id="tablaPrestamo"></tbody>
            </table>
        </div>
    </section>

    <section class="prestamo_panel" aria-labelledby="tituloSolicitudesPendientes">
        <div class="prestamo_panel_encabezado prestamo_solicitudes_encabezado">
            <div>
                <h2 id="tituloSolicitudesPendientes">Solicitudes de alumnos</h2>
                <p id="ayudaSolicitud">Selecciona una solicitud para aprobarla o rechazarla.</p>
            </div>
            <div class="prestamo_acciones_solicitud">
                <button type="button" id="Aprobar_solicitud" class="AgregarModificar" disabled>Aprobar solicitud</button>
                <button type="button" id="Rechazar_solicitud" class="Eliminar" disabled>Rechazar solicitud</button>
            </div>
        </div>
        <p id="mensajeSolicitudesPendientes" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="scroll_tabla prestamo_tabla_contenedor prestamo_tabla_solicitudes">
            <table class="contenido prestamo_tabla" aria-describedby="ayudaSolicitud">
                <caption class="solo_lectores">Solicitudes de préstamo pendientes de aprobación</caption>
                <thead>
                    <tr>
                        <th scope="col">Solicitud</th>
                        <th scope="col">Alumno</th>
                        <th scope="col">Carné</th>
                        <th scope="col">Equipo solicitado</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Devolución esperada</th>
                    </tr>
                </thead>
                <tbody id="tablaSolicitudesPendientes"></tbody>
            </table>
        </div>
    </section>
</div>
