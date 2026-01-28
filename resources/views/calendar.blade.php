@extends('layouts.user-panel')

@push('styles')
    <link href="{{ asset('vendor/fullcalendar/lib/main.min.css') }}" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        #calendar {
            min-height: 650px;
        }

        #calendar {
            min-height: 700px;
        }

        /* tus estilos existentes */
        /* Calendario compacto */
        #calendar {
            font-size: 0.875rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
        }

        .fc .fc-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 60px;
        }

        .fc-event {
            padding: 1px 3px;
            margin: 1px 0;
            font-size: 0.75rem;
        }

        .fc-timegrid-slot {
            height: 1.5em;
        }

        .sidebar-actions {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            height: 100%;
        }

        .quick-action-btn {
            width: 100%;
            margin-bottom: 0.5rem;
            text-align: left;
            padding: 0.5rem 0.75rem;
        }

        .event-preview {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        /*-----------------+NUEVOS ESRTILOS PARA CALENDARIO DE PRODUCCION+-----------------*/
        .codigo-row {
            background: #f8f9fa;
            transition: all 0.2s;
        }

        .codigo-row:hover {
            background: #e9ecef;
        }


        /* Estilo para fila allDay */
        .fc .fc-timegrid-axis-cushion,
        .fc .fc-timegrid-slot-label-cushion {
            font-weight: 600;
        }

        /* Eventos allDay más visibles */
        .fc-event.fc-event-all-day {
            border-left: 4px solid #666;
            font-size: 0.8rem;
        }

        /* En vista mensual */
        .fc-daygrid-day-events .fc-event {
            margin: 1px 0;
        }


        /*ESTILOS HACWR CLICK EN LA FECHA*/
        /* ESTILOS PARA FECHA SELECCIONADA */
        .fc-day-selected,
        .fc-day-highlight {
            background-color: rgba(59, 130, 246, 0.1) !important;
            border: 2px solid #3b82f6 !important;
            position: relative;
        }

        .fc-day-highlight::after {
            content: "📍";
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 12px;
            z-index: 10;
        }

        .fc-timegrid-col-selected {
            background-color: rgba(59, 130, 246, 0.15) !important;
            border-left: 3px solid #3b82f6 !important;
        }

        /* ANIMACIÓN DE DESTELLO */
        @keyframes pulse-date {
            0% {
                background-color: rgba(59, 130, 246, 0.1);
            }

            50% {
                background-color: rgba(59, 130, 246, 0.2);
            }

            100% {
                background-color: rgba(59, 130, 246, 0.1);
            }
        }

        .fc-day-selected {
            animation: pulse-date 2s infinite;
        }

        /* NOTIFICACIÓN MEJORADA */
        #fecha-seleccionada-notif {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid #3b82f6;
        }
    </style>
@endpush

