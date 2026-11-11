<div id="contenidoModulo" data-modulo="8">
    <h1 class="titulo">Registro de Devoluciones</h1><br>
    <form id="formDevolucion" class="formulario">
        <div class="campos">
            <select id="id_prestamo_devolucion">
                <option value="">Seleccione un préstamo</option>
            </select>
            <input type="text" id="observaciones_devolucion" maxlength="75" placeholder="Observaciones (opcional)">
        </div>
        <p id="infoPrestamoDevolucion" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="scroll_tabla">
        <table border=1 class="contenido">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Equipo</th>
                    <th>Prestados</th>
                    <th>Dañados o incompletos</th>
                    <th>Condición</th>
                </tr>
            </thead>
            <tbody id="tablaEquiposDevolucion"></tbody>
        </table>
        </div>
        <p id="totalDevolucion" class="inventario_mensaje_estado" aria-live="polite"></p>
        <div class="botones">
            <button type="submit" id="Agregar_devolucion" class="AgregarModificar">Registrar devolución</button>
            <button type="button" id="Limpiar_devolucion">Limpiar</button>
        </div>
    </form>
    <div class="scroll_tabla">
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Id</th>
                <th>Alumno</th>
                <th>Equipos</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución Esperada</th>
            </tr>
        </thead>
        <tbody id="tablaDevolucion"></tbody>
    </table>
    </div>
</div>
