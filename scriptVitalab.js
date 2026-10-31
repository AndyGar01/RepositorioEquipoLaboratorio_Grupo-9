// -------------------- PROTECCION DE SESION ----------------------------
(function verificarSesion() {
    const TOKEN_KEY = "vitalab_token";
    const API_LOGIN = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Login.php";

    const token = localStorage.getItem(TOKEN_KEY);

    if (!token) {
        window.location.replace("login.html");
        return;
    }

    fetch(`${API_LOGIN}?token=${encodeURIComponent(token)}`, {
        method: "GET",
        cache: "no-store"
    })
    .then(function (r) { return r.json(); })
    .then(function (respuesta) {
        if (!respuesta.data || !respuesta.data.id) {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem("vitalab_usuario");
            window.location.replace("login.html");
        }
    })
    .catch(function () {
        window.location.replace("login.html");
    });
})();
// ----------------------------------------------------------------------------------------------------------

const Logo = document.getElementById("Logo");
const barra_lateral = document.querySelector(".barra_lateral");
const spans = document.querySelectorAll("span");
const palanca = document.querySelector(".switch")
const circulo = document.querySelector(".circulo")
const menu = document.querySelector(".Menu")
const main = document.querySelector("main")

const PrimerBoton = document.getElementById("PrimerBoton");
const SegundoBoton = document.getElementById("SegundoBoton");
const TercerBoton = document.getElementById("TercerBoton");
const CuartoBoton = document.getElementById("CuartoBoton");
const QuintoBoton = document.getElementById("QuintoBoton");
const SextoBoton = document.getElementById("SextoBoton");
const SeptimoBoton = document.getElementById("SeptimoBoton");

let $ = (clase) => document.querySelector(clase);

menu.addEventListener("click",()=>{
    barra_lateral.classList.toggle("max_barra_lateral");
    if(barra_lateral.classList.contains("max_barra_lateral")){
        menu.children[0].style.display ="none";
        menu.children[1].style.display ="block";
    }
    else {
        menu.children[0].style.display ="block";
        menu.children[1].style.display ="none";
    }
});

palanca.addEventListener("click",()=>{
    let body = document.body;
    body.classList.toggle("dark-mode");
    circulo.classList.toggle("prendido");
});

Logo.addEventListener("click",()=>{
    barra_lateral.classList.toggle("mini_barra_lateral");
    main.classList.toggle("min_main");  
    spans.forEach((span)=>{
        span.classList.toggle("oculto");
    });
});

PrimerBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("desplegado");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("oculto");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("desplegado");
})

SegundoBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("desplegado");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("oculto");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("desplegado");
})

TercerBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("desplegado");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("oculto");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("desplegado");
})

CuartoBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("desplegado");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("oculto");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("desplegado");
})

QuintoBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("desplegado");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("oculto");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("desplegado");
})

SextoBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("desplegado");
    $("#SeptimaContenido").classList.add("oculto");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("oculto");
    $("#SeptimaContenido").classList.remove("desplegado");
    
})

SeptimoBoton.addEventListener("click",()=>{
    $("#PrimerContenido").classList.add("oculto");
    $("#SegundoContenido").classList.add("oculto");
    $("#TercerContenido").classList.add("oculto");
    $("#CuartoContenido").classList.add("oculto");
    $("#QuintoContenido").classList.add("oculto");
    $("#SextaContenido").classList.add("oculto");
    $("#SeptimaContenido").classList.add("desplegado");

    $("#PrimerContenido").classList.remove("desplegado");
    $("#SegundoContenido").classList.remove("desplegado");
    $("#TercerContenido").classList.remove("desplegado");
    $("#CuartoContenido").classList.remove("desplegado");
    $("#QuintoContenido").classList.remove("desplegado");
    $("#SextaContenido").classList.remove("desplegado");
    $("#SeptimaContenido").classList.remove("oculto");
})

const usuarioData = JSON.parse(localStorage.getItem("vitalab_usuario"));
$(".nombre").textContent = usuarioData.nombre;
$(".email").textContent = usuarioData.correo;

