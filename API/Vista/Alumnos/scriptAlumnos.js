(function(){

const API = "/SistemaLaboratorios/API/Controlador/C_Alumnos.php";
let alumnos = [];

async function accionAlumno(metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(API, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

async function Cargar_Alumnos(){
    try {
        let tabla = $("#tablaAlumno");
        let respuesta = await accionAlumno("GET");
        alumnos = respuesta.data;
        let filas = "";
        for (let i=0; i<alumnos.length; i++){
            let v = Object.values(alumnos[i]);
            filas += `<tr data-id="${v[0]}">
            <td>${v[0]}</td>
            <td>${v[1]}</td>
            <td>${v[2]}</td>
            <td>${v[3]}</td>
            <td>${v[4]}</td>
            <td>${v[5]}</td>
            <td>${v[6]}</td>
            <td>${v[7]}</td>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error); 
        alert("No se pudieron cargar los datos :(");
    }
}

async function Guardar_Alumno(evento){
    evento.preventDefault();
    try {
        let datos = {
            id_carrera: $("#id_carrera_alumno").value,
            id_estado: $("#id_estado_alumno").value,
            carne: $("#carne_alumno").value,
            nombre: $("#nombre_alumno").value,
            apellido: $("#apellido_alumno").value,
            correo: $("#correo_alumno").value,
            fecha_nacimiento: $("#fecha_nacimiento_alumno").value
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formAlumno").reset();
            $("#id_alumno").value = "";
        } else {
            if ($("#id_alumno").value){
                await accionAlumno("PUT", { id: $("#id_alumno").value, ...datos});
                alert(`Se actualizo el alumno ${$("#nombre_alumno").value} exitosamente`);
            } else {
                await accionAlumno("POST", datos);
                alert(`Se inserto el alumno ${$("#nombre_alumno").value} exitosamente`);
            }
            $("#formAlumno").reset();
            $("#id_alumno").value = "";
            Cargar_Alumnos();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar(idAlumno){
    try {
        await accionAlumno("DELETE", { id: idAlumno });
        Cargar_Alumnos();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el registro :(");
    }
}

function Rellenar(idAlumno){
    let alu = alumnos.find(e => Object.values(e)[0] == idAlumno);
    let v = Object.values(alu);
    $("#id_alumno").value = v[0];
    $("#id_carrera_alumno").value = v[1];
    $("#id_estado_alumno").value = v[2];
    $("#carne_alumno").value = v[3];
    $("#nombre_alumno").value = v[4];
    $("#apellido_alumno").value = v[5];
    $("#correo_alumno").value = v[6];
    $("#fecha_nacimiento_alumno").value = v[7];
}

function Limpiar(){
    $("#id_alumno").value = "";
    $("#id_carrera_alumno").value = "";
    $("#id_estado_alumno").value = "";
    $("#carne_alumno").value = "";
    $("#nombre_alumno").value = "";
    $("#apellido_alumno").value = "";
    $("#correo_alumno").value = "";
    $("#fecha_nacimiento_alumno").value = "";
}

$("#Eliminar_alumno").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_alumno").value;
    if(!id){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar al alumno de nombre: ${$("#nombre_alumno").value}?`)){
        alert(`Se cancelo eliminar al alumno ${$("#nombre_alumno").value}.`);
        $("#formAlumno").reset();
        $("#id_alumno").value = "";
        return;
    }
    Eliminar(id);
    alert("Se elimino el alumno correctamente.");
    $("#id_alumno").value = "";
    $("#formAlumno").reset();
});

$("#formAlumno").addEventListener("submit", Guardar_Alumno);
$("#Limpiar_alumno").addEventListener("click", () => Limpiar());
$("#tablaAlumno").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

Cargar_Alumnos();

})();