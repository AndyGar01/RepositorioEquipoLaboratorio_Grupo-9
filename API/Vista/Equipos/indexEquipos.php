<div id="contenidoModulo" data-modulo="6">
    <h1 class="titulo">Mantenimiento de Equipos</h1><br>
    <form id="formEquipo" class="formulario">
        <div class="campos">
            <input type="hidden" id="id_equipo" class="idRegistro">
            <select id="id_categoria_equipo">
                <option value="">Seleccione una categoría</option>
            </select>
            <select id="id_estado_equipo">
                <option value="">Seleccione un estado</option>
            </select>
            <input id="codigo_equipo" placeholder="Código" maxlength="50">
            <input id="nombre_equipo" placeholder="Nombre" maxlength="50">
            <input id="descripcion_equipo" placeholder="Descripción" maxlength="150">
            <input id="numero_serie_equipo" placeholder="Número de serie (opcional)" maxlength="100">
            <input type="date" id="fecha_adquisicion_equipo" title="Fecha de adquisición (opcional)">
            <input id="imagen_url_equipo" placeholder="URL de imagen (opcional)" maxlength="500">
        </div>
        <div class="botones">
            <button type="submit" id="Agregar_equipo" class="AgregarModificar">Agregar/Modificar</button>
            <button type="button" id="Eliminar_equipo" class="Eliminar">Eliminar</button>
            <button type="button" id="Limpiar_equipo">Limpiar</button>
        </div>
    </form>
    <br><br>
    <table border=1 class="contenido">
        <thead>
            <tr>
                <th>Id</th>
                <th>Id Categoria</th>
                <th>Id Estado</th>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Numero Serie</th>
                <th>Fecha Adquisicion</th>
                <th>Imagen URL</th>
            </tr>
        </thead>
        <tbody id="tablaEquipo"></tbody>
    </table>
</div>