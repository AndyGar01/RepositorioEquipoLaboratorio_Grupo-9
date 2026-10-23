<h1 class="titulo">Mantenimiento de Módulos</h1><br>
<form id="formModulo" class="formulario">
    <div class="campos">
        <input type="hidden" id="id_modulo">
        <input id="nombre_modulo" placeholder="Nombre">
        <input id="descripcion_modulo" placeholder="Descripción">
    </div>
    <div class="botones">
        <button type="submit" id="Agregar_modulo" class="AgregarModificar">Agregar/Modificar</button>
        <button type="button" id="Eliminar_modulo" class="Eliminar">Eliminar</button>
        <button type="button" id="Limpiar_modulo">Limpiar</button>
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
    <tbody id="tablaModulo"></tbody>
</table>