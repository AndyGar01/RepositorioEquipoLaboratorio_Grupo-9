(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Permisos.php";
let permisos = [];

async function accionPermiso(metodo, datos = null){
    let opciones = { method: metodo, cache: 'no-store' };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(API, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

async function Cargar_Permisos(){
    try {
        let tabla = $("#tablaPermiso");
        let respuesta = await accionPermiso("GET");
        permisos = respuesta.data;
        let filas = "";
        for (let i=0; i<permisos.length; i++){
            let v = Object.values(permisos[i]);
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

async function Guardar_Permiso(evento){
    evento.preventDefault();
    try {
        let datos = {
            nombre: $("#nombre_permiso").value,
            descripcion: $("#descripcion_permiso").value
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formPermiso").reset();
            $("#id_permiso").value = "";
        } else {
            if ($("#id_permiso").value){
                await accionPermiso("PUT", { id: $("#id_permiso").value, ...datos});
                alert(`Se actualizo el permiso ${$("#nombre_permiso").value} exitosamente`);
            } else {
                await accionPermiso("POST", datos);
                alert(`Se inserto el permiso ${$("#nombre_permiso").value} exitosamente`);
            }
            $("#formPermiso").reset();
            $("#id_permiso").value = "";
            Cargar_Permisos();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar(idPermiso){
    try {
        await accionPermiso("DELETE", { id: idPermiso });
        Cargar_Permisos();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el registro :(");
    }
}

function Rellenar(idPermiso){
    let per = permisos.find(e => Object.values(e)[0] == idPermiso);
    let v = Object.values(per);
    $("#id_permiso").value = v[0];
    $("#nombre_permiso").value = v[1];
    $("#descripcion_permiso").value = v[2];
}

function Limpiar(){
    $("#id_permiso").value = "";
    $("#nombre_permiso").value = "";
    $("#descripcion_permiso").value = "";
}

$("#Eliminar_permiso").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_permiso").value;
    if (!id){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar el permiso: ${$("#nombre_permiso").value}?`)){
        alert(`Se cancelo eliminar el permiso ${$("#nombre_permiso").value}.`);
        $("#formPermiso").reset();
        $("#id_permiso").value = "";
        return;
    }
    Eliminar(id);
    alert("Se elimino el permiso correctamente.");
    $("#id_permiso").value = "";
    $("#formPermiso").reset();
});

$("#formPermiso").addEventListener("submit", Guardar_Permiso);
$("#Limpiar_permiso").addEventListener("click", () => Limpiar());
$("#tablaPermiso").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

Cargar_Permisos();

})();