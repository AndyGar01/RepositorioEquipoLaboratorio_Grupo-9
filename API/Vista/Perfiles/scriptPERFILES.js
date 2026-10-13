(function(){

const API = "/SistemaLaboratorios/API/Controlador/C_Perfiles.php";
let perfiles = [];

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

async function Cargar_Perfiles(){
    try {
        let tabla = $("#tablaPerfil");
        let respuesta = await accionPermiso("GET");
        perfiles = respuesta.data;
        let filas = "";
        for (let i=0; i<perfiles.length; i++){
            let v = Object.values(perfiles[i]);
            filas += `<tr data-id="${v[0]}">
            <td>${v[0]}</td>
            <td>${v[1]}</td>
            <td>${v[2]}</td>
            <td>${v[3]}</td>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error);
        alert("No se pudieron cargar los perfiles :(");
    }
}

async function Guardar_Perfil(evento){
    evento.preventDefault();
    try {
        let datos = {
            nombre: $("#nombre_perfil").value,
            descripcion: $("#descripcion_perfil").value,
            estado: $("#estado_perfil").values
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formPerfiles").reset();
            $("#id_perfil").value = "";
        } else {
            if ($("#id_perfil").value){
                await accionPermiso("PUT", { id: $("#id_perfil").value, ...datos});
                alert(`Se actualizo el perfil ${$("#nombre_perfil").value} exitosamente`);
            } else {
                await accionPermiso("POST", datos);
                alert(`Se inserto el perfil ${$("#nombre_perfil").value} exitosamente`);
            }
            $("#formPerfiles").reset();
            $("#id_perfil").value = "";
            Cargar_Perfiles();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar_Perfiles(idPerfil){
    try {
        await accionPermiso("DELETE", { id: idPerfil });
        Cargar_Perfiles();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el perfil :(");
    }
}

function Rellenar(idModulo){
    let per = perfiles.find(e => Object.values(e)[0] == idModulo);
    let v = Object.values(per);
    $("#id_perfil").value = v[0];
    $("#nombre_perfil").value = v[1];
    $("#descripcion_perfil").value = v[2];
    $("#estado_perfil").value = v[3];
}

function Limpiar_Perfil(){
    $("#id_perfil").value = "";
    $("#nombre_perfil").value = "";
    $("#descripcion_perfil").value = "";
    $("#estado_perfil").value = "";
}

$("#Eliminar_perfil").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_perfil").value;
    if (!id){
        alert("Seleccione el perfil que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar el perfil: ${$("#nombre_perfil").value}?`)){
        alert(`Se cancelo eliminar el perfil ${$("#nombre_perfil").value}.`);
        $("#formPerfiles").reset();
        $("#id_perfil").value = "";
        return;
    }
    Eliminar_Perfiles(id);
    alert("Se elimino el perfil correctamente.");
    $("#id_perfil").value = "";
    $("#formPerfiles").reset();
});

$("#formPerfiles").addEventListener("submit", Guardar_Perfil);
$("#Limpiar_perfil").addEventListener("click", () => Limpiar_Perfil());
$("#tablaPerfil").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

Cargar_Perfiles();

})();