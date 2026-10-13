(function(){

const API = "/SistemaLaboratorios/API/Controlador/C_CarreraAlumnos.php";
let carreras = [];

async function accionCarrera(metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(API, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

async function Cargar_Carreras(){
    try {
        let tabla = $("#tablaCarrera");
        let respuesta = await accionCarrera("GET");
        carreras = respuesta.data;
        let filas = "";
        for (let i=0; i<carreras.length; i++){
            let v = Object.values(carreras[i]);
            filas += `<tr data-id="${v[0]}">
            <td>${v[0]}</td>
            <td>${v[1]}</td>
            <td>${v[2]}</td>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error); 
        alert("No se pudieron cargar los datos :(");
    }
}

async function Guardar_Carrera(evento){
    evento.preventDefault();
    try {
        let datos = {
            nombre: $("#nombre_carrera").value,
            descripcion: $("#descripcion_carrera").value
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formCarrera").reset();
            $("#id_carrera").value = "";
        } else {
            if ($("#id_carrera").value){
                await accionCarrera("PUT", { id: $("#id_carrera").value, ...datos});
                alert(`Se actualizo la carrera ${$("#nombre_carrera").value} exitosamente`);
            } else {
                await accionCarrera("POST", datos);
                alert(`Se inserto la carrera ${$("#nombre_carrera").value} exitosamente`);
            }
            $("#formCarrera").reset();
            $("#id_carrera").value = "";
            Cargar_Carreras();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar(idCarrera){
    try {
        await accionCarrera("DELETE", { id: idCarrera });
        Cargar_Carreras();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el registro :(");
    }
}

function Rellenar(idCarrera){
    let car = carreras.find(e => Object.values(e)[0] == idCarrera);
    let v = Object.values(car);
    $("#id_carrera").value = v[0];
    $("#nombre_carrera").value = v[1];
    $("#descripcion_carrera").value = v[2];
}

function Limpiar(){
    $("#id_carrera").value = "";
    $("#nombre_carrera").value = "";
    $("#descripcion_carrera").value = "";
}

$("#Eliminar_carrera").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_carrera").value;
    if(!id){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar la carrera de nombre: ${$("#nombre_carrera").value}?`)){
        alert(`Se cancelo eliminar la carrera ${$("#nombre_carrera").value}.`);
        $("#formCarrera").reset();
        $("#id_carrera").value = "";
        return;
    }
    Eliminar(id);
    alert("Se elimino la carrera correctamente.");
    $("#id_carrera").value = "";
    $("#formCarrera").reset();
});

$("#formCarrera").addEventListener("submit", Guardar_Carrera);
$("#Limpiar_carrera").addEventListener("click", () => Limpiar());
$("#tablaCarrera").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

Cargar_Carreras();

})();