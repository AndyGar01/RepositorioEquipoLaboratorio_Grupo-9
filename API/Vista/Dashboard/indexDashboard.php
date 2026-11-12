<div id="contenidoDashboard" class="inventario dashboard">
    <div class="inventario_encabezado">
        <div>
            <h1 class="titulo">Dashboard</h1>
            <p>Resumen del inventario, los préstamos y los cobros de VitaLab.</p>
        </div>
        <div class="inventario_ayuda">
            <img src="images/mascota_vitalab.png" alt="Mascota de VitaLab con una bata y un matraz de laboratorio">
        </div>
    </div>

    <div class="dashboard_barra">
        <div class="inventario_filtro">
            <label for="periodoDashboard">Periodo</label>
            <select id="periodoDashboard">
                <option value="todo">Todo el historial</option>
                <option value="30">Últimos 30 días</option>
                <option value="180">Últimos 6 meses</option>
                <option value="365">Último año</option>
            </select>
        </div>
        <button type="button" id="Actualizar_dashboard">Actualizar</button>
        <p id="actualizadoDashboard" class="inventario_mensaje_estado" aria-live="polite"></p>
    </div>
    <p id="mensajeDashboard" class="inventario_mensaje_estado" aria-live="polite"></p>

    <div id="dashboardContenido" class="oculto">
        <div class="dashboard_tarjetas">
            <button type="button" class="dashboard_tarjeta" data-destino="OctavaBoton" title="Ir a Equipos">
                <span class="dashboard_tarjeta_titulo">Unidades disponibles</span>
                <strong id="dashDisponibles">0</strong>
                <span id="dashDisponiblesDetalle" class="dashboard_tarjeta_detalle"></span>
            </button>
            <button type="button" class="dashboard_tarjeta" data-destino="NovenoBoton" title="Ir a Préstamos">
                <span class="dashboard_tarjeta_titulo">Préstamos en proceso</span>
                <strong id="dashEnProceso">0</strong>
                <span id="dashEnProcesoDetalle" class="dashboard_tarjeta_detalle"></span>
            </button>
            <button type="button" class="dashboard_tarjeta" data-destino="DecimoBoton" title="Ir a Devoluciones">
                <span class="dashboard_tarjeta_titulo">Préstamos vencidos</span>
                <strong id="dashVencidos">0</strong>
                <span id="dashVencidosDetalle" class="dashboard_tarjeta_detalle"></span>
            </button>
            <button type="button" class="dashboard_tarjeta" data-destino="NovenoBoton" title="Ir a Préstamos">
                <span class="dashboard_tarjeta_titulo">Solicitudes pendientes</span>
                <strong id="dashPendientes">0</strong>
                <span id="dashPendientesDetalle" class="dashboard_tarjeta_detalle"></span>
            </button>
            <button type="button" class="dashboard_tarjeta" data-destino="DecimoBoton" title="Ir a Devoluciones">
                <span class="dashboard_tarjeta_titulo">En reparación</span>
                <strong id="dashReparacion">0</strong>
                <span class="dashboard_tarjeta_detalle">unidades por reparar</span>
            </button>
            <button type="button" class="dashboard_tarjeta" data-destino="DecimoBoton" title="Ir a Devoluciones">
                <span class="dashboard_tarjeta_titulo">Cobros generados</span>
                <strong id="dashCobros">Q0.00</strong>
                <span id="dashCobrosDetalle" class="dashboard_tarjeta_detalle"></span>
            </button>
        </div>

        <div class="dashboard_cuadricula">
            <section class="dashboard_panel dashboard_panel_ancho">
                <h2>Préstamos y devoluciones</h2>
                <p id="dashSerieTexto" class="dashboard_subtitulo"></p>
                <div id="dashSerie" class="dashboard_grafica"></div>
                <ul class="dashboard_leyenda">
                    <li><span class="dashboard_muestra dashboard_turquesa"></span>Préstamos</li>
                    <li><span class="dashboard_muestra dashboard_azul"></span>Devoluciones</li>
                </ul>
                <p id="dashSerieVacio" class="inventario_mensaje_estado"></p>
            </section>

            <section class="dashboard_panel">
                <h2>Estado de los préstamos</h2>
                <p class="dashboard_subtitulo">Registrados en el periodo</p>
                <div id="dashEstados" class="dashboard_dona"></div>
            </section>

            <section class="dashboard_panel">
                <h2>Disponibilidad por categoría</h2>
                <p class="dashboard_subtitulo">Estado actual de las unidades</p>
                <ul class="dashboard_leyenda">
                    <li><span class="dashboard_muestra dashboard_turquesa"></span>Disponible</li>
                    <li><span class="dashboard_muestra dashboard_azul"></span>Prestado</li>
                    <li><span class="dashboard_muestra dashboard_rojo"></span>En reparación</li>
                    <li><span class="dashboard_muestra dashboard_gris"></span>Fuera de servicio</li>
                </ul>
                <ul id="dashCategorias" class="dashboard_lista"></ul>
            </section>

            <section class="dashboard_panel">
                <h2>Equipos más prestados</h2>
                <p class="dashboard_subtitulo">Unidades prestadas en el periodo</p>
                <ul id="dashMasPrestados" class="dashboard_lista"></ul>
            </section>

            <section class="dashboard_panel">
                <h2>Resumen del periodo</h2>
                <p class="dashboard_subtitulo">Alumnos, devoluciones y solicitudes</p>
                <dl id="dashResumen" class="dashboard_resumen"></dl>
            </section>

            <section class="dashboard_panel dashboard_panel_ancho">
                <h2>Devoluciones por atender</h2>
                <p class="dashboard_subtitulo">Préstamos vencidos o que vencen en los próximos 3 días</p>
                <div class="dashboard_tabla_scroll">
                    <table class="contenido dashboard_tabla">
                        <caption class="solo_lectores">Préstamos vencidos o próximos a vencer</caption>
                        <thead>
                            <tr>
                                <th scope="col">Préstamo</th>
                                <th scope="col">Alumno</th>
                                <th scope="col">Equipos</th>
                                <th scope="col">Devolución esperada</th>
                                <th scope="col">Situación</th>
                            </tr>
                        </thead>
                        <tbody id="dashTablaVencimientos"></tbody>
                    </table>
                </div>
                <p id="dashVencimientosNota" class="inventario_mensaje_estado"></p>
            </section>

            <section class="dashboard_panel">
                <h2>Solicitudes pendientes</h2>
                <p class="dashboard_subtitulo">Las más antiguas aparecen primero</p>
                <div class="dashboard_tabla_scroll">
                    <table class="contenido dashboard_tabla">
                        <caption class="solo_lectores">Solicitudes de alumnos pendientes de respuesta</caption>
                        <thead>
                            <tr>
                                <th scope="col">Alumno</th>
                                <th scope="col">Equipo solicitado</th>
                                <th scope="col">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="dashTablaPendientes"></tbody>
                    </table>
                </div>
                <p id="dashPendientesNota" class="inventario_mensaje_estado"></p>
            </section>
        </div>
    </div>
</div>
