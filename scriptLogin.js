(function () {
    const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Login.php";
    const form = document.getElementById("formLogin");
    const correoInput = document.getElementById("correo");
    const claveInput = document.getElementById("contrasena");
    const btnIngresar = document.getElementById("btnIngresar");
    const btnTexto = document.getElementById("btnTexto");
    const btnCargando = document.getElementById("btnCargando");
    const mensajeError = document.getElementById("mensajeError");
    const toggleClave = document.getElementById("toggleContrasena");

    function destino(usuario) {
        if (Number(usuario.cambio_contrasena_requerido) === 1) {
            return "cambiarContrasena.html";
        }
        return usuario.perfil_nombre === "Alumno" ? "cliente.html" : "index.html";
    }

    fetch(API, { method: "GET", credentials: "same-origin", cache: "no-store" })
        .then(respuesta => respuesta.json())
        .then(respuesta => {
            if (respuesta.data && respuesta.data.id) {
                localStorage.setItem("vitalab_usuario", JSON.stringify(respuesta.data));
                window.location.replace(destino(respuesta.data));
            }
        })
        .catch(() => {});

    toggleClave.addEventListener("click", function () {
        if (claveInput.type === "password") {
            claveInput.type = "text";
            toggleClave.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            claveInput.type = "password";
            toggleClave.classList.replace("fa-eye-slash", "fa-eye");
        }
    });

    function mostrarError(mensaje) {
        mensajeError.textContent = mensaje;
        mensajeError.classList.remove("oculto");
    }

    function setCargando(estado) {
        btnIngresar.disabled = estado;
        btnTexto.classList.toggle("oculto", estado);
        btnCargando.classList.toggle("oculto", !estado);
    }

    form.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        mensajeError.classList.add("oculto");

        const correo = correoInput.value.trim();
        const contrasena = claveInput.value;
        if (!correo || !contrasena) {
            mostrarError("Ingresa tu correo y contraseña.");
            return;
        }

        setCargando(true);
        try {
            const solicitud = await fetch(API, {
                method: "POST",
                credentials: "same-origin",
                body: new URLSearchParams({ correo, contrasena }),
                cache: "no-store"
            });
            const respuesta = await solicitud.json();
            if (!solicitud.ok || !respuesta.data || !respuesta.data.id) {
                mostrarError(respuesta.message || "No se pudo iniciar sesión.");
                setCargando(false);
                return;
            }

            localStorage.setItem("vitalab_usuario", JSON.stringify(respuesta.data));
            window.location.replace(destino(respuesta.data));
        } catch (error) {
            mostrarError("No fue posible conectarse con VitaLab. Intenta de nuevo.");
            setCargando(false);
        }
    });
})();
