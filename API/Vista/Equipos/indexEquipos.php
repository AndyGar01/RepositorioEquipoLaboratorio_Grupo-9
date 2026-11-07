<div id="contenidoModulo" data-modulo="6" class="inventario">
    <div class="inventario_encabezado">
        <div>
            <h1 class="titulo">Inventario de equipos</h1>
            <p>Consulta y administra el equipo médico y de laboratorio.</p>
        </div>
        <div class="inventario_ayuda">
            <img src="images/mascota_vitalab.png" alt="Mascota de VitaLab con una bata y un matraz de laboratorio">
        </div>
    </div>

    <div class="inventario_acciones">
        <button type="button" id="Nuevo_equipo" class="AgregarModificar">＋ Registrar equipo</button>
        <button type="button" id="Administrar_catalogos">Administrar categorías y estados</button>
        <button type="button" id="Gestionar_equipo" disabled>Gestionar selección</button>
    </div>

    <div class="inventario_resumen" id="resumenEquipo" aria-live="polite"></div>
    <p id="ayudaSeleccion" class="inventario_ayuda_seleccion">Selecciona una fila con clic, Enter o espacio; luego usa «Gestionar selección».</p>

    <div class="inventario_filtros">
        <div class="inventario_filtro"><label for="buscarEquipo">Buscar equipo</label><input type="search" id="buscarEquipo" placeholder="Código, nombre o número de serie"></div>
        <div class="inventario_filtro"><label for="filtroCategoria">Categoría</label><select id="filtroCategoria"><option value="">Todas</option></select></div>
        <div class="inventario_filtro"><label for="filtroEstado">Estado</label><select id="filtroEstado"><option value="">Todos</option></select></div>
    </div>

    <div class="inventario_tabla_scroll">
        <table class="contenido inventario_tabla" aria-describedby="ayudaSeleccion">
            <caption class="solo_lectores">Inventario de equipo médico y de laboratorio</caption>
            <thead><tr><th scope="col">Código</th><th scope="col">Equipo</th><th scope="col">Categoría</th><th scope="col">Número de serie</th><th scope="col">Existencias</th><th scope="col">Estado</th><th scope="col">Adquisición</th></tr></thead>
            <tbody id="tablaEquipo"></tbody>
        </table>
    </div>
    <p id="mensajeInventario" class="inventario_mensaje_estado" aria-live="polite"></p>

    <div id="ventanaGestionEquipo" class="inventario_modal oculto" role="dialog" aria-modal="true" aria-labelledby="tituloGestionEquipo">
        <div class="inventario_modal_contenido inventario_gestion">
            <button type="button" class="inventario_cerrar" data-cerrar="ventanaGestionEquipo" aria-label="Cerrar">×</button>
            <h2 id="tituloGestionEquipo">Equipo seleccionado</h2>
            <p id="detalleEquipoSeleccionado"></p>
            <div class="inventario_gestion_acciones">
                <button type="button" id="Editar_equipo" class="AgregarModificar">Editar equipo</button>
                <button type="button" id="Baja_equipo">Dar de baja</button>
            </div>
        </div>
    </div>

    <div id="ventanaEquipo" class="inventario_modal oculto" role="dialog" aria-modal="true" aria-labelledby="tituloFormularioEquipo">
        <div class="inventario_modal_contenido">
            <button type="button" class="inventario_cerrar" data-cerrar="ventanaEquipo" aria-label="Cerrar">×</button>
            <h2 id="tituloFormularioEquipo">Registrar equipo</h2>
            <form id="formEquipo" class="formulario" enctype="multipart/form-data">
                <input type="hidden" id="id_equipo" class="idRegistro">
                <div class="campos">
                    <label>Código<input id="codigo_equipo" maxlength="50" required></label>
                    <label>Nombre<input id="nombre_equipo" maxlength="50" required></label>
                    <label>Categoría<select id="id_categoria_equipo" required><option value="">Seleccione una categoría</option></select></label>
                    <label>Estado<select id="id_estado_equipo" required><option value="">Seleccione un estado</option></select></label>
                    <label>Descripción<input id="descripcion_equipo" maxlength="150" required></label>
                    <label>Existencias<input type="number" id="cantidad_existencias" min="1" step="1" value="1" required></label>
                    <label>Número de serie (opcional)<input id="numero_serie_equipo" maxlength="100"></label>
                    <label>Fecha de adquisición<input type="date" id="fecha_adquisicion_equipo"></label>
                    <label class="inventario_imagen_campo" for="imagen_equipo">Imagen del equipo (opcional)<input type="file" id="imagen_equipo" name="imagen" accept="image/jpeg,image/png,image/webp" aria-describedby="ayudaImagenEquipo"><span id="ayudaImagenEquipo">JPG, PNG o WEBP. Tamaño máximo: 5 MB.</span></label>
                </div>
                <div class="inventario_imagen_previa"><img id="vistaImagenEquipo" src="images/equipos/gradilla.svg" alt="Vista previa de la imagen del equipo"><p id="estadoImagenEquipo">Si no adjuntas una imagen, el catálogo mostrará una ilustración de referencia.</p></div>
                <div class="botones"><button type="submit" id="Guardar_equipo" class="AgregarModificar">Guardar equipo</button><button type="button" id="Limpiar_equipo">Limpiar</button></div>
            </form>
        </div>
    </div>

    <div id="ventanaCatalogos" class="inventario_modal oculto" role="dialog" aria-modal="true" aria-labelledby="tituloCatalogos">
        <div class="inventario_modal_contenido inventario_catalogos">
            <button type="button" class="inventario_cerrar" data-cerrar="ventanaCatalogos" aria-label="Cerrar">×</button>
            <h2 id="tituloCatalogos">Administrar categorías y estados</h2>
            <div class="inventario_catalogo_columnas">
                <section class="inventario_catalogo_columna">
                    <h3>Categorías</h3>
                    <form id="formCategoria">
                        <input type="hidden" id="id_categoria">
                        <label for="nombre_categoria">Nombre</label>
                        <input id="nombre_categoria" maxlength="100" required>
                        <label for="descripcion_categoria">Descripción</label>
                        <input id="descripcion_categoria" maxlength="255">
                        <div class="botones inventario_catalogo_acciones">
                            <button type="submit" id="Guardar_categoria" class="AgregarModificar">Agregar/Modificar</button>
                            <button type="button" id="Eliminar_categoria" class="Eliminar">Eliminar</button>
                            <button type="button" id="Limpiar_categoria">Limpiar</button>
                        </div>
                    </form>
                    <p class="inventario_catalogo_instruccion">Selecciona una categoría de la lista para editarla o eliminarla.</p>
                    <ul id="listaCategorias" class="inventario_catalogo_lista"></ul>
                </section>
                <section class="inventario_catalogo_columna">
                    <h3>Estados de equipo</h3>
                    <form id="formEstado">
                        <input type="hidden" id="id_estado">
                        <label for="nombre_estado">Nombre</label>
                        <input id="nombre_estado" maxlength="50" required>
                        <div class="botones inventario_catalogo_acciones">
                            <button type="submit" id="Guardar_estado" class="AgregarModificar">Agregar/Modificar</button>
                            <button type="button" id="Eliminar_estado" class="Eliminar">Eliminar</button>
                            <button type="button" id="Limpiar_estado">Limpiar</button>
                        </div>
                    </form>
                    <p class="inventario_catalogo_instruccion">Selecciona un estado de la lista para editarlo o eliminarlo.</p>
                    <ul id="listaEstados" class="inventario_catalogo_lista"></ul>
                </section>
            </div>
        </div>
    </div>
</div>
