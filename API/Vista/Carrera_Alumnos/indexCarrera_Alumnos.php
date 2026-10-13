<h1 class="titulo">Mantenimiento de Carreras</h1><br>
<form id="formCarrera" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_carrera">
        <input id="nombre_carrera" placeholder="Nombre">
        <input id="descripcion_carrera" placeholder="Descripcion">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_carrera">Agregar/Modificar</button>
        <button type="button" id="Eliminar_carrera">Eliminar</button>
        <button type="button" id="Limpiar_carrera">Limpiar</button>
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
    <tbody id="tablaCarrera"></tbody>
</table>