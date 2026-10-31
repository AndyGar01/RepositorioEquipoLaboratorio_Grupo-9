<div id="contenidoModulo" data-modulo="3"> 
    <h1 class="titulo">Mantenimientos de Permisos</h1><br>
    <form id="formPerfilModulo" class="formulario">
        <div class="campos">
            <select id="id_perfilcombo">
                <option value="">Seleccione un perfil</option>
            </select>
            <select id="id_modulocombo">
                <option value="">Seleccione un módulo</option>
            </select>
        </div>
        <div class="campos">
            <label><input type="checkbox" id="ingresar"> Permiso de insertar</label>
            <label><input type="checkbox" id="actualizar"> Permiso de actualizar</label>
            <label><input type="checkbox" id="eliminar"> Permiso de eliminar</label>
            <label><input type="checkbox" id="consultar"> Permiso de consultar</label>
        </div>
        <div class="botones">
            <button type="submit" id="Agregar_pm" class="AgregarModificar">Agregar/Modificar</button>
            <button type="button" id="Eliminar_pm" class="Eliminar">Eliminar</button>
            <button type="button" id="Limpiar_pm">Limpiar</button>
        </div>
    </form>
    <br><br>
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Perfil</th>
                <th>Módulo</th>
                <th>Insertar</th>
                <th>Actualizar</th>
                <th>Eliminar</th>
                <th>Consultar</th>
            </tr>
        </thead>
        <tbody id="tablaPerfilModulo"></tbody>
    </table>
</div>
