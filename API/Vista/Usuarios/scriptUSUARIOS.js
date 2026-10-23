const API_USUARIO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Usuarios.php";
const API_PERFIL = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Perfiles.php";
let usuarios = [];

async function accionUsuario(direccion, metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

//Esta función permite llenar el combo box con el estado del alumno
async function LlenarCombo(direccion, id, textoInicial){
    let lista = (await accionUsuario(direccion, "GET")).data;
    let opciones = `<option value="">${textoInicial}</option>`;
    for (let i = 0; i < lista.length; i++){
        let v = Object.values(lista[i]);
        opciones += `<option value="${v[0]}">${v[0]} - ${v[1]}</option>`;
    }
    $(id).innerHTML = opciones;
}

async function Cargar_Usuarios(){
    try {
        let tabla = $("#tabla");
        let respuesta = await accionUsuario(API_USUARIO, "GET");
        usuarios = respuesta.data;
        let filas = "";
        for (let i=0; i<usuarios.length; i++){
            let v = Object.values(usuarios[i]);
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

async function Guardar_Usuario(evento){
    evento.preventDefault();
    try {
        let datos = {
            id_per: $("#id_per_usuario").value,
            carne: $("#carne_usuario").value,
            nombre: $("#nombre_usuario").value,
            apellido: $("#apellido_usuario").value,
            correo: $("#correo_usuario").value,
            contrasena: $("#contrasena_usuario").value,
            activo: $("#estado_usuario").value
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#form").reset();
            $("#id_usuario").value = "";
        } else {
            if ($("#id_usuario").value){
            await accionUsuario(API_USUARIO, "PUT", { id_usu: $("#id_usuario").value, ...datos});
            alert(`Se actualizo el usuario ${$("#nombre_usuario").value} exitosamente`);
            } else{
                await accionUsuario(API_USUARIO, "POST", datos);
                alert(`Se inserto el usuario ${$("#nombre_usuario").value} exitosamente`);
            }
            $("#form").reset();
            $("#id_usuario").value = "";
            Cargar_Usuarios();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar(idUsuario){
    try {
        await accionUsuario(API_USUARIO, "DELETE", { id_usu: idUsuario });
        Cargar_Usuarios();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el registro :(");
    }
}

function Rellenar(idEmpleado){
    let usu = usuarios.find(e => Object.values(e)[0] == idEmpleado);
    let v = Object.values(usu);
    $("#id_usuario").value = v[0];
    $("#id_per_usuario").value = v[1];
    $("#carne_usuario").value = v[2];
    $("#nombre_usuario").value = v[3];
    $("#apellido_usuario").value = v[4];
    $("#correo_usuario").value = v[5];
    $("#contrasena_usuario").value = "";
    $("#estado_usuario").value = v[6];
}

function Limpiar(){
    $("#id_usuario").value = "";
    $("#id_per_usuario").value = "";
    $("#carne_usuario").value = "";
    $("#nombre_usuario").value = "";
    $("#apellido_usuario").value = "";
    $("#correo_usuario").value = "";
    $("#contrasena_usuario").value = "";
    $("#estado_usuario").value = "";
}

$("#Eliminar_usuario").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_usuario").value;
    if(!id){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar al usuario de nombre: ${$("#nombre_usuario").value}?`)){
        alert(`Se cancelo eliminar al usuario ${$("#nombre_usuario").value}.`);
        $("#form").reset();
        $("#id_usuario").value = "";
        return;
    }
    Eliminar(id);
    alert("Se elimino el usuario correctamente.");
    $("#id_usuario").value = "";
    $("#form").reset();
});

$("#form").addEventListener("submit", Guardar_Usuario)
$("#Limpiar_usuario").addEventListener("click", () => Limpiar());
$("#tabla").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id)
})

LlenarCombo(API_PERFIL, "#id_per_usuario", "Seleccione un perfil")
Cargar_Usuarios();