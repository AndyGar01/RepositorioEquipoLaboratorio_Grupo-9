(function(){
const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/";
const equipos = { lista: [], categorias: [], estados: [] };
let equipoSeleccionado = "";
const focoAnteriorModal = new Map();
const $ = selector => document.querySelector(selector);
const escapar = valor => String(valor ?? "").replace(/[&<>"']/g, caracter => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[caracter]));

async function solicitar(archivo, metodo = "GET", datos = null){
    const opciones = {method: metodo, cache: "no-store"};
    if (datos) opciones.body = datos instanceof FormData ? datos : new URLSearchParams(datos);
    const respuesta = await fetch(API + archivo, opciones);
    const contenido = await respuesta.json();
    if (!respuesta.ok || contenido.code !== 1) throw new Error(contenido.message || "No se pudo completar la solicitud.");
    return contenido.data;
}

function llenarOpciones(selector, lista, texto){
    const seleccion = $(selector);
    const valorActual = seleccion.value;
    seleccion.innerHTML = `<option value="">${texto}</option>` + lista.map(item => `<option value="${item.id}">${escapar(item.nombre)}</option>`).join("");
    seleccion.value = valorActual;
}

async function cargarCatalogos(){
    [equipos.categorias, equipos.estados] = await Promise.all([
        solicitar("C_CategoriaEquipo.php"), solicitar("C_EstadoEquipo.php")
    ]);
    llenarOpciones("#id_categoria_equipo", equipos.categorias, "Seleccione una categoría");
    llenarOpciones("#id_estado_equipo", equipos.estados, "Seleccione un estado");
    llenarOpciones("#filtroCategoria", equipos.categorias, "Todas");
    llenarOpciones("#filtroEstado", equipos.estados, "Todos");
    dibujarCatalogos();
}

function dibujarCatalogos(){
    const categoriaSeleccionada = $("#id_categoria").value;
    const estadoSeleccionado = $("#id_estado").value;
    $("#listaCategorias").innerHTML = equipos.categorias.map(item => `<li data-categoria="${item.id}" tabindex="0" role="button" aria-pressed="${String(item.id) === categoriaSeleccionada}">${escapar(item.nombre)}</li>`).join("") || "<li class=\"inventario_catalogo_vacio\">No hay categorías registradas.</li>";
    $("#listaEstados").innerHTML = equipos.estados.map(item => `<li data-estado="${item.id}" tabindex="0" role="button" aria-pressed="${String(item.id) === estadoSeleccionado}">${escapar(item.nombre)}</li>`).join("") || "<li class=\"inventario_catalogo_vacio\">No hay estados registrados.</li>";
}

async function cargarEquipos(){
    try {
        equipos.lista = await solicitar("C_Equipos.php");
        dibujarEquipos();
    } catch (error) {
        $("#mensajeInventario").textContent = error.message;
    }
}

function dibujarEquipos(){
    const consulta = $("#buscarEquipo").value.trim().toLowerCase();
    const categoria = $("#filtroCategoria").value;
    const estado = $("#filtroEstado").value;
    const lista = equipos.lista.filter(item =>
        (!consulta || [item.codigo,item.nombre,item.numero_serie].some(valor => String(valor ?? "").toLowerCase().includes(consulta))) &&
        (!categoria || String(item.id_categoria) === categoria) &&
        (!estado || String(item.id_estado) === estado)
    );
    if (!lista.some(item => String(item.id) === equipoSeleccionado)) equipoSeleccionado = "";
    const activos = equipos.lista.filter(item => Number(item.activo) === 1);
    const existencias = activos.reduce((total, item) => total + Number(item.cantidad_existencias || 0), 0);
    const disponibles = activos.filter(item => Number(item.disponible) === 1).length;
    $("#resumenEquipo").textContent = `${activos.length} registros activos · ${existencias} existencias registradas · ${disponibles} registros disponibles`;
    $("#Gestionar_equipo").disabled = !equipoSeleccionado;
    $("#tablaEquipo").innerHTML = lista.map(item => `<tr data-equipo="${item.id}" tabindex="0" aria-keyshortcuts="Enter Space" aria-selected="${String(item.id) === equipoSeleccionado}" class="${String(item.id) === equipoSeleccionado ? "equipo_seleccionado" : ""}">
        <td>${escapar(item.codigo)}</td><td>${escapar(item.nombre)}</td><td>${escapar(item.categoria)}</td>
        <td>${escapar(item.numero_serie || "Sin número")}</td><td>${Number(item.cantidad_existencias || 0)}</td>
        <td>${escapar(item.estado)}</td><td>${escapar(item.fecha_adquisicion || "—")}</td>
    </tr>`).join("");
    $("#mensajeInventario").textContent = lista.length ? "" : "No hay equipos que coincidan con la búsqueda.";
}

function abrir(id){
    const modal = $(id);
    focoAnteriorModal.set(id, document.activeElement);
    modal.classList.remove("oculto");
    modal.querySelector("button, input, select, [tabindex='0']")?.focus();
}

function cerrar(id, devolverFoco = true){
    $(id).classList.add("oculto");
    const elemento = focoAnteriorModal.get(id);
    focoAnteriorModal.delete(id);
    if (devolverFoco && elemento?.isConnected && !elemento.disabled) elemento.focus();
}
let imagenActualEquipo = "";
function mostrarVistaImagen(ruta, mensaje){
    const rutaValida = /^images\/equipos\/[a-z0-9_-]+\.(svg|png|jpe?g|webp)$/i.test(String(ruta || "")) || String(ruta || "").startsWith("blob:");
    $("#vistaImagenEquipo").src = rutaValida ? ruta : "images/equipos/gradilla.svg";
    $("#vistaImagenEquipo").alt = rutaValida ? "Vista previa de la imagen del equipo" : "Ilustración de referencia del equipo";
    $("#estadoImagenEquipo").textContent = mensaje;
}

function limpiarEquipo(){
    $("#formEquipo").reset();
    $("#id_equipo").value = "";
    $("#tituloFormularioEquipo").textContent = "Registrar equipo";
    imagenActualEquipo = "";
    mostrarVistaImagen("", "Si no adjuntas una imagen, el catálogo mostrará una ilustración de referencia.");
}

function mostrarEdicion(item){
    $("#id_equipo").value = item.id;
    $("#id_categoria_equipo").value = item.id_categoria;
    $("#id_estado_equipo").value = item.id_estado;
    $("#codigo_equipo").value = item.codigo;
    $("#nombre_equipo").value = item.nombre;
    $("#descripcion_equipo").value = item.descripcion;
    $("#numero_serie_equipo").value = item.numero_serie || "";
    $("#fecha_adquisicion_equipo").value = item.fecha_adquisicion || "";
    $("#cantidad_existencias").value = item.cantidad_existencias || 1;
    imagenActualEquipo = item.imagen_url || "";
    mostrarVistaImagen(imagenActualEquipo || "", imagenActualEquipo ? "Imagen guardada. Para reemplazarla, selecciona otro archivo." : "Este equipo usa una ilustración de referencia.");
    $("#tituloFormularioEquipo").textContent = "Editar equipo";
    cerrar("#ventanaGestionEquipo");
    abrir("#ventanaEquipo");
    $("#codigo_equipo").focus();
}

$("#Nuevo_equipo").addEventListener("click", () => { limpiarEquipo(); abrir("#ventanaEquipo"); $("#codigo_equipo").focus(); });
$("#Administrar_catalogos").addEventListener("click", () => abrir("#ventanaCatalogos"));
document.querySelectorAll("[data-cerrar]").forEach(boton => boton.addEventListener("click", () => cerrar("#" + boton.dataset.cerrar)));
document.querySelectorAll(".inventario_modal").forEach(modal => modal.addEventListener("click", evento => {
    if (evento.target === modal) cerrar("#" + modal.id);
}));

document.addEventListener("keydown", evento => {
    const modalesAbiertos = document.querySelectorAll(".inventario_modal:not(.oculto)");
    const modal = modalesAbiertos[modalesAbiertos.length - 1];
    if (!modal) return;
    if (evento.key === "Escape") {
        cerrar("#" + modal.id);
        return;
    }
    if (evento.key !== "Tab") return;
    const elementos = [...modal.querySelectorAll("button:not(:disabled), input:not(:disabled), select:not(:disabled), [tabindex='0']")]
        .filter(elemento => elemento.offsetParent !== null);
    if (!elementos.length) return;
    const primero = elementos[0];
    const ultimo = elementos[elementos.length - 1];
    if (evento.shiftKey && document.activeElement === primero) {
        evento.preventDefault();
        ultimo.focus();
    } else if (!evento.shiftKey && document.activeElement === ultimo) {
        evento.preventDefault();
        primero.focus();
    }
});
["#buscarEquipo", "#filtroCategoria", "#filtroEstado"].forEach(selector => $(selector).addEventListener("input", dibujarEquipos));

$("#formEquipo").addEventListener("submit", async evento => {
    evento.preventDefault();
    const datos = new FormData();
    const campos = {
        id_categoria: $("#id_categoria_equipo").value, id_estado: $("#id_estado_equipo").value,
        codigo: $("#codigo_equipo").value.trim(), nombre: $("#nombre_equipo").value.trim(),
        descripcion: $("#descripcion_equipo").value.trim(), numero_serie: $("#numero_serie_equipo").value.trim(),
        fecha_adquisicion: $("#fecha_adquisicion_equipo").value,
        cantidad_existencias: $("#cantidad_existencias").value
    };
    Object.entries(campos).forEach(([nombre, valor]) => datos.append(nombre, valor));
    try {
        const id = $("#id_equipo").value;
        if (id) {
            datos.append("id", id);
            datos.append("_method", "PUT");
        }
        const imagen = $("#imagen_equipo").files[0];
        if (imagen) datos.append("imagen", imagen);
        await solicitar("C_Equipos.php", "POST", datos);
        cerrar("#ventanaEquipo"); limpiarEquipo(); await cargarEquipos();
    } catch (error) { alert(error.message); }
});

$("#imagen_equipo").addEventListener("change", () => {
    const imagen = $("#imagen_equipo").files[0];
    if (!imagen) {
        mostrarVistaImagen(imagenActualEquipo, imagenActualEquipo ? "Imagen guardada. Si no eliges otra, se conservará." : "Si no adjuntas una imagen, el catálogo mostrará una ilustración de referencia.");
        return;
    }
    if (imagen.size > 5 * 1024 * 1024) {
        $("#imagen_equipo").value = "";
        mostrarVistaImagen(imagenActualEquipo, imagenActualEquipo ? "Imagen guardada. Si no eliges otra, se conservará." : "Si no adjuntas una imagen, el catálogo mostrará una ilustración de referencia.");
        alert("La imagen no debe superar los 5 MB.");
        return;
    }
    const vista = URL.createObjectURL(imagen);
    mostrarVistaImagen(vista, "Vista previa: " + imagen.name);
    $("#vistaImagenEquipo").addEventListener("load", () => URL.revokeObjectURL(vista), {once: true});
});

$("#Limpiar_equipo").addEventListener("click", limpiarEquipo);
function seleccionarFila(fila, devolverEnfoque = false){
    if (!fila) return;
    equipoSeleccionado = fila.dataset.equipo;
    document.querySelectorAll("#tablaEquipo tr[data-equipo]").forEach(filaEquipo => {
        const seleccionada = filaEquipo.dataset.equipo === equipoSeleccionado;
        filaEquipo.classList.toggle("equipo_seleccionado", seleccionada);
        filaEquipo.setAttribute("aria-selected", seleccionada);
    });
    $("#Gestionar_equipo").disabled = false;
    if (devolverEnfoque) fila.focus();
}

$("#tablaEquipo").addEventListener("click", evento => {
    seleccionarFila(evento.target.closest("tr[data-equipo]"));
});

$("#tablaEquipo").addEventListener("keydown", evento => {
    if (evento.key !== "Enter" && evento.key !== " ") return;
    const fila = evento.target.closest("tr[data-equipo]");
    if (!fila) return;
    evento.preventDefault();
    seleccionarFila(fila, true);
});

$("#Gestionar_equipo").addEventListener("click", () => {
    const item = equipos.lista.find(equipo => String(equipo.id) === equipoSeleccionado);
    if (!item) return;
    $("#detalleEquipoSeleccionado").textContent = `${item.codigo} · ${item.nombre} · ${item.cantidad_existencias} existencias`;
    $("#Baja_equipo").disabled = Number(item.activo) !== 1;
    abrir("#ventanaGestionEquipo");
    $("#Editar_equipo").focus();
});

$("#Editar_equipo").addEventListener("click", () => {
    const item = equipos.lista.find(equipo => String(equipo.id) === equipoSeleccionado);
    if (item) mostrarEdicion(item);
});

$("#Baja_equipo").addEventListener("click", async () => {
    if (!confirm("¿Dar de baja este equipo? El registro se conservará en el historial.")) return;
    try {
        await solicitar("C_Equipos.php", "DELETE", {id: equipoSeleccionado});
        cerrar("#ventanaGestionEquipo", false); equipoSeleccionado = ""; await cargarEquipos(); $("#Nuevo_equipo").focus();
    } catch (error) { alert(error.message); }
});

$("#formCategoria").addEventListener("submit", async evento => {
    evento.preventDefault();
    const id = $("#id_categoria").value;
    const datos = {nombre: $("#nombre_categoria").value.trim(), descripcion: $("#descripcion_categoria").value.trim()};
    try { await solicitar("C_CategoriaEquipo.php", id ? "PUT" : "POST", id ? {id, ...datos} : datos); limpiarCategoria(); await cargarCatalogos(); await cargarEquipos(); }
    catch (error) { alert(error.message); }
});

$("#formEstado").addEventListener("submit", async evento => {
    evento.preventDefault();
    const id = $("#id_estado").value;
    const datos = {nombre: $("#nombre_estado").value.trim()};
    try { await solicitar("C_EstadoEquipo.php", id ? "PUT" : "POST", id ? {id, ...datos} : datos); limpiarEstado(); await cargarCatalogos(); await cargarEquipos(); }
    catch (error) { alert(error.message); }
});

function limpiarCategoria(){ $("#formCategoria").reset(); $("#id_categoria").value = ""; dibujarCatalogos(); }
function limpiarEstado(){ $("#formEstado").reset(); $("#id_estado").value = ""; dibujarCatalogos(); }

function seleccionarCategoria(id){
    const item = equipos.categorias.find(categoria => String(categoria.id) === id);
    if (!item) return;
    $("#id_categoria").value = item.id;
    $("#nombre_categoria").value = item.nombre;
    $("#descripcion_categoria").value = item.descripcion || "";
    dibujarCatalogos();
}

function seleccionarEstado(id){
    const item = equipos.estados.find(estado => String(estado.id) === id);
    if (!item) return;
    $("#id_estado").value = item.id;
    $("#nombre_estado").value = item.nombre;
    dibujarCatalogos();
}

function conectarSeleccionLista(selector, atributo, seleccionar){
    const lista = $(selector);
    lista.addEventListener("click", evento => {
        const fila = evento.target.closest(`[${atributo}]`);
        if (fila) seleccionar(fila.getAttribute(atributo));
    });
    lista.addEventListener("keydown", evento => {
        if (evento.key !== "Enter" && evento.key !== " ") return;
        const fila = evento.target.closest(`[${atributo}]`);
        if (!fila) return;
        evento.preventDefault();
        seleccionar(fila.getAttribute(atributo));
    });
}

conectarSeleccionLista("#listaCategorias", "data-categoria", seleccionarCategoria);
conectarSeleccionLista("#listaEstados", "data-estado", seleccionarEstado);
$("#Limpiar_categoria").addEventListener("click", limpiarCategoria);
$("#Limpiar_estado").addEventListener("click", limpiarEstado);

$("#Eliminar_categoria").addEventListener("click", async () => {
    const id = $("#id_categoria").value;
    if (!id) return alert("Selecciona una categoría de la lista.");
    if (!confirm(`¿Eliminar la categoría ${$("#nombre_categoria").value}?`)) return;
    try { await solicitar("C_CategoriaEquipo.php", "DELETE", {id}); limpiarCategoria(); await cargarCatalogos(); await cargarEquipos(); }
    catch (error) { alert(error.message); }
});

$("#Eliminar_estado").addEventListener("click", async () => {
    const id = $("#id_estado").value;
    if (!id) return alert("Selecciona un estado de la lista.");
    if (!confirm(`¿Eliminar el estado ${$("#nombre_estado").value}?`)) return;
    try { await solicitar("C_EstadoEquipo.php", "DELETE", {id}); limpiarEstado(); await cargarCatalogos(); await cargarEquipos(); }
    catch (error) { alert(error.message); }
});

Promise.all([cargarCatalogos(), cargarEquipos()]).catch(error => { $("#mensajeInventario").textContent = error.message; });
})();
