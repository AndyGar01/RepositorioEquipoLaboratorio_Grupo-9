(function(){

const API = "/RepositorioEquipoLaboratorio_Grupo-9/API/Controlador/C_Dashboard.php";
const INTERVALO_ACTUALIZACION = 30000;
const MESES = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
const COLORES_ESTADO = { "En proceso": "#068988", "Completo": "#043D59", "No pagó": "#8A3030", "Incompleto": "#A8B4B8" };
let resumen = null;
let numeroConsulta = 0;

function escaparHTML(texto){
    return String(texto ?? "").replace(/[&<>"']/g, caracter => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
    })[caracter]);
}

function dinero(valor){
    return "Q" + Number(valor).toFixed(2);
}

function contar(cantidad, singular, plural){
    return cantidad + " " + (cantidad === 1 ? singular : plural);
}

function formatoFecha(texto){
    const partes = String(texto ?? "").slice(0, 10).split("-");
    return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : "";
}

function etiquetaSerie(clave, agrupacion){
    const partes = clave.split("-");
    const mes = MESES[Number(partes[1]) - 1];
    return agrupacion === "dia" ? `${Number(partes[2])} ${mes}` : mes;
}

function situacion(dias){
    if (dias > 0) return ["dashboard_situacion_vencido", `Vencido hace ${contar(dias, "día", "días")}`];
    if (dias === 0) return ["dashboard_situacion_proximo", "Vence hoy"];
    return ["dashboard_situacion_proximo", `Vence en ${contar(-dias, "día", "días")}`];
}

async function pedirResumen(){
    const periodo = $("#periodoDashboard").value;
    const solicitud = await fetch(`${API}?periodo=${encodeURIComponent(periodo)}`, {
        method: "GET",
        credentials: "same-origin",
        cache: "no-store"
    });
    const respuesta = await solicitud.json();
    if (!solicitud.ok) throw new Error(respuesta.message || "No se pudo obtener el resumen.");
    return respuesta.data;
}

function dibujarTarjetas(){
    const equipos = resumen.equipos;
    const prestamos = resumen.prestamos;
    const cobros = resumen.cobros;

    $("#dashDisponibles").textContent = equipos.disponibles;
    $("#dashDisponiblesDetalle").textContent = `de ${equipos.unidades} en inventario` + (equipos.agotados ? ` · ${contar(equipos.agotados, "tipo agotado", "tipos agotados")}` : "");

    $("#dashEnProceso").textContent = prestamos.en_proceso;
    $("#dashEnProcesoDetalle").textContent = contar(equipos.prestadas, "unidad fuera", "unidades fuera");

    $("#dashVencidos").textContent = prestamos.vencidos;
    $("#dashVencidosDetalle").textContent = `${prestamos.por_vencer} por vencer en 3 días`;
    $("#dashVencidos").closest(".dashboard_tarjeta").classList.toggle("dashboard_tarjeta_alerta", prestamos.vencidos > 0);

    $("#dashPendientes").textContent = resumen.pendientes.total;
    $("#dashPendientesDetalle").textContent = resumen.pendientes.total ? "esperan respuesta" : "todo al día";

    $("#dashReparacion").textContent = equipos.reparacion;

    $("#dashCobros").textContent = dinero(cobros.total);
    $("#dashCobrosDetalle").textContent = `${contar(cobros.cantidad, "cobro", "cobros")} · ${dinero(cobros.pendiente)} sin pagar`;
}

function dibujarSerie(){
    const serie = resumen.serie;
    const porDia = resumen.agrupacion === "dia";
    const ancho = 640, alto = 270, izquierda = 34, derecha = 8, arriba = 14, abajo = 40;
    const areaAncho = ancho - izquierda - derecha;
    const areaAlto = alto - arriba - abajo;
    let maximo = 0;
    let totalPrestamos = 0;
    let totalDevoluciones = 0;
    serie.forEach(punto => {
        maximo = Math.max(maximo, punto.prestamos, punto.devoluciones);
        totalPrestamos += punto.prestamos;
        totalDevoluciones += punto.devoluciones;
    });
    const limite = Math.max(4, Math.ceil(maximo / 4) * 4);

    let svg = `<svg viewBox="0 0 ${ancho} ${alto}" role="img" aria-label="Gráfica de préstamos y devoluciones: ${totalPrestamos} préstamos y ${totalDevoluciones} devoluciones en el periodo">`;
    for (let i = 0; i <= 4; i++){
        const valor = limite / 4 * i;
        const y = arriba + areaAlto - areaAlto * valor / limite;
        svg += `<line x1="${izquierda}" y1="${y}" x2="${ancho - derecha}" y2="${y}" stroke="#E1E7E9"/>`;
        svg += `<text x="${izquierda - 6}" y="${y + 4}" text-anchor="end" font-size="11" fill="#4D626B">${valor}</text>`;
    }

    const grupo = areaAncho / serie.length;
    const barra = Math.min(22, grupo * 0.34);
    const tipos = [["prestamos", "#068988", "préstamo", "préstamos"], ["devoluciones", "#043D59", "devolución", "devoluciones"]];
    serie.forEach((punto, i) => {
        const centro = izquierda + grupo * i + grupo / 2;
        const etiqueta = etiquetaSerie(punto.clave, resumen.agrupacion);
        tipos.forEach((tipo, k) => {
            const valor = punto[tipo[0]];
            const x = centro - barra - 1.5 + k * (barra + 3);
            const altoBarra = areaAlto * valor / limite;
            const y = arriba + areaAlto - altoBarra;
            if (valor > 0){
                svg += `<rect x="${x}" y="${y}" width="${barra}" height="${altoBarra}" rx="2" fill="${tipo[1]}"><title>${etiqueta}: ${contar(valor, tipo[2], tipo[3])}</title></rect>`;
                if (!porDia){
                    svg += `<text x="${x + barra / 2}" y="${y - 4}" text-anchor="middle" font-size="10" fill="#043D59">${valor}</text>`;
                }
            }
        });
        const base = alto - abajo + 16;
        if (porDia){
            if (i % 5 === 0){
                svg += `<text x="${centro}" y="${base}" text-anchor="middle" font-size="11" fill="#4D626B">${etiqueta}</text>`;
            }
        } else {
            svg += `<text x="${centro}" y="${base}" text-anchor="middle" font-size="11" fill="#4D626B">${etiqueta}</text>`;
            const anio = punto.clave.slice(0, 4);
            if (i === 0 || punto.clave.slice(5) === "01"){
                svg += `<text x="${centro}" y="${base + 13}" text-anchor="middle" font-size="10" fill="#4D626B">${anio}</text>`;
            }
        }
    });
    svg += `</svg>`;

    $("#dashSerie").innerHTML = svg;
    $("#dashSerieTexto").textContent = porDia ? "Últimos 30 días, por día" : `Últimos ${serie.length} meses, por mes`;
    $("#dashSerieVacio").textContent = totalPrestamos + totalDevoluciones ? "" : "No hay préstamos ni devoluciones en este periodo.";
}

function dibujarEstados(){
    const estados = resumen.estados;
    const total = estados.reduce((suma, item) => suma + item.total, 0);
    const radio = 52;
    const circunferencia = 2 * Math.PI * radio;
    let acumulado = 0;
    let anillo = `<circle cx="70" cy="70" r="${radio}" fill="none" stroke="#E1E7E9" stroke-width="20"/>`;
    estados.forEach(item => {
        if (item.total < 1) return;
        const largo = circunferencia * item.total / total;
        const color = COLORES_ESTADO[item.estado] ?? "#A8B4B8";
        anillo += `<circle cx="70" cy="70" r="${radio}" fill="none" stroke="${color}" stroke-width="20" stroke-dasharray="${largo} ${circunferencia - largo}" stroke-dashoffset="${-acumulado}" transform="rotate(-90 70 70)"><title>${escaparHTML(item.estado)}: ${item.total}</title></circle>`;
        acumulado += largo;
    });
    const descripcion = estados.map(item => `${item.estado}: ${item.total}`).join(", ");
    const svg = `<svg viewBox="0 0 140 140" role="img" aria-label="Préstamos por estado. ${escaparHTML(descripcion)}">${anillo}
        <text x="70" y="68" text-anchor="middle" font-size="26" fill="#043D59">${total}</text>
        <text x="70" y="86" text-anchor="middle" font-size="11" fill="#4D626B">${total === 1 ? "préstamo" : "préstamos"}</text></svg>`;
    const leyenda = estados.map(item => `<li><span class="dashboard_muestra" style="background:${COLORES_ESTADO[item.estado] ?? "#A8B4B8"}"></span>${escaparHTML(item.estado)} · ${item.total}</li>`).join("");
    $("#dashEstados").innerHTML = svg + `<ul class="dashboard_leyenda">${leyenda}</ul>`;
}

function dibujarCategorias(){
    const lista = resumen.categorias;
    if (!lista.length){
        $("#dashCategorias").innerHTML = `<li class="dashboard_vacio_texto">No hay equipos registrados.</li>`;
        return;
    }
    $("#dashCategorias").innerHTML = lista.map(item => {
        const total = (item.disponibles + item.prestadas + item.reparacion + item.fuera) || 1;
        const segmento = (cantidad, clase, nombre) => cantidad ? `<span class="${clase}" style="width:${cantidad * 100 / total}%" title="${nombre}: ${cantidad}"></span>` : "";
        return `<li>
            <div class="dashboard_fila_texto"><span>${escaparHTML(item.nombre)}</span><span>${item.disponibles} de ${total} disponibles</span></div>
            <div class="dashboard_apilada" role="img" aria-label="${escaparHTML(item.nombre)}: ${item.disponibles} disponibles, ${item.prestadas} prestadas, ${item.reparacion} en reparación, ${item.fuera} fuera de servicio">
                ${segmento(item.disponibles, "dashboard_turquesa", "Disponible")}${segmento(item.prestadas, "dashboard_azul", "Prestado")}${segmento(item.reparacion, "dashboard_rojo", "En reparación")}${segmento(item.fuera, "dashboard_gris", "Fuera de servicio")}
            </div></li>`;
    }).join("");
}

function dibujarMasPrestados(){
    const lista = resumen.mas_prestados;
    if (!lista.length){
        $("#dashMasPrestados").innerHTML = `<li class="dashboard_vacio_texto">Aún no hay préstamos en este periodo.</li>`;
        return;
    }
    const mayor = lista[0].total;
    $("#dashMasPrestados").innerHTML = lista.map(item => `<li>
        <div class="dashboard_fila_texto"><span>${escaparHTML(item.codigo)} · ${escaparHTML(item.nombre)}</span><span>${contar(item.total, "unidad", "unidades")}</span></div>
        <div class="dashboard_pista"><span style="width:${item.total * 100 / mayor}%"></span></div></li>`).join("");
}

function dibujarResumen(){
    const alumnos = resumen.alumnos;
    const devoluciones = resumen.devoluciones;
    const solicitudes = resumen.solicitudes;
    const porcentaje = devoluciones.devueltas ? Math.round(devoluciones.afectadas * 100 / devoluciones.devueltas) : 0;
    const filas = [
        ["Alumnos activos", alumnos.activos],
        ["Alumnos con préstamo en proceso", alumnos.con_prestamo],
        ["Devoluciones registradas", devoluciones.cantidad],
        ["Unidades dañadas o incompletas", `${devoluciones.afectadas} de ${devoluciones.devueltas} (${porcentaje}%)`],
        ["Solicitudes aprobadas", solicitudes.aprobadas],
        ["Solicitudes rechazadas", solicitudes.rechazadas]
    ];
    $("#dashResumen").innerHTML = filas.map(fila => `<div><dt>${fila[0]}</dt><dd>${fila[1]}</dd></div>`).join("");
}

function dibujarVencimientos(){
    const lista = resumen.vencimientos;
    $("#dashTablaVencimientos").innerHTML = lista.length ? lista.map(item => {
        const estado = situacion(item.dias);
        return `<tr><td>${Number(item.id)}</td><td>${escaparHTML(item.alumno)}</td><td>${escaparHTML(item.equipos)}</td><td>${formatoFecha(item.fecha_esperada)}</td><td class="${estado[0]}">${estado[1]}</td></tr>`;
    }).join("") : `<tr><td colspan="5" class="dashboard_vacio_texto">No hay préstamos vencidos ni por vencer.</td></tr>`;
    const total = resumen.prestamos.vencidos + resumen.prestamos.por_vencer;
    $("#dashVencimientosNota").textContent = total > lista.length ? `Se muestran ${lista.length} de ${total}. El resto está en Devoluciones.` : "";
}

function dibujarPendientes(){
    const pendientes = resumen.pendientes;
    $("#dashTablaPendientes").innerHTML = pendientes.lista.length ? pendientes.lista.map(item => `<tr>
        <td>${escaparHTML(item.alumno)}</td><td>${escaparHTML(item.equipos)}</td><td>${formatoFecha(item.fecha)}</td></tr>`).join("")
        : `<tr><td colspan="3" class="dashboard_vacio_texto">No hay solicitudes pendientes.</td></tr>`;
    $("#dashPendientesNota").textContent = pendientes.total > pendientes.lista.length ? `Se muestran ${pendientes.lista.length} de ${pendientes.total}. El resto está en Préstamos.` : "";
}

async function CargarDashboard(){
    const consulta = ++numeroConsulta;
    try {
        const datos = await pedirResumen();
        if (consulta !== numeroConsulta) return;
        resumen = datos;
        dibujarTarjetas();
        dibujarSerie();
        dibujarEstados();
        dibujarCategorias();
        dibujarMasPrestados();
        dibujarResumen();
        dibujarVencimientos();
        dibujarPendientes();
        $("#dashboardContenido").classList.remove("oculto");
        $("#mensajeDashboard").textContent = "";
        $("#actualizadoDashboard").textContent = "Actualizado a las " + new Date().toLocaleTimeString("es-GT");
    } catch (error) {
        if (consulta !== numeroConsulta) return;
        console.error("Error al cargar el dashboard:", error);
        $("#mensajeDashboard").textContent = error.name === "Error" ? error.message : "No se pudo cargar el dashboard.";
    }
}

function dashboardVisible(){
    return !$("#PrimerContenido").classList.contains("oculto") && !document.hidden;
}

$(".dashboard_tarjetas").addEventListener("click", evento => {
    const tarjeta = evento.target.closest(".dashboard_tarjeta");
    if (tarjeta){
        document.getElementById(tarjeta.dataset.destino).click();
    }
});
$("#periodoDashboard").addEventListener("change", CargarDashboard);
$("#Actualizar_dashboard").addEventListener("click", CargarDashboard);
document.addEventListener("actualizarDashboard", CargarDashboard);
document.addEventListener("visibilitychange", () => {
    if (dashboardVisible()) CargarDashboard();
});
setInterval(() => {
    if (dashboardVisible()) CargarDashboard();
}, INTERVALO_ACTUALIZACION);

CargarDashboard();

})();
