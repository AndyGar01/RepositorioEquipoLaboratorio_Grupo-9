<div id="contenidoModulo" data-modulo="7">
    <h1 class="titulo">Registro de Préstamos</h1><br>
    <form id="formPrestamo" class="formulario">
        <div class="campos">
            <select id="id_alumno_prestamo">
                <option value="">Seleccione un alumno</option>
            </select>
            <input type="date" id="fecha_devolucion_prestamo" title="Fecha de devolución esperada">
        </div>
        <div class="inventario_filtros">
            <div class="inventario_filtro"><label for="buscarEquipoPrestamo">Buscar equipo</label><input type="search" id="buscarEquipoPrestamo" placeholder="Código o nombre"></div>
            <div class="inventario_filtro"><label for="filtroCategoriaPrestamo">Categoría</label><select id="filtroCategoriaPrestamo"><option value="">Todas</option></select></div>
            <div class="inventario_filtro"><label for="filtroDisponiblePrestamo">Disponibilidad</label><select id="filtroDisponiblePrestamo"><option value="">Todos</option><option value="1">Con disponibles</option><option value="0">Agotados</option></select></div>
        </div>
        <p id="mensajeEquiposPrestamo" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="scroll_tabla">
        <table border=1 class="contenido">
            <thead>
                <tr>
                    <th>Seleccionar</th>
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
        <div class="botones">
            <button type="submit" id="Agregar_prestamo" class="AgregarModificar">Registrar préstamo</button>
            <button type="button" id="Limpiar_prestamo">Limpiar</button>
        </div>
    </form>
    <div class="scroll_tabla">
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Id</th>
                <th>Alumno</th>
                <th>Equipos</th>
                <th>Estado</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución Esperada</th>
            </tr>
        </thead>
        <tbody id="tablaPrestamo"></tbody>
    </table>
    </div>
</div>
