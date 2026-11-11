(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Devoluciones.php";
const API_CONDICION = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Estado_Condicion_Devolucion.php";
let pendientes = [];
let condiciones = [];
let prestamo = null;

async function accionDevolucion(direccion, metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

function SinTildes(texto){
    return texto.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
}

function Quetzales(valor){
    return "Q" + Number(valor).toFixed(2);
}

async function LlenarCondiciones(){
    try {
        let lista = (await accionDevolucion(API_CONDICION, "GET")).data;
        condiciones = lista.filter(item => ["danado", "incompleto"].includes(SinTildes(item.nombre)));
    } catch (error) {
        console.error("Error al cargar condiciones:", error);
    }
}

async function Cargar_Pendientes(){
    try {
        let respuesta = await accionDevolucion(API, "GET");
        pendientes = respuesta.data;
        let opciones = `<option value="">Seleccione un préstamo</option>`;
        let filas = "";
        for (let i=0; i<pendientes.length; i++){
            opciones += `<option value="${pendientes[i].id}">${pendientes[i].id} - ${pendientes[i].alumno}</option>`;
            filas += `<tr data-id="${pendientes[i].id}">
            <td>${pendientes[i].id}</td>
            <td>${pendientes[i].alumno}</td>
            <td>${pendientes[i].equipos}</td>
            <td>${pendientes[i].fecha_prestamo}</td>
            <td>${pendientes[i].fecha_devolucion_esperada}</td>
            </tr>`;
        }
        $("#id_prestamo_devolucion").innerHTML = opciones;
        $("#tablaDevolucion").innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error);
        alert("No se pudieron cargar los datos :(");
    }
}

async function Elegir_Prestamo(){
    let id = $("#id_prestamo_devolucion").value;
    if (id == ""){
        prestamo = null;
        dibujarEquipos();
        return;
    }
    try {
        let respuesta = await accionDevolucion(API + "?id_prestamo=" + id, "GET");
        if (respuesta.code !== 1){
            alert(respuesta.message);
            Limpiar();
            return;
        }
        prestamo = respuesta.data;
        dibujarEquipos();
    } catch (error) {
        console.error("Error al cargar el préstamo:", error);
    }
}

function dibujarEquipos(){
    if (prestamo === null){
        $("#tablaEquiposDevolucion").innerHTML = "";
        $("#infoPrestamoDevolucion").textContent = "";
        $("#totalDevolucion").textContent = "";
        return;
    }
    let opciones = `<option value="">Seleccione</option>` + condiciones.map(item => `<option value="${item.id}">${item.nombre}</option>`).join("");
    $("#tablaEquiposDevolucion").innerHTML = prestamo.equipos.map(item => `<tr data-id="${item.id_detalle}">
        <td>${item.codigo}</td><td>${item.nombre}</td><td>${item.cantidad_prestada}</td>
        <td><input type="number" name="afectada" min="0" max="${item.cantidad_prestada}" value="0" style="width:70px"></td>
        <td><select name="condicion" disabled>${opciones}</select></td>
        </tr>`).join("");
    let retraso = Number(prestamo.prestamo.dias_retraso);
    $("#infoPrestamoDevolucion").textContent = `Alumno: ${prestamo.prestamo.alumno} | Fecha esperada: ${prestamo.prestamo.fecha_devolucion_esperada} | ` + (retraso > 0 ? `Entrega con ${retraso} día(s) de retraso.` : "Entrega a tiempo.");
    CalcularTotal();
}

function CalcularTotal(){
    if (prestamo === null){
        return;
    }
    let afectadas = 0;
    document.querySelectorAll("#tablaEquiposDevolucion tr").forEach(fila => {
        let cantidad = Number(fila.querySelector("input[name='afectada']").value) || 0;
        let condicion = fila.querySelector("select[name='condicion']");
        condicion.disabled = cantidad < 1;
        if (cantidad < 1){
            condicion.value = "";
        }
        afectadas += cantidad;
    });
    let mora = Number(prestamo.prestamo.dias_retraso) > 0 ? Number(prestamo.tarifa_mora) * prestamo.equipos.length : 0;
    let dano = Number(prestamo.tarifa_dano) * afectadas;
    $("#totalDevolucion").textContent = `Mora: ${Quetzales(mora)} | Daño: ${Quetzales(dano)} | Total a cobrar: ${Quetzales(mora + dano)}`;
}

async function Guardar_Devolucion(evento){
    evento.preventDefault();
    try {
        if (prestamo === null){
            alert("Seleccione un préstamo para el proceso.");
            return;
        }
        let lineas = [];
        let filas = document.querySelectorAll("#tablaEquiposDevolucion tr");
        for (let i = 0; i < filas.length; i++){
            let afectada = Number(filas[i].querySelector("input[name='afectada']").value) || 0;
            let condicion = filas[i].querySelector("select[name='condicion']").value;
            if (afectada > 0 && condicion == ""){
                alert("Seleccione si el equipo está dañado o incompleto.");
                return;
            }
            lineas.push(filas[i].dataset.id + ":" + afectada + ":" + (condicion || 0));
        }
        let datos = {
            id_prestamo: prestamo.prestamo.id,
            observaciones: $("#observaciones_devolucion").value,
            equipos: lineas.join(",")
        }
        let respuesta = await accionDevolucion(API, "POST", datos);
        if (respuesta.code !== 1){
            alert(respuesta.message);
            return;
        }
        alert(`Se registro la devolución. Total a cobrar: ${Quetzales(respuesta.data.total_cobro)}`);
        Limpiar();
        Cargar_Pendientes();
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

function Limpiar(){
    $("#formDevolucion").reset();
    prestamo = null;
    dibujarEquipos();
}

$("#formDevolucion").addEventListener("submit", Guardar_Devolucion);
$("#Limpiar_devolucion").addEventListener("click", () => Limpiar());
$("#id_prestamo_devolucion").addEventListener("change", Elegir_Prestamo);
$("#tablaEquiposDevolucion").addEventListener("input", CalcularTotal);

LlenarCondiciones();
Cargar_Pendientes();

})();