async function CargarNombrePerfil() {
    let respuesta = await fetch("/SistemaLaboratorios/API/Controlador/C_Perfiles.php");
    let datos = await respuesta.json();
    let nombresPerfil = new Map(datos.data.map(p => {
        return [String(p.id), p.nombre];
    }));
    let nombrePerfil = nombresPerfil.get(String(usuarioData.perfil));
    $(".perfil").textContent = nombrePerfil ?? "Sin perfil";
}

CargarNombrePerfil();

$(".cerrarSesion").addEventListener("click", () => {
    if(confirm(`¿Está seguro de cerrar sesión siendo ${usuarioData.nombre}?`)){
        localStorage.removeItem("vitalab_token");
        localStorage.removeItem("vitalab_usuario");
        window.location.replace("login.html");
        return;
    }
});

/*------------------------------------------------------------------CRUD DE USUARIOS------------------------------------------------------------------*/

async function MantenimientoUsuarios(){
    const respuesta = await fetch("API/Vista/Usuarios/indexUSUARIOS.php");
    $(".contenedor_usuario").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_usuario"));

    const script = document.createElement("script");
    script.src = "API/Vista/Usuarios/scriptUSUARIOS.js";
    document.body.appendChild(script);
}

MantenimientoUsuarios();

/*------------------------------------------------------------------CRUD DE MODULOS------------------------------------------------------------------*/

async function MantenimientoModulos(){
    const respuesta = await fetch("API/Vista/Modulos/indexMODULOS.php");
    $(".contenedor_modulo").innerHTML = await respuesta.text();

    const script = document.createElement("script");
    script.src = "API/Vista/Modulos/scriptMODULOS.js";
    document.body.appendChild(script);
}

MantenimientoModulos();

/*------------------------------------------------------------------CRUD DE PERFILES------------------------------------------------------------------*/

async function MantenimientoPerfiles(){
    const respuesta = await fetch("API/Vista/Perfiles/indexPERFILES.php");
    $(".contenedor_perfil").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_perfil"));

    const script = document.createElement("script");
    script.src = "API/Vista/Perfiles/scriptPERFILES.js";
    document.body.appendChild(script);
}

MantenimientoPerfiles();

/*------------------------------------------------------------------CRUD DE PERFILESMODULOS------------------------------------------------------------------*/

async function MantenimientoPerfilModulo(){
    const respuesta = await fetch("API/Vista/PerfilModulo/indexPERFILMODULO.php");
    $(".contenedor_perfilmodulo").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_perfilmodulo"));

    const script = document.createElement("script");
    script.src = "API/Vista/PerfilModulo/scriptPERFILMODULO.js";
    document.body.appendChild(script);
}

MantenimientoPerfilModulo();

/*------------------------------------------------------------------CRUD DE ALUMNOS------------------------------------------------------------------*/

async function MantenimientoAlumnos(){
    const respuesta = await fetch("API/Vista/Alumnos/indexAlumnos.php");
    $(".contenedor_alumno").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_alumno"));

    const script = document.createElement("script");
    script.src = "API/Vista/Alumnos/scriptAlumnos.js";
    document.body.appendChild(script);
}
MantenimientoAlumnos();

/*------------------------------------------------------------------CRUD DE CARRERAS------------------------------------------------------------------*/

async function MantenimientoCarreras(){
    const respuesta = await fetch("API/Vista/Carrera_Alumnos/indexCarrera_Alumnos.php");
    $(".contenedor_carrera").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_carrera"));    

    const script = document.createElement("script");
    script.src = "API/Vista/Carrera_Alumnos/scriptCarrera_Alumnos.js";
    document.body.appendChild(script);
}

MantenimientoCarreras();

/*------------------------------------------------------------------CRUD DE EQUIPOS------------------------------------------------------------------*/

async function MantenimientoEquipos(){
    const respuesta = await fetch("API/Vista/Equipos/indexEquipos.php");
    $(".contenedor_equipos").innerHTML = await respuesta.text();

    VerificarModulo($(".contenedor_equipos"));    

    const script = document.createElement("script");
    script.src = "API/Vista/Equipos/scriptEquipos.js";
    document.body.appendChild(script);
}

MantenimientoEquipos();