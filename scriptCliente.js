(function () {
    const API_LOGIN = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Login.php";
    const API_CLIENTE = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Cliente.php";
    const usuario = { perfil: null };
    let equipos = [];
    const seleccion = {};
    let solicitudesAlumno = [];

    const $ = selector => document.querySelector(selector);

    function escapar(texto) {
        return String(texto ?? "").replace(/[&<>"']/g, caracter => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        })[caracter]);
    }

    async function consultar(url, opciones = {}) {
        const respuesta = await fetch(url, {
            credentials: "same-origin",
            cache: "no-store",
            ...opciones
        });
        const datos = await respuesta.json();
        if (!respuesta.ok) throw new Error(datos.message || "No se pudo completar la solicitud.");
        return datos;
    }

    async function iniciar() {
        try {
            const sesion = await consultar(API_LOGIN);
            usuario.perfil = sesion.data;
            if (usuario.perfil.perfil_nombre !== "Alumno") {
                window.location.replace("index.html");
                return;
            }
            if (Number(usuario.perfil.cambio_contrasena_requerido) === 1) {
                window.location.replace("cambiarContrasena.html");
                return;
            }
            $("#nombreAlumno").textContent = usuario.perfil.nombre;
            await Promise.all([cargarEquipos(), cargarSolicitudes()]);
        } catch (error) {
            localStorage.removeItem("vitalab_usuario");
            window.location.replace("login.html");
        }
    }

    function rutaImagen(ruta) {
        if (/^images\/equipos\/[a-z0-9_-]+\.(svg|png|jpe?g|webp)$/i.test(String(ruta || ""))) {
            return ruta;
        }
        return "images/equipos/gradilla.svg";
    }

    async function cargarEquipos() {
        const respuesta = await consultar(API_CLIENTE);
        equipos = respuesta.data || [];
        const categorias = [...new Set(equipos.map(equipo => equipo.categoria))].sort((a, b) => a.localeCompare(b, "es"));
        $("#categoriaCatalogo").innerHTML = '<option value="">Todas las categorías</option>' + categorias.map(categoria => `<option value="${escapar(categoria)}">${escapar(categoria)}</option>`).join("");
        dibujarEquipos();
    }

    function dibujarEquipos() {
        const busqueda = sinTildes($("#buscarCatalogo").value.trim());
        const categoria = $("#categoriaCatalogo").value;
        const filtrados = equipos.filter(equipo => {
            const coincideTexto = !busqueda || sinTildes([equipo.nombre, equipo.codigo, equipo.descripcion].join(" ")).includes(busqueda);
            return coincideTexto && (!categoria || equipo.categoria === categoria);
        });

        $("#listaEquipos").innerHTML = filtrados.map(equipo => {
            const disponibles = Math.max(0, Number(equipo.disponibles));
            const agotado = disponibles < 1;
            const seleccionado = seleccion[equipo.id];
            return `<article class="cliente_producto">
                <div class="cliente_producto_imagen"><img src="${escapar(rutaImagen(equipo.imagen_url))}" alt="${escapar(equipo.nombre)}" loading="lazy"></div>
                <div class="cliente_producto_cuerpo">
                    <p class="cliente_producto_codigo">${escapar(equipo.codigo)}</p>
                    <h3>${escapar(equipo.nombre)}</h3>
                    <p class="cliente_producto_descripcion">${escapar(equipo.descripcion)}</p>
                    <p class="cliente_producto_categoria">${escapar(equipo.categoria)}</p>
                    <p class="cliente_producto_disponibilidad ${agotado ? "agotado" : "disponible"}">${agotado ? "Sin unidades disponibles" : disponibles + (disponibles === 1 ? " unidad disponible" : " unidades disponibles")}</p>
                    <div class="cliente_producto_acciones">
                        <label class="solo_lectores" for="cantidadEquipo${Number(equipo.id)}">Cantidad de ${escapar(equipo.nombre)}</label>
                        <input type="number" id="cantidadEquipo${Number(equipo.id)}" min="1" max="${disponibles}" value="${seleccionado ? seleccionado.cantidad : 1}" ${agotado ? "disabled" : ""}>
                        <button type="button" data-agregar="${Number(equipo.id)}" ${agotado ? "disabled" : ""}>${seleccionado ? "Actualizar cantidad" : "Agregar a solicitud"}</button>
                    </div>
                </div>
            </article>`;
        }).join("");

        $("#mensajeCatalogo").textContent = filtrados.length ? "" : "No hay equipos que coincidan con la búsqueda.";
    }

    function sinTildes(texto) {
        return String(texto).normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    function dibujarSeleccion() {
        const ids = Object.keys(seleccion);
        const unidades = ids.reduce((total, id) => total + seleccion[id].cantidad, 0);
        $("#cantidadSeleccionada").textContent = unidades + (unidades === 1 ? " unidad" : " unidades");
        $("#enviarSolicitud").disabled = ids.length === 0;
        if (!ids.length) {
            $("#detalleSeleccion").className = "cliente_seleccion_vacia";
            $("#detalleSeleccion").textContent = "Agrega equipos para preparar tu solicitud.";
            return;
        }
        $("#detalleSeleccion").className = "";
        $("#detalleSeleccion").innerHTML = `<ul class="cliente_seleccion_lista">${ids.map(id => {
            const equipo = seleccion[id];
            return `<li><span>${escapar(equipo.nombre)} × ${equipo.cantidad}</span><button type="button" data-quitar="${Number(id)}" aria-label="Quitar ${escapar(equipo.nombre)}">Quitar</button></li>`;
        }).join("")}</ul>`;
    }

    function dibujarSolicitudes() {
        const contenedor = $("#listaSolicitudes");
        if (!solicitudesAlumno.length) {
            contenedor.innerHTML = "";
            $("#mensajeSolicitudes").textContent = "Aún no has enviado solicitudes.";
            return;
        }
        $("#mensajeSolicitudes").textContent = "";
        contenedor.innerHTML = solicitudesAlumno.map(solicitud => `<article class="cliente_solicitud_fila">
            <h3>Solicitud #${Number(solicitud.id)} · <span class="cliente_estado">${escapar(solicitud.estado)}</span></h3>
            <p>Equipo: ${escapar(solicitud.equipos)}</p>
            <p>Enviada: ${escapar(solicitud.fecha_solicitud)} · Devolución esperada: ${escapar(solicitud.fecha_devolucion_esperada)}</p>
            ${solicitud.id_prestamo ? `<p>Préstamo registrado: #${Number(solicitud.id_prestamo)}</p>` : ""}
        </article>`).join("");
    }

    async function cargarSolicitudes() {
        const respuesta = await consultar(API_CLIENTE + "?solicitudes=1");
        solicitudesAlumno = respuesta.data || [];
        dibujarSolicitudes();
    }

    function establecerFechas() {
        const fecha = new Date();
        const hoy = new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000);
        const maximo = new Date(hoy);
        maximo.setDate(maximo.getDate() + 30);
        $("#fechaDevolucion").min = hoy.toISOString().slice(0, 10);
        $("#fechaDevolucion").max = maximo.toISOString().slice(0, 10);
    }

    $("#listaEquipos").addEventListener("click", evento => {
        const boton = evento.target.closest("[data-agregar]");
        if (!boton) return;
        const id = boton.dataset.agregar;
        const equipo = equipos.find(item => String(item.id) === id);
        const cantidad = Number($("#cantidadEquipo" + id).value);
        if (!equipo || !Number.isInteger(cantidad) || cantidad < 1 || cantidad > Number(equipo.disponibles)) {
            alert("La cantidad debe estar entre 1 y las unidades disponibles.");
            return;
        }
        seleccion[id] = { cantidad, nombre: equipo.nombre };
        dibujarSeleccion();
        dibujarEquipos();
    });

    $("#detalleSeleccion").addEventListener("click", evento => {
        const boton = evento.target.closest("[data-quitar]");
        if (!boton) return;
        delete seleccion[boton.dataset.quitar];
        dibujarSeleccion();
        dibujarEquipos();
    });

    $("#buscarCatalogo").addEventListener("input", dibujarEquipos);
    $("#categoriaCatalogo").addEventListener("change", dibujarEquipos);

    $("#enviarSolicitud").addEventListener("click", async () => {
        const fecha = $("#fechaDevolucion").value;
        if (!fecha) {
            $("#fechaDevolucion").focus();
            alert("Selecciona la fecha esperada de devolución.");
            return;
        }
        const lineas = Object.entries(seleccion).map(([id, equipo]) => id + ":" + equipo.cantidad).join(",");
        try {
            const respuesta = await consultar(API_CLIENTE, {
                method: "POST",
                body: new URLSearchParams({ fecha_devolucion_esperada: fecha, equipos: lineas })
            });
            alert(respuesta.message);
            Object.keys(seleccion).forEach(id => delete seleccion[id]);
            dibujarSeleccion();
            await Promise.all([cargarEquipos(), cargarSolicitudes()]);
            $("#fechaDevolucion").value = "";
        } catch (error) {
            alert(error.message);
            await cargarEquipos();
        }
    });

    function mostrarSeccion(nombre) {
        const esCatalogo = nombre === "catalogo";
        $("#seccionCatalogo").classList.toggle("oculta", !esCatalogo);
        $("#seccionSolicitudes").classList.toggle("oculta", esCatalogo);
        $("#mostrarCatalogo").classList.toggle("activa", esCatalogo);
        $("#mostrarSolicitudes").classList.toggle("activa", !esCatalogo);
        if (!esCatalogo) cargarSolicitudes().catch(error => { $("#mensajeSolicitudes").textContent = error.message; });
    }

    $("#mostrarCatalogo").addEventListener("click", () => mostrarSeccion("catalogo"));
    $("#mostrarSolicitudes").addEventListener("click", () => mostrarSeccion("solicitudes"));
    $("#cerrarSesionCliente").addEventListener("click", async () => {
        await fetch(API_LOGIN, { method: "DELETE", credentials: "same-origin" });
        localStorage.removeItem("vitalab_usuario");
        window.location.replace("login.html");
    });

    dibujarSeleccion();
    establecerFechas();
    iniciar();
})();
