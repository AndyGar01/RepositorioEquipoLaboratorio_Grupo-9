(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Equipos.php";
const API_CATEGORIA_EQUIPO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_CategoriaEquipo.php"
const API_ESTADO_EQUIPO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_EstadoEquipo.php"
let equipos = [];

async function accionEquipo(direccion, metodo, datos = null){
    let opciones = { method : metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

//Esta función permite llenar el combo box con el estado del equipo
async function LlenarCombo(direccion, id, textoInicial){
    let lista = (await accionEquipo(direccion, "GET")).data;
    let opciones = `<option value="">${textoInicial}</option>`;
    for (let i = 0; i < lista.length; i++){
        let v = Object.values(lista[i]);
        opciones += `<option value="${v[0]}">${v[0]} - ${v[1]}</option>`;
    }
    $(id).innerHTML = opciones;
}

async function Cargar_Equipos(){
    try {
        let tabla = $("#tablaEquipo");
        let respuesta = await accionEquipo(API, "GET");
        equipos = respuesta.data;
        let filas = "";
        for (let i=0; i<equipos.length; i++){
            let v = Object.values(equipos[i]);
            filas += `<tr data-id="${v[0]}">
            <td>${v[0]}</td>
            <td>${v[1]}</td>
            <td>${v[2]}</td>
            <td>${v[3]}</td>
            <td>${v[4]}</td>
            <td>${v[5]}</td>
            <td>${v[6] ?? ""}</td>
            <td>${v[7] ?? ""}</td>
            <td>${v[8] ?? ""}</td>
            </tr>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error); 
        alert("No se pudieron cargar los datos :(");
    }
}

async function Guardar_Equipo(evento){
    evento.preventDefault();
    try {
        let datos = {
            id_categoria: $("#id_categoria_equipo").value,
            id_estado: $("#id_estado_equipo").value,
            codigo: $("#codigo_equipo").value,
            nombre: $("#nombre_equipo").value,
            descripcion: $("#descripcion_equipo").value,
            numero_serie: $("#numero_serie_equipo").value,
            fecha_adquisicion: $("#fecha_adquisicion_equipo").value,
            imagen_url: $("#imagen_url_equipo").value
        }
        let obligatorios = [datos.id_categoria, datos.id_estado, datos.codigo, datos.nombre, datos.descripcion];
        if (obligatorios.some(vacio => vacio == "")){
            alert("Categoría, estado, código, nombre y descripción son necesarios para el proceso.");
            $("#formEquipo").reset();
            $("#id_equipo").value = "";
        } else {
            if ($("#id_equipo").value){
                await accionEquipo(API, "PUT", { id: $("#id_equipo").value, ...datos});
                alert(`Se actualizo el equipo ${$("#nombre_equipo").value} exitosamente`);
            } else {
                await accionEquipo(API, "POST", datos);
                alert(`Se inserto el equipo ${$("#nombre_equipo").value} exitosamente`);
            }
            $("#formEquipo").reset();
            $("#id_equipo").value = "";
            Cargar_Equipos();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar(idEquipo){
    try {
        await accionEquipo(API, "DELETE", { id: idEquipo });
        Cargar_Equipos();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el registro :(");
    }
}

function Rellenar(idEquipo){
    let equi = equipos.find(e => Object.values(e)[0] == idEquipo);
    let v = Object.values(equi);
    $("#id_equipo").value = v[0];
    $("#id_categoria_equipo").value = v[1];
    $("#id_estado_equipo").value = v[2];
    $("#codigo_equipo").value = v[3];
    $("#nombre_equipo").value = v[4];
    $("#descripcion_equipo").value = v[5];
    $("#numero_serie_equipo").value = v[6] ?? "";
    $("#fecha_adquisicion_equipo").value = v[7] ?? "";
    $("#imagen_url_equipo").value = v[8] ?? "";
}

function Limpiar(){
    $("#id_equipo").value = "";
    $("#id_categoria_equipo").value = "";
    $("#id_estado_equipo").value = "";
    $("#codigo_equipo").value = "";
    $("#nombre_equipo").value = "";
    $("#descripcion_equipo").value = "";
    $("#numero_serie_equipo").value = "";
    $("#fecha_adquisicion_equipo").value = "";
    $("#imagen_url_equipo").value = "";
}

$("#Eliminar_equipo").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_equipo").value;
    if(!id){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar el equipo de nombre: ${$("#nombre_equipo").value}?`)){
        alert(`Se cancelo eliminar el equipo ${$("#nombre_equipo").value}.`);
        $("#formEquipo").reset();
        $("#id_equipo").value = "";
        return;
    }
    Eliminar(id);
    alert("Se elimino el equipo correctamente.");
    $("#id_equipo").value = "";
    $("#formEquipo").reset();
});

$("#formEquipo").addEventListener("submit", Guardar_Equipo);
$("#Limpiar_equipo").addEventListener("click", () => Limpiar());
$("#tablaEquipo").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

LlenarCombo(API_CATEGORIA_EQUIPO, "#id_categoria_equipo", "Seleccione una categoría");
LlenarCombo(API_ESTADO_EQUIPO, "#id_estado_equipo", "Seleccione un estado");
Cargar_Equipos();

})();