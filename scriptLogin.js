(function () {
    const API       = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Login.php";
    const TOKEN_KEY = "vitalab_token";

    // Si ya hay sesion activa, ir directo al sistema
    const tokenPrevio = localStorage.getItem(TOKEN_KEY);
    if (tokenPrevio) {
        fetch(`${API}?token=${encodeURIComponent(tokenPrevio)}`, {
            method: "GET",
            cache: "no-store"
        })
        .then(r => r.json())
        .then(function (respuesta) {
            if (respuesta.data && respuesta.data.usuario) {
                window.location.replace("index.html");
            }
        })
        .catch(function () { /* si falla la verificacion, dejar en login */ });
    }

    // Referencias al DOM
    const form          = document.getElementById("formLogin");
    const correoInput   = document.getElementById("correo");
    const claveInput    = document.getElementById("contrasena");
    const recordarCheck = document.getElementById("recordarme");
    const btnIngresar   = document.getElementById("btnIngresar");
    const btnTexto      = document.getElementById("btnTexto");
    const btnCargando   = document.getElementById("btnCargando");
    const mensajeError  = document.getElementById("mensajeError");
    const toggleClave   = document.getElementById("toggleContrasena");

    // Mostrar / ocultar contraseña
    toggleClave.addEventListener("click", function () {
        if (claveInput.type === "password") {
            claveInput.type = "text";
            toggleClave.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            claveInput.type = "password";
            toggleClave.classList.replace("fa-eye-slash", "fa-eye");
        }
    });

    function mostrarError(msg) {
        mensajeError.textContent = msg;
        mensajeError.classList.remove("oculto");
    }

    function ocultarError() {
        mensajeError.classList.add("oculto");
        mensajeError.textContent = "";
    }

    function setCargando(estado) {
        btnIngresar.disabled = estado;
        btnTexto.classList.toggle("oculto", estado);
        btnCargando.classList.toggle("oculto", !estado);
    }

    // Submit del formulario
    form.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        ocultarError();

        const correo     = correoInput.value.trim();
        const contrasena = claveInput.value;

        if (!correo || !contrasena) {
            mostrarError("Por favor ingresa tu correo y contraseña.");
            return;
        }

        setCargando(true);

        try {
            const solicitud = await fetch(API, {
                method: "POST",
                body: new URLSearchParams({ correo, contrasena }),
                cache: "no-store"
            });

            const respuesta = await solicitud.json();

            if (!respuesta.data || !respuesta.data.token) {
                mostrarError(respuesta.message || "Credenciales incorrectas.");
                setCargando(false);
                return;
            }

            const token   = respuesta.data.token;
            const usuario = respuesta.data.usuario;

            localStorage.setItem(TOKEN_KEY, token);
            localStorage.setItem("vitalab_usuario", JSON.stringify(usuario));

            if (recordarCheck.checked) {
                const exp = new Date();
                exp.setDate(exp.getDate() + 7);
                document.cookie = `${TOKEN_KEY}=${token}; expires=${exp.toUTCString()}; path=/; SameSite=Strict`;
            }

            window.location.replace("index.html");

        } catch (error) {
            console.error("Error en login:", error);
            mostrarError("Error de conexión. Verifica tu red e intenta de nuevo.");
            setCargando(false);
        }
    });

})();