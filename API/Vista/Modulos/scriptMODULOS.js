(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Modulos.php";
let modulos = [];

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

async function Cargar_Modulos(){
    try {
        let tabla = $("#tablaModulo");
        let respuesta = await accionPermiso("GET");
        modulos = respuesta.data;
        let filas = "";
        for (let i=0; i<modulos.length; i++){
            let v = Object.values(modulos[i]);
            filas += `<tr data-id="${v[0]}">
            <td>${v[0]}</td>
            <td>${v[1]}</td>
            <td>${v[2]}</td>`;
        }
        tabla.innerHTML = filas;
    } catch (error) {
        console.error("Error en Cargar:", error);
        alert("No se pudieron cargar los módulos :(");
    }
}

async function Guardar_Modulo(evento){
    evento.preventDefault();
    try {
        let datos = {
            nombre: $("#nombre_modulo").value,
            descripcion: $("#descripcion_modulo").value
        }
        if (Object.values(datos).some(vacio => vacio == "")){
            alert("Todos los datos son necesarios para el proceso.");
            $("#formModulo").reset();
            $("#id_modulo").value = "";
        } else {
            if ($("#id_modulo").value){
                await accionPermiso("PUT", { id: $("#id_modulo").value, ...datos});
                alert(`Se actualizo el permiso ${$("#nombre_modulo").value} exitosamente`);
            } else {
                await accionPermiso("POST", datos);
                alert(`Se inserto el módulo ${$("#nombre_modulo").value} exitosamente`);
            }
            $("#formModulo").reset();
            $("#id_modulo").value = "";
            Cargar_Modulos();
        }
    } catch (error) {
        console.error(error);
        alert(error);
    }
}

async function Eliminar_Modulo(idModulo){
    try {
        await accionPermiso("DELETE", { id: idModulo });
        Cargar_Modulos();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar el módulo :(");
    }
}

function Rellenar(idModulo){
    let per = modulos.find(e => Object.values(e)[0] == idModulo);
    let v = Object.values(per);
    $("#id_modulo").value = v[0];
    $("#nombre_modulo").value = v[1];
    $("#descripcion_modulo").value = v[2];
}

function Limpiar(){
    $("#id_modulo").value = "";
    $("#nombre_modulo").value = "";
    $("#descripcion_modulo").value = "";
}

$("#Eliminar_modulo").addEventListener("click", function(accion){
    accion.preventDefault();
    let id = $("#id_modulo").value;
    if (!id){
        alert("Seleccione el módulo que desea eliminar.");
        return;
    }
    if (!confirm(`Esta seguro de eliminar el módulo: ${$("#nombre_modulo").value}?`)){
        alert(`Se cancelo eliminar el módulo ${$("#nombre_modulo").value}.`);
        $("#formModulo").reset();
        $("#id_modulo").value = "";
        return;
    }
    Eliminar_Modulo(id);
    alert("Se elimino el módulo correctamente.");
    $("#id_modulo").value = "";
    $("#formModulo").reset();
});

$("#formModulo").addEventListener("submit", Guardar_Modulo);
$("#Limpiar_modulo").addEventListener("click", () => Limpiar());
$("#tablaModulo").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.id);
});

Cargar_Modulos();

})();