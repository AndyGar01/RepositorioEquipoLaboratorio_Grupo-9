const API = "../RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_PerfilModulo.php"

async function CargarPermisos(idModulo, modulo) {
    try {
        let solicitud = await fetch(API);
        let respuesta = await solicitud.json();

        let usuario = JSON.parse(localStorage.getItem("vitalab_usuario"));
        let permiso = respuesta.data.find(p => {
            let datos = Object.values(p);
            return datos[0] == usuario.perfil && datos[1] == idModulo;
        });

        let datos = Object.values(permiso);

        console.log("Ingresar:", datos[2]);
        console.log("Actualizar:", datos[3]);
        console.log("Eliminar:", datos[4]);
        console.log("Consultar:", datos[5]);

        let btnEliminar = modulo.querySelector(".Eliminar");
        if (btnEliminar) {
            btnEliminar.disabled = permiso.eliminar == 0;
        }

        if (permiso.consultar == 0) {
            modulo.style.display = "none";
        }

        let btnGuardar = modulo.querySelector(".AgregarModificar");
        let idRegistro = modulo.querySelector(".idRegistro");

        if (btnGuardar && idRegistro) {
            function RevisarGuardar() {
                if (idRegistro.value) {
                    btnGuardar.disabled = permiso.actualizar == 0;
                } else {
                    btnGuardar.disabled = permiso.ingresar == 0;
                }
            }
            RevisarGuardar();
            modulo.addEventListener("click", () => {
                setTimeout(RevisarGuardar, 0);
            });
            modulo.addEventListener("change", RevisarGuardar);
        }

        console.log(permiso);
        return permiso;

    } catch (error) {
        console.error("Error al cargar permisos:", error);
    }
}

function VerificarModulo(contenedor) {
    let modulo = contenedor.querySelector("#contenidoModulo");

    if (modulo) {
        CargarPermisos(modulo.dataset.modulo, modulo);
    }
}