<h1 class="titulo">Mantenimiento de Alumnos</h1><br>
<form id="formAlumno" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_alumno">
        <input id="id_carrera_alumno" placeholder="Id de la Carrera">
        <input id="id_estado_alumno" placeholder="Id del Estado">
        <input id="carne_alumno" placeholder="Carnet" maxlength="10">
        <input id="nombre_alumno" placeholder="Nombre">
        <input id="apellido_alumno" placeholder="Apellido">
        <input type="email" id="correo_alumno" placeholder="Correo">
        <input type="date" id="fecha_nacimiento_alumno" title="Fecha de nacimiento">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_alumno">Agregar/Modificar</button>
        <button type="button" id="Eliminar_alumno">Eliminar</button>
        <button type="button" id="Limpiar_alumno">Limpiar</button>
    </div>
</form>
<br><br>
<table border=1 class="contenido">
    <thead>
        <tr>
            <th>Id</th>
            <th>Id Carrera</th>
            <th>Id Estado</th>
            <th>Carne</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Correo</th>
            <th>Fecha Nacimiento</th>
        </tr>
    </thead>
    <tbody id="tablaAlumno"></tbody>
</table>