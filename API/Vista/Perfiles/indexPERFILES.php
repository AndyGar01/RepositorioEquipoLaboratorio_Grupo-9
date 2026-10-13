<h1 class="titulo">Mantenimiento de Perfiles</h1><br>
<form id="formPerfiles" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_perfil">
        <input id="nombre_perfil" placeholder="Nombre">
        <input id="descripcion_perfil" placeholder="Descripción">
        <input id="estado_perfil" placeholder="Estado">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_perfil">Agregar/Modificar</button>
        <button type="button" id="Eliminar_perfil">Eliminar</button>
        <button type="button" id="Limpiar_perfil">Limpiar</button>
    </div>
</form>
<br><br>
<table border=1 class="contenido">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody id="tablaPerfil"></tbody>
</table>