@section('content')
    <!-- Sidebar acciones rápidas (1/4 ancho) -->
    <div class="col-lg-3 col-md-4">
        <div class="sidebar-actions">
            <!-- Botón para abrir modal de evento -->
            <button class="btn btn-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#eventModal">
                <i class="fas fa-plus me-1"></i> Nuevo Embarque.
            </button>

            <!-- Información del evento actual -->
            <input type="hidden" id="currentEventId" value="">
        </div>
    </div>
    <div class="row">
        <!-- Calendario principal (100% ancho) -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>


    </div>

    <!-- Eventos de hoy -->
    <div class="mt-4">
        <h6 class="mb-2">📅 Hoy</h6>
        <div class="list-group" id="todayEvents">
            <div class="list-group-item list-group-item-action py-2">
                <small class="text-primary">10:00 AM</small>
                <div>Reunión de equipo</div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Modal para formulario de evento -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">📦 Nueva Factura.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Fecha Entrega</label>
                            <input type="date" class="form-control form-control-sm" id="fechaEntrega"
                                value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Fecha Producción</label>
                            <input type="date" class="form-control form-control-sm" id="fechaProduccion"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Cliente</label>
                        <input type="text" class="form-control form-control-sm" id="cliente"
                            placeholder="Nombre del cliente">
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-3">
                            <label class="form-label small">Orden de Producción</label>
                            <input type="text" class="form-control form-control-sm" id="opNumber"
                                placeholder="OP-001">
                        </div>
                        <div class="col-3">
                            <label class="form-label small">Factura</label>
                            <input type="text" class="form-control form-control-sm" id="factura" placeholder="FP-">
                        </div>
                               <div class="col-3">
                            <label class="form-label small">Orden de Compra</label>
                            <input type="text" class="form-control form-control-sm" id="ordencompra"
                                placeholder="Orden de compra">
                        </div>
                    </div>
                             <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Descripción</label>
                            <input type="text" class="form-control form-control-sm" id="descripcion"
                                placeholder="Descripción">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Código</label>
                            <input type="text" class="form-control form-control-sm" id="codigo"
                                placeholder="Código">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Cantidad Req.</label>
                            <input type="number" class="form-control form-control-sm" id="cantidadReq" placeholder="0"
                                min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Cantidad Factura</label>
                            <input type="number" class="form-control form-control-sm" id="cantidadFact" placeholder="0"
                                min="1">
                        </div>
                    </div>
           
                    <div class="row g-2 mb-2">
                  <!--VACIO -->
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Estado</label>
                        <select class="form-select form-select-sm" id="eventStatus">
                            <option value="pendiente">🟡 Pendiente</option>
                            <option value="en_progreso">🔵 En Progreso</option>
                            <option value="completado">🟢 Completado</option>
                            <option value="retrasado">🔴 Cancelado</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="eventDate"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-3">
                            <label class="form-label small">Inicio</label>
                            <input type="time" class="form-control form-control-sm" id="startTime" value="08:00"
                                step="300">
                        </div>
                        <div class="col-3">
                            <label class="form-label small">Fin</label>
                            <input type="time" class="form-control form-control-sm" id="endTime" value="17:00"
                                step="300">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="updateBtn" onclick="updateProductionEvent()"
                        style="display: none;">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                    <button class="btn btn-sm btn-danger" id="deleteBtn" onclick="deleteCurrentEvent()"
                        style="display: none;">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                    <button class="btn btn-sm btn-primary" id="saveBtn" onclick="saveProductionEvent()">
                        <i class="fas fa-plus me-1"></i> Crear Nuevo
                    </button>
                </div>
                <hr>

                <div id="pdf-section" class="d-none">
                    <div class="row g-2">

                        <div class="col-md-6">
                            <label class="form-label">Factura (PDF)</label>
                            <input type="file" id="pdf_factura" class="form-control" accept="application/pdf">
                            <button type="button"
                                class="btn btn-primary btn-sm mt-2"
                                onclick="subirPdf('factura')">
                                Guardar Factura
                            </button>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Certificado (PDF)</label>
                            <input type="file" id="pdf_certificado" class="form-control" accept="application/pdf">
                            <button type="button"
                                class="btn btn-success btn-sm mt-2"
                                onclick="subirPdf('certificado')">
                                Guardar Certificado
                            </button>
                        </div>

                    </div>
                    <div id="pdf-viewer" class="mt-3 d-none">

                        <div class="d-flex gap-3">

                            <div id="ver-factura" class="d-none">
                                📄
                                <a href="#" target="_blank" id="link-factura"></a>
                            </div>

                            <div id="ver-certificado" class="d-none">
                                📄
                                <a href="#" target="_blank" id="link-certificado"></a>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/fullcalendar/lib/main.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/lib/locales/es.js') }}"></script>
    <script>
        // ==================== CONFIGURACIÓN GLOBAL ====================
        let calendar;
        let currentEvent = null;
        //let codigosCounter = 0;
        // Variable global para guardar la última fecha seleccionada
        let ultimaFechaSeleccionada = null;
        const eventosCargados = new Set();
        let primeraCarga = true;

        let selectedEventId = null;
        async function pollingEventos() {
            try {
                const res = await fetch('/api/calendar/events');
                const eventos = await res.json();

                let nuevos = 0;

                eventos.forEach(ev => {
                    const id = String(ev.id);
                    if (!eventosCargados.has(id)) {
                        eventosCargados.add(id);
                        if (!primeraCarga) nuevos++;
                    }
                });

                if (nuevos > 0) {
                    showToast(`📢 ${nuevos} nuevo(s) evento(s)`, 'info');
                    calendar.refetchEvents();
                    console(eventos);
                }
                calendar.refetchEvents();
                // 🔥 LIMPIA Y RECARGA
                //calendar.getEvents().forEach(e => e.remove());


                primeraCarga = false;

            } catch (e) {
                console.warn('Polling falló');
            }
        }
        // ==================== UTILIDADES: Toasts y escape ====================
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function showToast(message, type = 'info', timeout = 3000) {
            const container = document.getElementById('toastContainer');
            if (!container) {
                // Fallback a alert si no existe el contenedor
                try {
                    alert(message);
                } catch (e) {
                    console.log(message);
                }
                return;
            }

            const bgClass = (type === 'success') ? 'bg-success text-white' :
                (type === 'danger') ? 'bg-danger text-white' :
                (type === 'warning') ? 'bg-warning text-dark' : 'bg-info text-white';

            const toast = document.createElement('div');
            toast.className = `toast align-items-center ${bgClass} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.dataset.bsDelay = timeout;

            const inner = document.createElement('div');
            inner.className = 'd-flex';

            const body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = message;

            const btnClose = document.createElement('button');
            btnClose.type = 'button';
            btnClose.className = 'btn-close btn-close-white me-2 m-auto';
            btnClose.dataset.bsDismiss = 'toast';
            btnClose.setAttribute('aria-label', 'Close');

            inner.appendChild(body);
            inner.appendChild(btnClose);

            toast.appendChild(inner);
            container.appendChild(toast);

            try {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                toast.addEventListener('hidden.bs.toast', () => toast.remove());
            } catch (e) {
                // Fallback a alert si bootstrap no está disponible
                alert(message);
            }
        }
        // ==================== FUNCIÓN PARA MOSTRAR FECHA SELECCIONADA ====================
        function mostrarSeleccionFecha(fecha, vista = 'mensual') {
            // 1. Quitar selección anterior
            document.querySelectorAll('.fc-day-selected').forEach(el => {
                el.classList.remove('fc-day-selected', 'fc-day-highlight');
            });

            document.querySelectorAll('.fc-timegrid-col-selected').forEach(el => {
                el.classList.remove('fc-timegrid-col-selected', 'fc-timegrid-highlight');
            });

            // 2. Crear fecha a partir del parámetro
            const fechaObj = new Date(fecha);

            // 3. Guardar para uso posterior
            ultimaFechaSeleccionada = fechaObj;

            // 4. Mostrar en consola para debug
            console.log('📍 Fecha seleccionada:', fechaObj.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));

            // 5. ACTUALIZAR FORMULARIO CON FECHA SELECCIONADA
            // Solo actualizar si el formulario está en modo "Nuevo"
            if (!currentEvent) {
                const fechaFormatoInput = fechaObj.toISOString().split('T')[0];
                document.getElementById('eventDate').value = fechaFormatoInput;

                // Mostrar notificación visual
                mostrarNotificacionFecha(fechaObj);
            }

            // ✅ ACTUALIZAR DISPLAY EN SIDEBAR
            const fechaDisplay = document.getElementById('fechaActualDisplay');
            if (fechaDisplay) {
                fechaDisplay.textContent = fechaObj.toLocaleDateString('es-ES', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
            // 6. Resaltar visualmente en calendario
            setTimeout(() => {
                const cellSelector = vista === 'mensual' ?
                    `.fc-day[data-date="${fechaObj.toISOString().split('T')[0]}"]` :
                    `.fc-timegrid-col[data-date="${fechaObj.toISOString().split('T')[0]}"]`;

                const celda = document.querySelector(cellSelector);
                if (celda) {
                    celda.classList.add('fc-day-selected', 'fc-day-highlight');
                }
            }, 50);
        }
        // =================== NOTIFICACIÓN VISUAL ====================
        function mostrarNotificacionFecha(fecha) {
            // Crear o actualizar notificación
            let notificacion = document.getElementById('fecha-seleccionada-notif');

            if (!notificacion) {
                notificacion = document.createElement('div');
                notificacion.id = 'fecha-seleccionada-notif';
                notificacion.className = 'alert alert-info alert-dismissible fade show';
                notificacion.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 9999;
            max-width: 300px;
             `;

                document.body.appendChild(notificacion);
            }

            const opcionesFecha = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };

            notificacion.innerHTML = `
              <strong>📅 Fecha seleccionada:</strong><br>
              <small>${fecha.toLocaleDateString('es-ES', opcionesFecha)}</small>
              <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
          `;

            // Auto-ocultar después de 3 segundos
            setTimeout(() => {
                if (notificacion && notificacion.parentNode) {
                    notificacion.remove();
                }
            }, 3000);
        }
        // ==================== FUNCIONES AUXILIARES ====================
        function getColorByStatus(estado) {
            const colores = {
                'pendiente': '#f59e0b',
                'en_progreso': '#3b82f6',
                'completado': '#10b981',
                'cancelado': '#ef4444'
            };
            return colores[estado] || '#6b7280';
        }

        function getColorByStatus_db(estado) {
            switch (estado) {
                case 'pendiente':
                    return '#facc15'; // 🟡
                case 'en_progreso':
                    return '#3b82f6'; // 🔵
                case 'completado':
                    return '#22c55e'; // 🟢
                case 'cancelado':
                    return '#ef4444'; // 🔴
                default:
                    return '#9ca3af'; // gris
            }
        }
        // ==================== FUNCIONES DEL FORMULARIO ====================
        function loadEventToForm(event) {
            currentEvent = event;
            const props = event.extendedProps || {};
            // Configurar título del modal
            document.getElementById('eventModalLabel').textContent = '📦 Editando: ' + event.title;
            document.getElementById('currentEventId').value = event.id;
            // Datos básicos
            document.getElementById('descripcion').value = props.descripcion || '';
            document.getElementById('codigo').value = props.codigo || '';
            document.getElementById('ordencompra').value = props.ordencompra || '';
            document.getElementById('factura').value = props.factura || '';
            document.getElementById('cantidadFact').value = props.cantidadFact || 0;
            document.getElementById('opNumber').value = event.title;
            document.getElementById('cliente').value = props.cliente || '';
            document.getElementById('cantidadReq').value = props.cantidadReq || '';
            document.getElementById('fechaEntrega').value = props.fechaEntrega || '';
            document.getElementById('fechaProduccion').value = props.fechaProduccion || '';
            // Fecha y hora del evento
            if (event.start) {
                const fecha = event.start.toISOString().split('T')[0];
                document.getElementById('eventDate').value = fecha;

                if (!event.allDay && event.start) {
                    const horaInicio = event.start.toTimeString().substring(0, 5);
                    document.getElementById('startTime').value = horaInicio;
                } else {
                    document.getElementById('startTime').value = '00:00';
                }
            }
            if (event.end && !event.allDay) {
                const horaFin = event.end.toTimeString().substring(0, 5);
                document.getElementById('endTime').value = horaFin;
            } else {
                document.getElementById('endTime').value = '01:00';
            }
            // Estado
            document.getElementById('eventStatus').value = props.estado || 'pendiente';

            // Botones
            document.getElementById('saveBtn').style.display = 'none';
            document.getElementById('updateBtn').style.display = 'block';
            document.getElementById('deleteBtn').style.display = 'block';

            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        }

        function resetToNewEvent() {
            currentEvent = null;
            // Resetear título del modal
            document.getElementById('eventModalLabel').textContent = '📦 Nuevo Embarque.';
            document.getElementById('currentEventId').value = '';
            document.getElementById('opNumber').value = '';
            document.getElementById('cliente').value = '';
            document.getElementById('cantidadReq').value = '';
            document.getElementById('fechaEntrega').value = '';
            document.getElementById('fechaProduccion').value = new Date().toISOString().split('T')[0];
            document.getElementById('eventDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('startTime').value = '08:00';
            document.getElementById('endTime').value = '17:00';
            document.getElementById('eventStatus').value = 'pendiente';
            document.getElementById('factura').value = '';
            document.getElementById('cantidadFact').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('codigo').value = '';
            document.getElementById('ordencompra').value = '';

            // Botones
            document.getElementById('saveBtn').style.display = 'block';
            document.getElementById('updateBtn').style.display = 'none';
            document.getElementById('deleteBtn').style.display = 'none';

            // Cerrar modal si está abierto
            const modalEl = document.getElementById('eventModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        }
        // ==================== CREAR EVENTO (allDay por defecto) ====================
        async function saveProductionEvent() {
            // 1. Validar OP
            const opNumber = document.getElementById('opNumber').value.trim();
            if (!opNumber) {
                showToast('❌ Ingresa un número de OP', 'danger');
                return;
            }

            // 2. ✅ ASEGURAR QUE SE USA LA FECHA SELECCIONADA
            let fechaEvento;
            if (ultimaFechaSeleccionada) {
                // Usar la fecha seleccionada por el usuario
                fechaEvento = ultimaFechaSeleccionada.toISOString().split('T')[0];
                console.log('📅 Usando fecha seleccionada:', fechaEvento);
            } else {
                // Fallback a la fecha del formulario
                fechaEvento = document.getElementById('eventDate').value;
                console.log('⚠️ Usando fecha del formulario:', fechaEvento);
            }
            // Validar que tenemos fecha
            if (!fechaEvento) {
                showToast('❌ No hay fecha seleccionada. Haz click en una fecha del calendario primero.', 'danger');
                return;
            }
            // 3. Obtener códigos
            /*const codigos = getCodigosFromForm();
            if (codigos.length === 0) {
                showToast('❌ Agrega al menos un producto', 'danger');
                return;
            }*/
            // 4. Crear evento allDay por defecto EN LA FECHA SELECCIONADA
            const eventoData = {
                title: opNumber,
                start: fechaEvento, // ✅ Fecha seleccionada por el usuario
                allDay: true,
                color: getColorByStatus('pendiente'),
                extendedProps: {
                    cliente: document.getElementById('cliente').value || '',
                    cantidadReq: parseInt(document.getElementById('cantidadReq').value) || 0,
                    fechaEntrega: document.getElementById('fechaEntrega').value || '',
                    fechaProduccion: document.getElementById('fechaProduccion').value || '',

                    factura: document.getElementById('factura').value || '',
                    cantidadFact: document.getElementById('cantidadFact').value || '',
                    descripcion: document.getElementById('descripcion').value || '',
                    codigo: document.getElementById('codigo').value || '',
                    ordencompra: document.getElementById('ordencompra').value || '',

                    estado: 'pendiente',
                    //codigos: codigos,
                    tipo: 'production'
                }
            };
            // 5. Agregar al calendario
            const nuevoEvento = calendar.addEvent(eventoData);

            const eventId = 'event_' + Date.now();
            nuevoEvento.setProp('id', eventId);
            // 6. Guardar en base de datos
            const resultado = await guardarEventoEnDB({
                id: 'temp_' + Date.now(),
                ...eventoData
            }, false);

            // Actualizar ID con el real de la DB
            if (resultado && resultado.id) {
                nuevoEvento.setProp('id', resultado.id);
            }
            // 7. Cargar para editar
            //loadEventToForm(nuevoEvento);
            calendar.getEvents().forEach(e => e.remove());
            calendar.refetchEvents();
            // 8. Feedback al usuario
            showToast(
                `✅ OP ${escapeHtml(opNumber)} creada para el ${new Date(fechaEvento).toLocaleDateString('es-ES')}`,
                'success');
            // 9. Cerrar modal
            const modalEl = document.getElementById('eventModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
            // 10. Actualizar y limpiar
            updateTodayEvents();
            // 11. Limpiar selección visual
            setTimeout(() => {
                document.querySelectorAll('.fc-day-selected, .fc-timegrid-col-selected').forEach(el => {
                    el.classList.remove('fc-day-selected', 'fc-day-highlight',
                        'fc-timegrid-col-selected', 'fc-timegrid-highlight');
                });
                ultimaFechaSeleccionada = null;
            }, 500);
        }
        // ==================== ACTUALIZAR EVENTO (solo datos del formulario) ====================
        async function updateProductionEvent() {
            if (!currentEvent) {
                showToast('❌ No hay evento seleccionado', 'danger');
                return;
            }

            const opNumber = document.getElementById('opNumber').value.trim();
            if (!opNumber) {
                showToast('❌ Ingresa un número de OP', 'danger');
                return;
            }

            /*const codigos = getCodigosFromForm();
            if (codigos.length === 0) {
                showToast('❌ Agrega al menos un producto', 'danger');
                return;
            }*/

            // ✅ NO actualizar fecha/hora - solo datos del formulario
            const eventoData = {
                id: currentEvent.id,
                title: opNumber,
                start: currentEvent.start, // ✅ Mantener fecha/hora actual
                end: currentEvent.end, // ✅ Mantener fecha/hora actual
                allDay: currentEvent.allDay, // ✅ Mantener tipo
                color: getColorByStatus(document.getElementById('eventStatus').value),
                extendedProps: {
                    cliente: document.getElementById('cliente').value || '',
                    cantidadReq: parseInt(document.getElementById('cantidadReq').value) || 0,
                    fechaEntrega: document.getElementById('fechaEntrega').value || '',
                    fechaProduccion: document.getElementById('fechaProduccion').value || '',
                    estado: document.getElementById('eventStatus').value,
                    factura: document.getElementById('factura').value || '',
                    cantidadFact: document.getElementById('cantidadFact').value || '',
                    descripcion: document.getElementById('descripcion').value || '',
                    codigo: document.getElementById('codigo').value || '',
                    ordencompra: document.getElementById('ordencompra').value || '',
                    tipo: 'production'
                }
            };

            // Actualizar solo propiedades del evento (no fecha/hora)
            currentEvent.setProp('title', eventoData.title);
            currentEvent.setProp('color', eventoData.color);

            // Actualizar extendedProps
            Object.keys(eventoData.extendedProps).forEach(key => {
                currentEvent.setExtendedProp(key, eventoData.extendedProps[key]);
            });


            await guardarEventoEnDB(eventoData, true); // Guardar en base de datos

            showToast(`✅ OP ${escapeHtml(opNumber)} actualizada`, 'success');
            updateTodayEvents();
            resetToNewEvent();
        }
        // ==================== ELIMINAR EVENTO ====================
        async function deleteCurrentEvent() {
            if (!currentEvent) return;

            if (confirm(`¿Estás seguro de eliminar la OP "${currentEvent.title}"?`)) {
                // Eliminar de la base de datos
                await eliminarEventoDeDB(currentEvent.id);

                // Eliminar del calendario
                currentEvent.remove();

                showToast('🗑️ Evento eliminado', 'success');
                resetToNewEvent();
                updateTodayEvents();
            }
        }
        // ==================== FUNCIONES ADICIONALES ====================
        function updateTodayEvents() {
            const today = new Date().toDateString();
            const events = calendar.getEvents();
            const todayEvents = events.filter(event =>
                event.start && event.start.toDateString() === today
            );

            const container = document.getElementById('todayEvents');
            if (todayEvents.length === 0) {
                container.innerHTML = `
            <div class="list-group-item py-3 text-center text-muted">
                <small>No hay eventos para hoy</small>
            </div>
            `;
                return;
            }

            let html = '';
            todayEvents.forEach(event => {
                // ✅ FORMATO 24 HORAS
                const time = event.allDay ? 'Todo el día' :
                    event.start.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false // ✅ 24 horas
                    });

                const estado = event.extendedProps?.estado || 'pendiente';
                const estadoColor = estado === 'completado' ? 'success' :
                    estado === 'en_progreso' ? 'primary' :
                    estado === 'retrasado' ? 'danger' : 'warning';

                const safeTitle = escapeHtml(event.title);
                const safeEstado = escapeHtml(estado);
                const safeId = escapeHtml(event.id);

                html += `
            <div class="list-group-item list-group-item-action py-2" 
                 onclick="loadEventById('${safeId}')" 
                 style="cursor: pointer;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-primary">${time}</small>
                        <div>${safeTitle}</div>
                        <span class="badge bg-${estadoColor}">${safeEstado}</span>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="event.stopPropagation(); removeEvent('${safeId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
          `;
            });

            container.innerHTML = html;
        }

        function loadEventById(eventId) {
            const event = calendar.getEventById(eventId);
            if (event) {
                loadEventToForm(event);
                document.querySelector('.sidebar-actions').scrollTop = 0;
            }
        }

        async function removeEvent(eventId) {
            const event = calendar.getEventById(eventId);
            if (event && confirm(`¿Eliminar "${event.title}"?`)) {
                // Eliminar de la base de datos
                await eliminarEventoDeDB(eventId);

                // Eliminar del calendario
                event.remove();

                // Si es el evento en edición, resetear
                if (currentEvent && currentEvent.id === eventId) {
                    resetToNewEvent();
                }

                updateTodayEvents();
            }
        }
        // ==================== NAVEGAR A VISTA DE FECHA SELECCIONADA ====================
        function irAVistaFecha(fecha, vista = 'diaria') {
            if (!fecha) {
                showToast('❌ Primero selecciona una fecha haciendo click en el calendario', 'warning');
                return;
            }

            const fechaObj = new Date(fecha);

            // Validar que sea una fecha válida
            if (isNaN(fechaObj.getTime())) {
                showToast('❌ Fecha no válida', 'danger');
                return;
            }

            console.log(`🚀 Navegando a vista ${vista} de:`, fechaObj.toLocaleDateString('es-ES'));

            // Cambiar a la vista solicitada
            switch (vista) {
                case 'diaria':
                    calendar.changeView('timeGridDay', fechaObj);
                    console.log('📅 Vista diaria cargada');
                    break;

                case 'semanal':
                    calendar.changeView('timeGridWeek', fechaObj);
                    console.log('📅 Vista semanal cargada');
                    break;

                case 'mensual':
                    calendar.changeView('dayGridMonth', fechaObj);
                    console.log('📅 Vista mensual cargada');
                    break;

                default:
                    showToast('❌ Vista no válida', 'danger');
                    return;
            }

            // Resaltar la fecha en la nueva vista
            setTimeout(() => {
                mostrarSeleccionFecha(fecha, vista === 'mensual' ? 'mensual' : 'semanal');
            }, 300);

            // Feedback al usuario
            mostrarNotificacionNavegacion(fechaObj, vista);
        }
        // ==================== NOTIFICACIÓN DE NAVEGACIÓN ====================
        function mostrarNotificacionNavegacion(fecha, vista) {
            let notificacion = document.getElementById('navegacion-notif');

            if (!notificacion) {
                notificacion = document.createElement('div');
                notificacion.id = 'navegacion-notif';
                notificacion.className = 'alert alert-success alert-dismissible fade show';
                notificacion.style.cssText = `
            position: fixed;
            top: 110px;
            right: 20px;
            z-index: 9999;
            max-width: 300px;
             `;

                document.body.appendChild(notificacion);
            }

            const textoVista = {
                'diaria': 'vista diaria',
                'semanal': 'vista semanal',
                'mensual': 'vista mensual'
            } [vista] || 'vista';

            notificacion.innerHTML = `
          <strong>🚀 Navegando a ${textoVista}</strong><br>
           <small>${fecha.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
             })}</small>
          <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
         `;

            setTimeout(() => notificacion.remove(), 2500);
        }

        /*--------------------------------------------------DATA BASE SECCION------------------------------------------------------------------------------------------*/
        /**
         * Cargar eventos desde la base de datos
         */
        async function cargarEventosDesdeDB() {
            try {
                const response = await fetch('/api/calendar/events');
                const eventos = await response.json();

                // Ajustar fechas para FullCalendar
                const eventosAjustados = eventos.map(evento => {
                    if (evento.allDay && evento.start) {
                        // Si es allDay y hora es 00:00, ajustar a medio día
                        const fecha = new Date(evento.start);
                        if (fecha.getHours() === 0 && fecha.getMinutes() === 0) {
                            fecha.setHours(12, 0, 0, 0);
                            evento.start = fecha.toISOString();
                        }
                        if (evento.end) {
                            const fechaEnd = new Date(evento.end);
                            if (fechaEnd.getHours() === 0 && fechaEnd.getMinutes() === 0) {
                                fechaEnd.setHours(12, 0, 0, 0);
                                evento.end = fechaEnd.toISOString();
                            }
                        }
                    }
                    return evento;
                });

                console.log('📂 Eventos ajustados:', eventosAjustados);
                return eventosAjustados;

            } catch (error) {
                console.error('❌ Error cargando eventos:', error);
                return [];
            }
        }
        /**
         * Actualiza SOLO la fecha/hora de un evento en la DB
         * @param {string} eventId - ID del evento
         * @param {Date} newStart - Nueva fecha/hora de inicio
         * @param {Date} newEnd - Nueva fecha/hora de fin (opcional)
         * @param {boolean} newAllDay - Si es todo el día
         */
        async function actualizarFechaHoraEventoDB(eventId, newStart, newEnd = null, newAllDay = false, nuevoEstado =
            null) {
            try {
                const data = {
                    start: newStart.toISOString(),
                    end: newEnd ? newEnd.toISOString() : null,
                    all_day: newAllDay
                };

                // Si hay nuevo estado, incluirlo
                if (nuevoEstado) {
                    data.estado = nuevoEstado;
                }

                const response = await fetch(`/api/calendar/events/${eventId}/datetime`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) throw new Error('Error actualizando');

                console.log('✅ Fecha/hora y estado actualizados en DB');
                return true;

            } catch (error) {
                console.error('❌ Error:', error);
                return false;
            }
        }
        /**
         * Guardar evento en base de datos
         */
        async function guardarEventoEnDB(eventoData, esActualizacion = false) {


            const url = esActualizacion ? `/api/calendar/events/${eventoData.id}` : '/api/calendar/events';

            const method = esActualizacion ? 'PATCH' : 'POST'; // ← USA PATCH
            console.log(eventoData);
            // Preparar datos para la DB
            const dbEvent = {
                id: eventoData.id,
                title: eventoData.title || '',
                start: eventoData.start instanceof Date ? eventoData.start.toISOString() : eventoData.start,
                end: eventoData.end ?
                    (eventoData.end instanceof Date ? eventoData.end.toISOString() : eventoData.end) : null,
                all_day: eventoData.allDay === true,
                color: eventoData.color,
                factura: eventoData.extendedProps?.factura || '',
                ordencompra: eventoData.extendedProps?.ordencompra || '',
                codigo: eventoData.extendedProps?.codigo || '',
                descripcion: eventoData.extendedProps?.descripcion || '',
                cantidadFact: eventoData.extendedProps?.cantidadFact || 0,
                op_number: eventoData.extendedProps?.op_number || eventoData.title || '',
                cliente: eventoData.extendedProps?.cliente || '',
                cantidad_req: parseInt(eventoData.extendedProps?.cantidadReq) || 0,

                fecha_entrega: eventoData.extendedProps?.fechaEntrega || null,
                fecha_produccion: eventoData.extendedProps?.fechaProduccion || null,
                estado: eventoData.extendedProps?.estado || 'pendiente',

                //codigos: eventoData.extendedProps?.codigos || []
            };

            // Eliminar id si es nuevo
            if (!esActualizacion) {
                delete dbEvent.id;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(dbEvent)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Error en la respuesta del servidor');
            }

            const result = await response.json();

            return result.event;


        }
        /**
         * Eliminar evento de la base de datos
         */
        async function eliminarEventoDeDB(eventId) {
            try {
                const response = await fetch(`/api/calendar/events/${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                if (!response.ok) throw new Error('Error al eliminar');

                return true;

            } catch (error) {
                console.error('❌ Error eliminando de DB:', error);
                return false;
            }
        }

        function formatTime(date) {
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        function subirPdf(tipo) {
            if (!selectedEventId) {
                showToast('⚠️ Guarda el evento antes de subir PDFs', 'warning');
                return;
            }

            const input = tipo === 'factura' ?
                document.getElementById('pdf_factura') :
                document.getElementById('pdf_certificado');

            if (!input.files.length) {
                showToast('⚠️ Selecciona un archivo PDF', 'warning');
                return;
            }

            const file = input.files[0];
            
            // Validar que sea PDF
            if (file.type !== 'application/pdf') {
                showToast('❌ Solo se permiten archivos PDF', 'danger');
                return;
            }

            // Validar tamaño (10MB)
            if (file.size > 10 * 1024 * 1024) {
                showToast('❌ El archivo no debe superar 10MB', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('pdf', file);
            formData.append('tipo', tipo);

            // Deshabilitar botón mientras sube
            const btn = event.target;
            const btnTexto = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...';

            fetch(`/api/calendar/events/${currentEvent.id}/pdf`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showToast(`✅ PDF de ${tipo} guardado correctamente`, 'success');
                        input.value = '';
                        
                        // Validar que currentEvent existe
                        if (!currentEvent) {
                            console.error('currentEvent is undefined');
                            return;
                        }
                        
                        // Actualizar vista de PDFs
                        if (tipo === 'factura') {
                            currentEvent.setExtendedProp('factura_pdf', res.archivo);
                        } else {
                            currentEvent.setExtendedProp('certif_pdf', res.archivo);
                        }
                        
                        // Mostrar PDF recién subido
                        const opNumber = currentEvent.extendedProps.op_number;
                        mostrarPdf(
                            currentEvent.extendedProps.factura_pdf,
                            currentEvent.extendedProps.certif_pdf,
                            opNumber
                        );
                    } else {
                        showToast('❌ Error al guardar PDF: ' + (res.error || 'Error desconocido'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('❌ Error de conexión al subir PDF', 'danger');
                })
                .finally(() => {
                    // Restaurar botón
                    btn.disabled = false;
                    btn.innerHTML = btnTexto;
                });
        }

        function limpiarInputsPdf() {
            document.getElementById('pdf_factura').value = '';
            document.getElementById('pdf_certificado').value = '';
        }
        document.getElementById('eventModal').addEventListener('hidden.bs.modal', () => {
            selectedEventId = null;
            limpiarInputsPdf();
        });

        function mostrarPdf(facturaPdf, certPdf, op) {

    document.getElementById('pdf-viewer').classList.remove('d-none');

// FACTURA
if (facturaPdf) {
    const url = `/storage/facturas_certf_pdf/${op}/${facturaPdf}`;
    const link = document.getElementById('link-factura');

    link.href = url;
    link.textContent = facturaPdf;

    document.getElementById('ver-factura').classList.remove('d-none');
} else {
    document.getElementById('ver-factura').classList.add('d-none');
}

// CERTIFICADO
if (certPdf) {
    const url = `/storage/facturas_certf_pdf/${op}/${certPdf}`;
    const link = document.getElementById('link-certificado');

    link.href = url;
    link.textContent = certPdf;

    document.getElementById('ver-certificado').classList.remove('d-none');
} else {
    document.getElementById('ver-certificado').classList.add('d-none');
}
}
function limpiarVistaPdf() {
    document.getElementById('pdf-viewer').classList.add('d-none');
    document.getElementById('ver-factura').classList.add('d-none');
    document.getElementById('ver-certificado').classList.add('d-none');
}
        // ==================== INICIALIZACIÓN DEL CALENDARIO ====================
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            // Carga eventos primero

            calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'local', // 👈 CLAVE
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMultiMonth today'
                },

                // ✅ CONFIGURACIÓN 24 HORAS
                // Formato de hora en 24h
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false, // ✅ Esto cambia a formato 24h
                    meridiem: false
                },

                // Formato de etiquetas de tiempo
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false, // ✅ 24 horas
                    meridiem: false
                },

                // Vistas con configuración 24h
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 3,
                        dayMaxEvents: true,
                        // Eventos en vista mensual mostrarán hora en 24h si tienen
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        }
                    },
                    listMultiMonth: {
                        type: 'list',
                        duration: {
                            months: 3
                        },
                        buttonText: '3 Meses',
                        // Formato 24h en lista
                        listDayFormat: {
                            weekday: 'short',
                            day: 'numeric',
                            month: 'short'
                        }
                    },
                    timeGridWeek: {
                        allDaySlot: true,
                        allDayText: 'Todo el día',
                        slotDuration: '01:00:00',
                        slotLabelInterval: '01:00:00',
                        slotMinTime: '00:00:00', // ✅ Empieza a medianoche
                        slotMaxTime: '24:00:00', // ✅ Termina a medianoche
                        slotLabelFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false // ✅ 24 horas
                        }
                    },
                    timeGridDay: {
                        allDaySlot: true,
                        allDayText: 'Todo el día',
                        slotDuration: '00:30:00',
                        slotMinTime: '00:00:00', // ✅ 0:00
                        slotMaxTime: '24:00:00', // ✅ 24:00
                        slotLabelFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false // ✅ 24 horas
                        }
                    }
                },

                // Configuración general

                allDayText: 'Todo el día',
                height: 'auto',
                contentHeight: 500,
                editable: true,
                droppable: true,
                allDaySlot: true,
                events: '/api/calendar/events', // ← FullCalendar hace fetch automático


                // ✅ ACTUALIZAR eventDrop PARA HORAS 24H
                eventDataTransform: function(eventData) {
                    // Asignar color automáticamente según estado
                    const estado = eventData.extendedProps?.estado || 'pendiente';
                    eventData.color = getColorByStatus(estado);
                    return eventData;
                },
                eventDrop: function(info) {
                    try {
                        const esVistaHoraria = info.view.type.includes('timeGrid');
                        const esVistaMensual = info.view.type === 'dayGridMonth';

                        let newStart = info.event.start;
                        let newEnd = info.event.end;
                        let newAllDay = info.event.allDay;
                        let nuevoEstado = null;

                        if (esVistaMensual) {
                            // Vista mensual: allDay a medio día
                            const fechaAllDay = new Date(info.event.start);
                            fechaAllDay.setHours(12, 0, 0, 0);

                            newStart = fechaAllDay;
                            newEnd = null;
                            newAllDay = true;

                            info.event.setAllDay(true);
                            info.event.setStart(fechaAllDay);
                            info.event.setEnd(null);

                        } else if (esVistaHoraria) {
                            // ✅ VISTA HORARIA: end = start + 1 hora
                            const startDate = new Date(info.event.start);

                            // Crear end sumando 1 hora
                            const endDate = new Date(startDate);
                            endDate.setHours(endDate.getHours() + 1);

                            newStart = startDate;
                            newEnd = endDate;
                            newAllDay = false;

                            // Aplicar al evento
                            info.event.setAllDay(false);
                            info.event.setStart(startDate);
                            info.event.setEnd(endDate);

                            // Si era allDay antes, mantener la hora personalizada
                            if (info.event.allDay) {
                                info.event.setAllDay(false);
                            }

                            console.log(
                                `⏰ Drop horario: ${formatTime(startDate)} - ${formatTime(endDate)}`);

                            // Cambiar estado si era pendiente
                            const estadoActual = info.event.extendedProps?.estado || 'pendiente';
                            if (estadoActual === 'pendiente') {
                                nuevoEstado = 'en_progreso';
                                info.event.setExtendedProp('estado', nuevoEstado);
                                info.event.setProp('color', getColorByStatus(nuevoEstado));
                            }
                        }

                        // Guardar en DB
                        actualizarFechaHoraEventoDB(
                            info.event.id,
                            newStart,
                            newEnd,
                            newAllDay,
                            nuevoEstado
                        );

                        // Actualizar interfaz si está en edición
                        if (currentEvent && currentEvent.id === info.event.id) {
                            setTimeout(() => loadEventToForm(info.event), 100);
                        }

                        updateTodayEvents();

                    } catch (error) {
                        console.error('❌ Error:', error);
                        if (info.revert) info.revert();
                    }
                },
                // ==================== ACTUALIZAR dateClick ====================
                // En la configuración del calendario:
                // En la configuración del calendario, añade:
                dateClick: function(info) {
                    console.log('🖱️ Click en fecha:', info.dateStr);
                    selectedEventId = null;

                    // 🔒 ocultar PDFs
                    document.getElementById('pdf-section').classList.add('d-none');

                    limpiarInputsPdf();
                    // Guardar fecha para doble click
                    if (!this.ultimoClick) this.ultimoClick = {
                        time: 0,
                        date: null
                    };

                    const ahora = Date.now();
                    const esDobleClick = (ahora - this.ultimoClick.time < 300) &&
                        this.ultimoClick.date === info.dateStr;

                    this.ultimoClick = {
                        time: ahora,
                        date: info.dateStr
                    };

                    if (esDobleClick) {
                        // Doble click: ir a vista diaria automáticamente
                        console.log('🖱️🖱️ Doble click detectado');
                        irAVistaFecha(info.date, 'diaria');
                        return;
                    }

                    // Click simple: seleccionar fecha y abrir modal
                    const tipoVista = info.view.type === 'dayGridMonth' ? 'mensual' : 'semanal';
                    mostrarSeleccionFecha(info.date, tipoVista);
                    document.getElementById('eventDate').value = info.dateStr;
                    resetToNewEvent();
                    // Abrir modal
                    const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                    //modal.show();
                },

                eventClick: function(info) {
                    const event = info.event;

                    // 👇 ya lo haces en tu código
                    selectedEventId = event.id;

                    // 🔓 habilitar PDFs SOLO en edición
                    document.getElementById('pdf-section').classList.remove('d-none');

                    limpiarInputsPdf();

                    info.jsEvent.preventDefault();
                    loadEventToForm(info.event);
                 // MOSTRAR PDFS
                    const props = info.event.extendedProps;

                    mostrarPdf(
                        props.factura_pdf,
                        props.certif_pdf,
                        props.op_number
                    );
                },

                eventResize: function(info) {
                    try {
                        console.log('📏 EventResize');

                        const estadoActual = info.event.extendedProps?.estado || 'pendiente';
                        if (estadoActual === 'pendiente') {
                            info.event.setExtendedProp('estado', 'en_progreso');
                            info.event.setProp('color', getColorByStatus('en_progreso'));
                            // Guardar todo porque cambió estado
                            guardarEventoEnDB(info.event, true);
                        } else {
                            // Solo fecha/hora
                            actualizarFechaHoraEventoDB(
                                info.event.id,
                                info.event.start,
                                info.event.end,
                                info.event.allDay
                            );
                        }

                        if (currentEvent && currentEvent.id === info.event.id) {
                            loadEventToForm(info.event);
                        }

                        updateTodayEvents();
                    } catch (error) {
                        console.error('❌ Error en eventResize:', error);
                    }
                },


            });

            calendar.render();
            // polling
            setInterval(pollingEventos, 20000);
            updateTodayEvents();
            resetToNewEvent();
        });
    </script>
@endpush