<h1 class="titulo">Mantenimiento de Permisos</h1><br>
<form id="formPermiso" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_permiso">
        <input id="nombre_permiso" placeholder="Nombre">
        <input id="descripcion_permiso" placeholder="Descripcion">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_permiso" class="AgregarModificar">Agregar/Modificar</button>
        <button type="button" id="Eliminar_permiso" class="Eliminar">Eliminar</button>
        <button type="button" id="Limpiar_permiso">Limpiar</button>
    </div>
</form>
<br><br>
<table border=1 class="contenido">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Descripcion</th>
        </tr>
    </thead>
    <tbody id="tablaPermiso"></tbody>
</table>