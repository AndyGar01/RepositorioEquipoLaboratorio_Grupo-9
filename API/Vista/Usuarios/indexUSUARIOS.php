<h1 class="titulo">Mantenimiento de Usuarios</h1><br>
<form id="form" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_usuario">
        <select id="id_per_usuario">
            <option value="">Seleccione un perfil</option>
        </select>
        <input id="carne_usuario" placeholder="Carnet">
        <input id="nombre_usuario" placeholder="Nombre">
        <input id="apellido_usuario" placeholder="Apellido">
        <input id="correo_usuario" placeholder="Correo">
        <input type="password" id="contrasena_usuario" placeholder="Contraseña">
        <input id="estado_usuario" placeholder="Estado">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_usuario">Agregar/Modificar</button>
        <button type="button" id="Eliminar_usuario">Eliminar</button>
        <button type="button" id="Limpiar_usuario">Limpiar</button>
    </div>
</form>
<br><br>
<table border=1 class="contenido">
    <thead>
        <tr>
            <th>Id</th>
            <th>Id Perfil</th>
            <th>Carne</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Fecha Creacion</th>
        </tr>
    </thead>
    <tbody id="tabla"></tbody>
</table>
