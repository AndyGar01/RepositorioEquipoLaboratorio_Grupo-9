(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Prestamos.php";
const API_ALUMNO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Alumnos.php";
const API_SOLICITUDES = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Solicitudes.php";
let prestamos = [];
let solicitudesPendientes = [];
let solicitudSeleccionada = "";
const equipos = { lista: [], categorias: [] };
let elegidos = {};

function escaparHTML(texto){
    return String(texto ?? "").replace(/[&<>"']/g, caracter => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    })[caracter]);
}

async function accionPrestamo(direccion, metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    opciones.credentials = "same-origin";
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    if (!solicitud.ok) throw new Error(respuesta.message || "No se pudo completar la solicitud.");
    return respuesta;
}

async function LlenarAlumnos(){
    try {
        let lista = (await accionPrestamo(API_ALUMNO, "GET")).data;
        let opciones = `<option value="">Seleccione un alumno</option>`;
        for (let i = 0; i < lista.length; i++){
            opciones += `<option value="${Number(lista[i].id)}">${escaparHTML(lista[i].carne)} - ${escaparHTML(lista[i].nombre)} ${escaparHTML(lista[i].apellido)}</option>`;
        }
        $("#id_alumno_prestamo").innerHTML = opciones;
        actualizarResumenPrestamo();
    } catch (error) {
        console.error("Error al cargar alumnos:", error);
    }
}

function SinTildes(texto){
    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

function llenarOpciones(selector, lista, texto){
    const seleccion = $(selector);
    const valorActual = seleccion.value;
    seleccion.innerHTML = `<option value="">${escaparHTML(texto)}</option>` + lista.map(item => `<option value="${Number(item.id)}">${escaparHTML(item.nombre)}</option>`).join("");
    seleccion.value = valorActual;
}

async function LlenarEquipos(){
    try {
        equipos.lista = (await accionPrestamo(API + "?equipos=1", "GET")).data;
        let categorias = {};
        equipos.lista.forEach(item => categorias[item.id_categoria] = item.categoria);
        equipos.categorias = Object.keys(categorias).map(id => ({ id: id, nombre: categorias[id] }));
        llenarOpciones("#filtroCategoriaPrestamo", equipos.categorias, "Todas");
        elegidos = {};
        dibujarEquipos();
    } catch (error) {
        console.error("Error al cargar equipos:", error);
    }
}

function dibujarEquipos(){
    const consulta = SinTildes($("#buscarEquipoPrestamo").value.trim());
    const categoria = $("#filtroCategoriaPrestamo").value;
    const disponible = $("#filtroDisponiblePrestamo").value;
    const coincidencias = equipos.lista.filter(item =>
        (!consulta || [item.codigo, item.nombre].some(valor => SinTildes(String(valor ?? "")).includes(consulta))) &&
        (!categoria || String(item.id_categoria) === categoria) &&
        (!disponible || String(Number(item.cantidad_disponible) > 0 ? 1 : 0) === disponible)
    );
    const lista = equipos.lista.filter(item => coincidencias.includes(item) || elegidos[item.id] !== undefined);
    $("#tablaEquiposPrestamo").innerHTML = lista.map(item => {
        const agotado = Number(item.cantidad_disponible) < 1 ? "disabled" : "";
        const marcado = elegidos[item.id] !== undefined ? "checked" : "";
        return `<tr data-id="${item.id}">
        <td><input type="checkbox" name="equipo" value="${item.id}" aria-label="Incluir ${escaparHTML(item.nombre)}" ${agotado} ${marcado}></td>
        <td>${escaparHTML(item.codigo)}</td><td>${escaparHTML(item.nombre)}</td><td>${escaparHTML(item.categoria)}</td>
        <td>${item.cantidad_existencias}</td><td>${item.cantidad_disponible}</td>
        <td><input type="number" class="prestamo_cantidad" name="cantidad" min="1" max="${item.cantidad_disponible}" value="${elegidos[item.id] ?? 1}" aria-label="Cantidad de ${escaparHTML(item.nombre)}" ${agotado}></td>
        </tr>`;
    }).join("");
    $("#mensajeEquiposPrestamo").textContent = coincidencias.length ? "" : "No hay equipos que coincidan con la búsqueda.";
    actualizarResumenPrestamo();
}

function actualizarResumenPrestamo(){
    const tipos = Object.keys(elegidos).length;
    const unidades = Object.values(elegidos).reduce((total, cantidad) => total + (Number(cantidad) || 0), 0);
    const alumno = Boolean($("#id_alumno_prestamo").value);
    const fecha = Boolean($("#fecha_devolucion_prestamo").value);
    const faltantes = [!alumno ? "selecciona al alumno" : "", !fecha ? "indica la fecha" : ""].filter(Boolean).join(" e ");
    $("#resumenPrestamo").textContent = tipos
        ? `${tipos} ${tipos === 1 ? "equipo" : "tipos de equipo"} · ${unidades} ${unidades === 1 ? "unidad seleccionada" : "unidades seleccionadas"}.${faltantes ? ` También ${faltantes} para continuar.` : ""}`
        : "Elige un alumno, indica la fecha y selecciona al menos un equipo disponible.";
    $("#Agregar_prestamo").disabled = !alumno || !fecha || tipos === 0;
}

function ElegirEquipo(evento){
    const fila = evento.target.closest("tr[data-id]");
    if (!fila){
        return;
    }
    if (fila.querySelector("input[name='equipo']").checked){
        elegidos[fila.dataset.id] = fila.querySelector("input[name='cantidad']").value;
    } else {
        delete elegidos[fila.dataset.id];
    }
    actualizarResumenPrestamo();
}

async function Cargar_Prestamos(){
    try {
        let tabla = $("#tablaPrestamo");
        let respuesta = await accionPrestamo(API, "GET");
        prestamos = respuesta.data;
        let filas = "";
        for (let i=0; i<prestamos.length; i++){
            filas += `<tr data-id="${prestamos[i].id}">
            <td>${prestamos[i].id}</td>
            <td>${escaparHTML(prestamos[i].alumno)}</td>
            <td>${escaparHTML(prestamos[i].equipos)}</td>
            <td>${escaparHTML(prestamos[i].estado)}</td>
            <td>${escaparHTML(prestamos[i].fecha_prestamo)}</td>
            <td>${escaparHTML(prestamos[i].fecha_devolucion_esperada)}</td>
            </tr>`;
        }
        tabla.innerHTML = filas;
        $("#mensajePrestamos").textContent = prestamos.length ? "" : "No hay préstamos registrados.";
    } catch (error) {
        console.error("Error en Cargar:", error); 
        alert("No se pudieron cargar los datos :(");
    }
}

async function Cargar_Solicitudes_Pendientes(){
    try {
        solicitudesPendientes = (await accionPrestamo(API_SOLICITUDES, "GET")).data;
        const filas = solicitudesPendientes.map(solicitud => `<tr data-solicitud="${Number(solicitud.id)}" tabindex="0" aria-selected="${String(solicitud.id) === String(solicitudSeleccionada)}">
            <td>${Number(solicitud.id)}</td><td>${escaparHTML(solicitud.alumno)}</td><td>${escaparHTML(solicitud.carne)}</td><td>${escaparHTML(solicitud.equipos)}</td>
            <td>${escaparHTML(solicitud.fecha_solicitud)}</td><td>${escaparHTML(solicitud.fecha_devolucion_esperada)}</td></tr>`).join("");
        $("#tablaSolicitudesPendientes").innerHTML = filas;
        $("#mensajeSolicitudesPendientes").textContent = filas ? "" : "No hay solicitudes pendientes.";
        actualizarAccionesSolicitud();
    } catch (error) {
        $("#mensajeSolicitudesPendientes").textContent = error.message;
    }
}

function actualizarAccionesSolicitud(){
    const habilitar = solicitudesPendientes.some(solicitud => String(solicitud.id) === String(solicitudSeleccionada));
    $("#Aprobar_solicitud").disabled = !habilitar;
    $("#Rechazar_solicitud").disabled = !habilitar;
}

function seleccionarSolicitud(fila){
    if (!fila) return;
    solicitudSeleccionada = fila.dataset.solicitud;
    $("#tablaSolicitudesPendientes").querySelectorAll("tr[data-solicitud]").forEach(filaActual => {
        const seleccionada = filaActual === fila;
        filaActual.setAttribute("aria-selected", seleccionada ? "true" : "false");
        filaActual.classList.toggle("solicitud-seleccionada", seleccionada);
    });
    actualizarAccionesSolicitud();
}

async function ResolverSolicitud(accion){
    const solicitud = solicitudesPendientes.find(item => String(item.id) === String(solicitudSeleccionada));
    if (!solicitud) return alert("Selecciona una solicitud de la lista.");
    const verbo = accion === "aprobar" ? "aprobar" : "rechazar";
    if (!confirm(`¿Deseas ${verbo} la solicitud de ${solicitud.alumno}?`)) return;
    try {
        const respuesta = await accionPrestamo(API_SOLICITUDES, "PUT", {id: solicitud.id, accion});
        alert(respuesta.message);
        solicitudSeleccionada = "";
        await Promise.all([Cargar_Solicitudes_Pendientes(), Cargar_Prestamos(), LlenarEquipos()]);
    } catch (error) {
        alert(error.message);
    }
}

async function Guardar_Prestamo(evento){
    evento.preventDefault();
    const alumno = $("#id_alumno_prestamo").value;
    const fecha = $("#fecha_devolucion_prestamo").value;
    if (!alumno || !fecha || Object.keys(elegidos).length === 0){
        $("#mensajeFormularioPrestamo").textContent = !alumno
            ? "Selecciona al alumno que recibirá el equipo."
            : !fecha
                ? "Indica la fecha esperada de devolución."
                : "Selecciona al menos un equipo disponible.";
        if (!alumno) $("#id_alumno_prestamo").focus();
        else if (!fecha) $("#fecha_devolucion_prestamo").focus();
        else $("#buscarEquipoPrestamo").focus();
        return;
    }
    try {
        let lineas = Object.keys(elegidos).map(id => id + ":" + elegidos[id]);
        let datos = {
            id_alumno: alumno,
            fecha_devolucion_esperada: fecha,
            equipos: lineas.join(",")
        }
        $("#Agregar_prestamo").disabled = true;
        const respuesta = await accionPrestamo(API, "POST", datos);
        $("#mensajeFormularioPrestamo").textContent = respuesta.message || "Préstamo registrado correctamente.";
        $("#id_alumno_prestamo").value = "";
        $("#fecha_devolucion_prestamo").value = "";
        $("#buscarEquipoPrestamo").value = "";
        $("#filtroCategoriaPrestamo").value = "";
        $("#filtroDisponiblePrestamo").value = "";
        elegidos = {};
        await Promise.all([LlenarEquipos(), Cargar_Prestamos()]);
    } catch (error) {
        $("#mensajeFormularioPrestamo").textContent = error.message;
    } finally {
        actualizarResumenPrestamo();
    }
}

function Limpiar(){
    $("#id_alumno_prestamo").value = "";
    $("#fecha_devolucion_prestamo").value = "";
    $("#buscarEquipoPrestamo").value = "";
    $("#filtroCategoriaPrestamo").value = "";
    $("#filtroDisponiblePrestamo").value = "";
    elegidos = {};
    $("#mensajeFormularioPrestamo").textContent = "";
    dibujarEquipos();
    actualizarResumenPrestamo();
}

function LimitarFecha(){
    let hoy = new Date();
    let limite = new Date();
    limite.setDate(hoy.getDate() + 30);
    $("#fecha_devolucion_prestamo").min = hoy.toISOString().slice(0, 10);
    $("#fecha_devolucion_prestamo").max = limite.toISOString().slice(0, 10);
}

$("#formPrestamo").addEventListener("submit", Guardar_Prestamo);
$("#Limpiar_prestamo").addEventListener("click", () => Limpiar());
$("#id_alumno_prestamo").addEventListener("change", actualizarResumenPrestamo);
$("#fecha_devolucion_prestamo").addEventListener("change", actualizarResumenPrestamo);
["#buscarEquipoPrestamo", "#filtroCategoriaPrestamo", "#filtroDisponiblePrestamo"].forEach(selector => $(selector).addEventListener("input", dibujarEquipos));
$("#tablaEquiposPrestamo").addEventListener("input", ElegirEquipo);
$("#tablaSolicitudesPendientes").addEventListener("click", evento => seleccionarSolicitud(evento.target.closest("tr[data-solicitud]")));
$("#tablaSolicitudesPendientes").addEventListener("keydown", evento => {
    if (evento.key !== "Enter" && evento.key !== " ") return;
    const fila = evento.target.closest("tr[data-solicitud]");
    if (!fila) return;
    evento.preventDefault();
    seleccionarSolicitud(fila);
});
$("#Aprobar_solicitud").addEventListener("click", () => ResolverSolicitud("aprobar"));
$("#Rechazar_solicitud").addEventListener("click", () => ResolverSolicitud("rechazar"));

LimitarFecha();
LlenarAlumnos();
LlenarEquipos();
Cargar_Prestamos();
Cargar_Solicitudes_Pendientes();

})();
