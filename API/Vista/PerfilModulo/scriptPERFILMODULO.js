(function(){

const API_PERFILMODULO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_PerfilModulo.php";
const API_PERFIL = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Perfiles.php";
const API_MODULO = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Modulos.php";
let permisos = [];

async function accionPermiso(direccion, metodo, datos = null){
    let opciones = { method: metodo };
    if (datos !== null){
        opciones.body = new URLSearchParams(datos);
    }
    let solicitud = await fetch(direccion, opciones);
    let respuesta = await solicitud.json();
    console.log(metodo, solicitud.status, respuesta);
    return respuesta;
}

//Esta función permite llenar el combo box con perfiles y modulos
async function LlenarCombo(direccion, id, textoInicial){
    let lista = (await accionPermiso(direccion, "GET")).data;
    let opciones = `<option value="">${textoInicial}</option>`;
    for (let i = 0; i < lista.length; i++){
        let v = Object.values(lista[i]);
        opciones += `<option value="${v[0]}">${v[0]} - ${v[1]}</option>`;
    }
    $(id).innerHTML = opciones;
}

async function Cargar_PerfilModulo(){
    try {
        let respuesta = await accionPermiso(API_PERFILMODULO, "GET");
        let perfil = (await accionPermiso(API_PERFIL, "GET")).data;
        let modulo = (await accionPermiso(API_MODULO, "GET")).data;

        let nombresPerfil = new Map(perfil.map(p => {
            let v = Object.values(p);
            return [String(v[0]), v[1]];
        }));

        let nombresModulo = new Map(modulo.map(p => {
            let v = Object.values(p);
            return [String(v[0]), v[1]];
        })); 

        permisos = respuesta.data;
        let filas = "";
        for (let i = 0; i < permisos.length; i++){
            let v = Object.values(permisos[i]);
            let nombrePerfil = nombresPerfil.get(String(v[0]));
            let nombreModulo = nombresModulo.get(String(v[1]));
            filas += `<tr data-per="${v[0]}" data-mod="${v[1]}">
                <td>${v[0]} - ${nombrePerfil}</td>
                <td>${v[1]} - ${nombreModulo}</td>
                <td>${v[2] == 1 ? "✔" : "✘"}</td>
                <td>${v[3] == 1 ? "✔" : "✘"}</td>
                <td>${v[4] == 1 ? "✔" : "✘"}</td>
                <td>${v[5] == 1 ? "✔" : "✘"}</td>
            </tr>`;
        }
        $("#tablaPerfilModulo").innerHTML = filas;
    } catch (error) {
        console.error(error);
        alert("No se pudieron cargar los permisos :(");
    }
}

async function Guardar_PerfilModulo(evento){
    evento.preventDefault();
    try {
        let datos = {
            id_per: $("#id_perfilcombo").value,
            id_mod: $("#id_modulocombo").value,
            ingresar: $("#ingresar").checked ? "1" : "0",
            actualizar: $("#actualizar").checked ? "1" : "0",
            eliminar: $("#eliminar").checked ? "1" : "0",
            consultar: $("#consultar").checked ? "1" : "0"
        }
        if ( $("#id_perfilcombo").value === "" || $("#id_modulocombo").value === ""){
            alert("Seleccione el perfil y el módulo.");
            return;
        }
        await accionPermiso(API_PERFILMODULO, "POST", datos);
        alert("Permisos guardados correctamente.");
        Limpiar();
        Cargar_PerfilModulo();
    } catch (error) {
        alert("No se pudieron guardar los permisos :(");
    }
}

async function Eliminar_PerfilModulo(idPerfil, idModulo){
    try {
        await accionPermiso(API_PERFILMODULO, "DELETE", { id_per: idPerfil, id_mod: idModulo });
        Cargar_PerfilModulo();
    } catch (error) {
        console.error(error);
        alert("No ha sido posible eliminar los permisos :(");
    }
}

function Rellenar(idPerfil, idModulo){
    let pm = permisos.find(e => Object.values(e)[0] == idPerfil && Object.values(e)[1] == idModulo);
    let v = Object.values(pm);
    $("#id_perfilcombo").value = v[0];
    $("#id_modulocombo").value = v[1];
    $("#ingresar").checked = v[2] == 1;
    $("#actualizar").checked = v[3] == 1;
    $("#eliminar").checked = v[4] == 1;
    $("#consultar").checked = v[5] == 1;
}

function Limpiar(){
    $("#id_perfilcombo").value = "";
    $("#id_modulocombo").value = "";
    $("#ingresar").checked = false;
    $("#actualizar").checked = false;
    $("#eliminar").checked = false;
    $("#consultar").checked = false;
}

$("#Eliminar_pm").addEventListener("click", function(accion){
    accion.preventDefault();
    let id_per = $("#id_perfilcombo").value;
    let id_mod = $("#id_modulocombo").value;
    if (!id_per || !id_mod){
        alert("Seleccione el registro que desea eliminar.");
        return;
    }
    let perfil = $("#id_perfilcombo").selectedOptions[0].text;
    let modulo = $("#id_modulocombo").selectedOptions[0].text;
    if (!confirm(`Esta seguro de eliminar los permisos del perfil ${perfil} en el módulo ${modulo}?`)){
        alert(`Se cancelo eliminar los permisos de ${perfil} en ${modulo}.`);
        Limpiar();
        return;
    }
    Eliminar_PerfilModulo(id_per, id_mod);
    alert("Se elimino los permisos correctamente.");
    Limpiar();
});

$("#formPerfilModulo").addEventListener("submit", Guardar_PerfilModulo);
$("#Limpiar_pm").addEventListener("click", () => Limpiar());
$("#tablaPerfilModulo").addEventListener("click", (evento) => {
    let fila = evento.target.closest("tr");
    if (!fila) return;
    Rellenar(fila.dataset.per, fila.dataset.mod);
});

LlenarCombo(API_PERFIL, "#id_perfilcombo", "Seleccione un perfil");
LlenarCombo(API_MODULO, "#id_modulocombo", "Seleccione un módulo");
Cargar_PerfilModulo();

})();