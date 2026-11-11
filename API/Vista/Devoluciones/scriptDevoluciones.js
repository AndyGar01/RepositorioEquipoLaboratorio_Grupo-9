(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Devoluciones.php";
const API_CONDICION = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Estado_Condicion_Devolucion.php";
let pendientes = [];
let condiciones = [];
let prestamo = null;
let reparaciones = [];
let notificaciones = [];

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

function Escapar(texto){
    return String(texto ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
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
        for (let i=0; i<pendientes.length; i++){
            opciones += `<option value="${Escapar(pendientes[i].id)}">${Escapar(pendientes[i].id)} - ${Escapar(pendientes[i].alumno)} (${Escapar(pendientes[i].equipos)})</option>`;
        }
        $("#id_prestamo_devolucion").innerHTML = opciones;
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
    let opciones = `<option value="">Seleccione</option>` + condiciones.map(item => `<option value="${Escapar(item.id)}">${Escapar(item.nombre)}</option>`).join("");
    $("#tablaEquiposDevolucion").innerHTML = prestamo.equipos.map(item => `<tr data-id="${Escapar(item.id_detalle)}">
        <td>${Escapar(item.codigo)}</td><td>${Escapar(item.nombre)}</td><td>${Escapar(item.cantidad_prestada)}</td>
        <td><input type="number" name="afectada" aria-label="Unidades dañadas o incompletas de ${Escapar(item.nombre)}" min="0" max="${Number(item.cantidad_prestada)}" value="0" style="width:70px"></td>
        <td><select name="condicion" aria-label="Condición de ${Escapar(item.nombre)}" disabled>${opciones}</select></td>
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
        Cargar_Reparaciones();
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Cargar_Reparaciones(){
    try {
        let respuesta = await accionDevolucion(API + "?reparaciones=1", "GET");
        reparaciones = respuesta.data;
        let filas = "";
        for (let i=0; i<reparaciones.length; i++){
            filas += `<tr data-id="${Escapar(reparaciones[i].id)}">
            <td>${Escapar(reparaciones[i].id_prestamo)}</td>
            <td>${Escapar(reparaciones[i].codigo)} - ${Escapar(reparaciones[i].nombre)}</td>
            <td>${Escapar(reparaciones[i].condicion)}</td>
            <td>${Escapar(reparaciones[i].pendientes)}</td>
            <td><input type="number" name="reparadas" aria-label="Unidades reparadas de ${Escapar(reparaciones[i].nombre)}" min="1" max="${Number(reparaciones[i].pendientes)}" value="${Number(reparaciones[i].pendientes)}" style="width:70px"></td>
            <td><button type="button" class="Reparar">Marcar reparado</button></td>
            </tr>`;
        }
        $("#tablaReparacion").innerHTML = filas;
    } catch (error) {
        console.error("Error al cargar reparaciones:", error);
    }
}

async function Cargar_Notificaciones(){
    try {
        let respuesta = await accionDevolucion(API + "?notificaciones=1", "GET");
        notificaciones = respuesta.data;
        let nuevas = notificaciones.filter(item => item.leida == 0).length;
        $("#contadorNotificaciones").textContent = nuevas > 0 ? `(${nuevas})` : "";
        let filas = "";
        for (let i=0; i<notificaciones.length; i++){
            let leida = notificaciones[i].leida == 1;
            filas += `<tr data-id="${Escapar(notificaciones[i].id)}">
            <td>${Escapar(notificaciones[i].fecha)}</td>
            <td>${Escapar(notificaciones[i].mensaje)}</td>
            <td>${leida ? "Leída" : "Nueva"}</td>
            <td>${leida ? "" : `<button type="button" class="Leer">Marcar leída</button>`}</td>
            </tr>`;
        }
        $("#tablaNotificacion").innerHTML = filas;
    } catch (error) {
        console.error("Error al cargar notificaciones:", error);
    }
}

async function Reparar(evento){
    let boton = evento.target.closest(".Reparar");
    if (!boton){
        return;
    }
    try {
        let fila = boton.closest("tr");
        let datos = {
            id_detalle_devolucion: fila.dataset.id,
            cantidad: fila.querySelector("input[name='reparadas']").value
        }
        let respuesta = await accionDevolucion(API, "PUT", datos);
        if (respuesta.code !== 1){
            alert(respuesta.message);
            return;
        }
        alert("Se registró la reparación. El administrador fue notificado.");
        Cargar_Reparaciones();
        Cargar_Notificaciones();
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Marcar_Leida(evento){
    let boton = evento.target.closest(".Leer");
    if (!boton){
        return;
    }
    try {
        let respuesta = await accionDevolucion(API, "PUT", { id_notificacion: boton.closest("tr").dataset.id });
        if (respuesta.code !== 1){
            alert(respuesta.message);
            return;
        }
        Cargar_Notificaciones();
    } catch (error) {
        console.error(error);
    }
}

function CambiarPestana(evento){
    let boton = evento.target.closest(".pestana_devolucion");
    if (!boton){
        return;
    }
    document.querySelectorAll(".pestana_devolucion").forEach(item => item.classList.toggle("activa", item === boton));
    ["Devolver", "Reparaciones", "Notificaciones"].forEach(nombre => $("#panel" + nombre).classList.toggle("oculto", nombre !== boton.dataset.pestana));
}

function Recargar(){
    Limpiar();
    Cargar_Pendientes();
    Cargar_Reparaciones();
    Cargar_Notificaciones();
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
$("#tablaReparacion").addEventListener("click", Reparar);
$("#tablaNotificacion").addEventListener("click", Marcar_Leida);
$(".pestanas_devolucion").addEventListener("click", CambiarPestana);
document.addEventListener("actualizarDevoluciones", Recargar);

LlenarCondiciones();
Cargar_Pendientes();
Cargar_Reparaciones();
Cargar_Notificaciones();

})();
