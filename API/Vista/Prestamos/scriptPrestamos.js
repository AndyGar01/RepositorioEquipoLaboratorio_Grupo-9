(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Prestamos.php";
const API_ALUMNO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Alumnos.php";
const API_EQUIPO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Equipos.php";
let prestamos = [];

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

async function LlenarEquipos(){
    try {
        let lista = (await accionPrestamo(API_EQUIPO, "GET")).data;
        let opciones = "";
        for (let i = 0; i < lista.length; i++){
            if (lista[i].activo == 1 && lista[i].disponible == 1){
                opciones += `<label><input type="checkbox" name="equipo" value="${lista[i].id}" style="width:auto;padding:0"> ${lista[i].codigo} - ${lista[i].nombre}</label>`;
            }
        }
        $("#listaEquipos").innerHTML = opciones || "<span>No hay equipos disponibles.</span>";
    } catch (error) {
        console.error("Error al cargar equipos:", error);
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
        let equipos = [...document.querySelectorAll("#listaEquipos input[name='equipo']:checked")].map(e => e.value);
        let datos = {
            id_alumno: $("#id_alumno_prestamo").value,
            fecha_devolucion_esperada: $("#fecha_devolucion_prestamo").value,
            equipos: equipos.join(",")
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formPrestamo").reset();
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
    document.querySelectorAll("#listaEquipos input[name='equipo']").forEach(e => e.checked = false);
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

LimitarFecha();
LlenarAlumnos();
LlenarEquipos();
Cargar_Prestamos();

})();
