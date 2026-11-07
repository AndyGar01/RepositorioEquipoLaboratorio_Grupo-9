(function () {
    const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Login.php";
    const form = document.getElementById("formCambioContrasena");
    const mensaje = document.getElementById("mensajeCambio");
    let usuario = null;

    fetch(API, { credentials: "same-origin", cache: "no-store" })
        .then(respuesta => respuesta.json())
        .then(respuesta => {
            if (!respuesta.data || !respuesta.data.id) {
                window.location.replace("login.html");
                return;
            }
            usuario = respuesta.data;
        })
        .catch(() => window.location.replace("login.html"));

    form.addEventListener("submit", async evento => {
        evento.preventDefault();
        const actual = document.getElementById("contrasenaActual").value;
        const nueva = document.getElementById("contrasenaNueva").value;
        const confirmar = document.getElementById("confirmarContrasena").value;
        if (nueva.length < 8) {
            mensaje.textContent = "La nueva contraseña debe tener al menos 8 caracteres.";
            return;
        }
        if (nueva !== confirmar) {
            mensaje.textContent = "Las contraseñas nuevas no coinciden.";
            return;
        }
        try {
            const respuesta = await fetch(API, {
                method: "PUT",
                credentials: "same-origin",
                body: new URLSearchParams({ contrasena_actual: actual, contrasena_nueva: nueva })
            });
            const datos = await respuesta.json();
            if (!respuesta.ok) throw new Error(datos.message || "No se pudo actualizar la contraseña.");
            if (usuario) {
                usuario.cambio_contrasena_requerido = 0;
                localStorage.setItem("vitalab_usuario", JSON.stringify(usuario));
            }
            window.location.replace(usuario && usuario.perfil_nombre === "Alumno" ? "cliente.html" : "index.html");
        } catch (error) {
            mensaje.textContent = error.message;
        }
    });
})();
