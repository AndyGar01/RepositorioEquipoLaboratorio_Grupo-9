<div id="contenidoModulo" data-modulo="8">
    <h1 class="titulo">Registro de Devoluciones</h1><br>
    <div class="pestanas_devolucion">
        <button type="button" class="pestana_devolucion activa" data-pestana="Devolver">Devolver</button>
        <button type="button" class="pestana_devolucion" data-pestana="Reparaciones">Reparaciones</button>
        <button type="button" class="pestana_devolucion" data-pestana="Notificaciones">Notificaciones <span id="contadorNotificaciones"></span></button>
    </div>
    <div id="panelDevolver">
        <form id="formDevolucion" class="formulario">
            <div class="campos">
                <select id="id_prestamo_devolucion" aria-label="Préstamo a devolver">
                    <option value="">Seleccione un préstamo</option>
                </select>
                <input type="text" id="observaciones_devolucion" aria-label="Observaciones de la devolución" maxlength="75" placeholder="Observaciones (opcional)">
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
    </div>
    <div id="panelReparaciones" class="oculto">
        <div class="scroll_tabla">
        <table border=1 class="contenido">
            <thead>
                <tr>
                    <th>Préstamo</th>
                    <th>Equipo</th>
                    <th>Condición</th>
                    <th>Pendientes</th>
                    <th>Reparados</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tablaReparacion"></tbody>
        </table>
        </div>
    </div>
    <div id="panelNotificaciones" class="oculto">
        <div class="scroll_tabla">
        <table border=1 class="contenido">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tablaNotificacion"></tbody>
        </table>
        </div>
    </div>
</div>
