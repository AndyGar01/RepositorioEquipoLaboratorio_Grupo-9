(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Prestamos.php";
const API_ALUMNO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Alumnos.php";
let prestamos = [];
const equipos = { lista: [], categorias: [] };
let elegidos = {};

async function accionPrestamo(direccion, metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

async function LlenarAlumnos(){
    try {
        let lista = (await accionPrestamo(API_ALUMNO, "GET")).data;
        let opciones = `<option value="">Seleccione un alumno</option>`;
        for (let i = 0; i < lista.length; i++){
            opciones += `<option value="${lista[i].id}">${lista[i].carne} - ${lista[i].nombre} ${lista[i].apellido}</option>`;
        }
        $("#id_alumno_prestamo").innerHTML = opciones;
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
    seleccion.innerHTML = `<option value="">${texto}</option>` + lista.map(item => `<option value="${item.id}">${item.nombre}</option>`).join("");
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
        <td><input type="checkbox" name="equipo" value="${item.id}" ${agotado} ${marcado} style="width:auto;padding:0"></td>
        <td>${item.codigo}</td><td>${item.nombre}</td><td>${item.categoria}</td>
        <td>${item.cantidad_existencias}</td><td>${item.cantidad_disponible}</td>
        <td><input type="number" name="cantidad" min="1" max="${item.cantidad_disponible}" value="${elegidos[item.id] ?? 1}" ${agotado} style="width:70px"></td>
        </tr>`;
    }).join("");
    $("#mensajeEquiposPrestamo").textContent = coincidencias.length ? "" : "No hay equipos que coincidan con la búsqueda.";
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
            <td>${prestamos[i].alumno}</td>
            <td>${prestamos[i].equipos}</td>
            <td>${prestamos[i].estado}</td>
            <td>${prestamos[i].fecha_prestamo}</td>
            <td>${prestamos[i].fecha_devolucion_esperada}</td>
            </tr>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error); 
        alert("No se pudieron cargar los datos :(");
    }
}

async function Guardar_Prestamo(evento){
    evento.preventDefault();
    try {
        let lineas = Object.keys(elegidos).map(id => id + ":" + elegidos[id]);
        let datos = {
            id_alumno: $("#id_alumno_prestamo").value,
            fecha_devolucion_esperada: $("#fecha_devolucion_prestamo").value,
            equipos: lineas.join(",")
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formPrestamo").reset();
            elegidos = {};
            dibujarEquipos();
        } else {
            await accionPrestamo(API, "POST", datos);
            alert(`Se registro el prestamo exitosamente`);
            $("#formPrestamo").reset();
            LlenarEquipos();
            Cargar_Prestamos();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

function Limpiar(){
    $("#id_alumno_prestamo").value = "";
    $("#fecha_devolucion_prestamo").value = "";
    $("#buscarEquipoPrestamo").value = "";
    $("#filtroCategoriaPrestamo").value = "";
    $("#filtroDisponiblePrestamo").value = "";
    elegidos = {};
    dibujarEquipos();
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
["#buscarEquipoPrestamo", "#filtroCategoriaPrestamo", "#filtroDisponiblePrestamo"].forEach(selector => $(selector).addEventListener("input", dibujarEquipos));
$("#tablaEquiposPrestamo").addEventListener("input", ElegirEquipo);

LimitarFecha();
LlenarAlumnos();
LlenarEquipos();
Cargar_Prestamos();

})();
