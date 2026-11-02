<div id="contenidoModulo" data-modulo="7">
    <h1 class="titulo">Registro de Préstamos</h1><br>
    <form id="formPrestamo" class="formulario">
        <div class="campos">
            <select id="id_alumno_prestamo">
                <option value="">Seleccione un alumno</option>
            </select>
            <input type="date" id="fecha_devolucion_prestamo" title="Fecha de devolución">
        </div>
        <div class="campos" id="listaEquipos"></div>
        <div class="botones">
            <button type="submit" id="Agregar_prestamo" class="AgregarModificar">Registrar préstamo</button>
            <button type="button" id="Limpiar_prestamo">Limpiar</button>
        </div>
    </form>
    <br><br>
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Id</th>
                <th>Alumno</th>
                <th>Equipos</th>
                <th>Estado</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
            </tr>
        </thead>
        <tbody id="tablaPrestamo"></tbody>
    </table>
</div>
