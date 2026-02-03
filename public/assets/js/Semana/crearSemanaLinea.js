document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formSemanaPL');
    if (!form) return;

    // --- Inicialización de Quill.js ---
    const quillToolbar = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
    ];

    window.quill_editors_pl = {};

    // Define los campos y su relación con los IDs de los editores
    const quillFields = [
        { name: 'contenido', id: 'editor_contenido_pl' },
        { name: 'objetivo', id: 'editor_objetivo_pl' },
        { name: 'innovacion', id: 'editor_innovacion_pl' },
        { name: 'actividades', id: 'editor_actividades_pl' },
        { name: 'desarrollo', id: 'editor_desarrollo_pl' },
        { name: 'cierre', id: 'editor_cierre_pl' },
        { name: 'evaluacion_clase', id: 'editor_evaluacion_clase_pl' },
        { name: 'equipo_herramientas_recursos', id: 'editor_equipo_herramientas_recursos_pl' },
        { name: 'actividades_refuerzo', id: 'editor_actividades_refuerzo_pl' }
    ];

    // Inicializar todos los editores Quill
    quillFields.forEach(field => {
        const el = document.getElementById(field.id);
        if (el) {
            window.quill_editors_pl[field.name] = new Quill(`#${field.id}`, {
                theme: 'snow',
                modules: { toolbar: quillToolbar }
            });
            // Sincroniza el contenido con el input hidden en cada cambio
            window.quill_editors_pl[field.name].on('text-change', function () {
                const input = form.querySelector(`input[name="${field.name}"]`);
                if (input) input.value = window.quill_editors_pl[field.name].root.innerHTML;
            });
        }
    });

    // Función para pintar verde
    function pintarVerde(element) {
        const original = element.style.backgroundColor;
        element.style.backgroundColor = '#b6fcb6';
        setTimeout(() => { element.style.backgroundColor = original; }, 1200);
    }

    // Función de auto-guardado
    async function autoGuardarSemanaPL(inputEditado) {
        const idSemanaLinea = form.querySelector('input[name="id_semana_linea"]');
        if (!idSemanaLinea || !idSemanaLinea.value) return;

        // Sincronizar todos los editores antes de guardar
        quillFields.forEach(field => {
            const input = form.querySelector(`input[name="${field.name}"]`);
            if (input && window.quill_editors_pl[field.name]) {
                input.value = window.quill_editors_pl[field.name].root.innerHTML;
            }
        });

        const formData = new FormData(form);
        try {
            const resp = await fetch('/SysPlanificacion/app/SemanaLinea/updateSemanaLinea.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            const msgDiv = document.getElementById('msgSemana');
            if (data.success) {
                msgDiv.innerHTML = '<div class="alert alert-success py-2 mb-2" style="font-size:15px;">¡Guardado automáticamente!</div>';
                setTimeout(() => { msgDiv.innerHTML = ''; }, 1500);

                if (inputEditado) {
                    pintarVerde(inputEditado);
                    // Si es un input hidden de un Quill, pinta también el área del editor
                    quillFields.forEach(field => {
                        if (inputEditado.name === field.name && window.quill_editors_pl[field.name]) {
                            pintarVerde(window.quill_editors_pl[field.name].root);
                        }
                    });
                }
            }
        } catch (e) {
            // Manejo de error opcional
        }
    }

    // Auto-guardado al perder el foco en los editores Quill
    quillFields.forEach(field => {
        if (window.quill_editors_pl[field.name]) {
            window.quill_editors_pl[field.name].on('selection-change', function (range, oldRange, source) {
                if (oldRange && !range) { // blur
                    const input = form.querySelector(`input[name="${field.name}"]`);
                    if (input) autoGuardarSemanaPL(input);
                }
            });
        }
    });

    // Auto-guardado para inputs normales
    const hoy = new Date();
    let fechaSabadoAnterior = '';

    ['tiempo_actividades', 'tiempo_desarrollo', 'tiempo_cierre', 'fecha_sabado'].forEach(name => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (input) {
            if (name === 'fecha_sabado') {
                input.addEventListener('focus', () => {
                    fechaSabadoAnterior = input.value;
                });
                input.addEventListener('change', async () => {
                    if (input.value) {
                        const fechaSeleccionada = new Date(input.value);
                        if (fechaSeleccionada.getDay() !== 6) {
                            await Swal.fire({
                                icon: 'warning',
                                title: 'Fecha inválida',
                                text: 'Solo puede seleccionar días sábado.',
                                confirmButtonText: 'OK'
                            });
                            input.value = fechaSabadoAnterior;
                            input.focus();
                            return;
                        }
                    }
                });
            }
            input.addEventListener('blur', async () => {
                if (name === 'fecha_sabado' && input.value) {
                    const fechaSeleccionada = new Date(input.value);
                    if (
                        fechaSeleccionada.getFullYear() < hoy.getFullYear() ||
                        (fechaSeleccionada.getFullYear() === hoy.getFullYear() && fechaSeleccionada.getMonth() < hoy.getMonth())
                    ) {
                        await Swal.fire({
                            icon: 'warning',
                            title: 'Fecha inválida',
                            text: 'Solo puede seleccionar fechas del mes actual.',
                            confirmButtonText: 'OK'
                        });
                        input.value = fechaSabadoAnterior;
                        input.focus();
                        return;
                    }
                    if (fechaSeleccionada.getDay() !== 6) {
                        await Swal.fire({
                            icon: 'warning',
                            title: 'Fecha inválida',
                            text: 'Solo puede seleccionar días sábado.',
                            confirmButtonText: 'OK'
                        });
                        input.value = fechaSabadoAnterior;
                        input.focus();
                        return;
                    }
                }
                autoGuardarSemanaPL(input);
            });
        }
    });

    // Al enviar el formulario manualmente
    const msgDiv = document.getElementById('msgSemana');
    form.addEventListener('submit', async function (e) {
        // Sincronizar todos los editores Quill antes de enviar
        quillFields.forEach(field => {
            const input = form.querySelector(`input[name="${field.name}"]`);
            if (input && window.quill_editors_pl[field.name]) {
                input.value = window.quill_editors_pl[field.name].root.innerHTML;
            }
        });
        e.preventDefault();
        msgDiv.innerHTML = '';
        const formData = new FormData(form);
        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '¡Semana Semipresencial guardada correctamente!',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar',
                    confirmButtonText: 'OK'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'OK'
            });
        }
    });

    // Cargar datos si ya existe una semana S/EL
    const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
    if (idSemanaLineaInput && idSemanaLineaInput.value) {
        fetch(`/SysPlanificacion/app/SemanaLinea/getSemanaLineaById.php?id_semana_linea=${idSemanaLineaInput.value}`)
            .then(resp => resp.json())
            .then(data => {
                if (data.success && data.semana) {
                    // Llena los campos normales
                    ['fecha_sabado', 'tiempo_actividades', 'tiempo_desarrollo', 'tiempo_cierre'].forEach(name => {
                        const input = form.querySelector(`input[name="${name}"]`);
                        if (input && data.semana[name]) input.value = data.semana[name];
                    });
                    // Llena los editores Quill
                    quillFields.forEach(field => {
                        if (window.quill_editors_pl[field.name] && data.semana[field.name]) {
                            window.quill_editors_pl[field.name].root.innerHTML = data.semana[field.name];
                            const input = form.querySelector(`input[name="${field.name}"]`);
                            if (input) input.value = data.semana[field.name];
                        }
                    });

                    const collapseEl = document.getElementById('collapsePlanificacion');
                    if (collapseEl) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        bsCollapse.show();
                    }
                }
            });
    }

    // --- Botón Visualizar PDF Línea ---
    const btnVisualizarPDFLinea = document.getElementById('btnVisualizarPDFLinea');
    if (btnVisualizarPDFLinea) {
        btnVisualizarPDFLinea.addEventListener('click', function () {
            const idSemanaLinea = document.getElementById('id_semana_linea').value;
            if (!idSemanaLinea) {
                alert('No hay semana seleccionada.');
                return;
            }
            const iframe = document.getElementById('iframePDF');
            if (iframe) {
                iframe.src = `/SysPlanificacion/app/gestionPDFS/visualizarSemanLineaPDF.php?id_semana_linea=${idSemanaLinea}`;
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPDF'));
                modal.show();
            }
        });
    }

    // --- Botón Guardar PDF ---
    const btnGuardarPDF = document.getElementById('btnGuardarPDF');
    if (btnGuardarPDF) {
        btnGuardarPDF.addEventListener('click', async function () {
            const nombreUnidad = window.nombreUnidad || '';
            const idUnidad = document.querySelector('input[name="unidad_id"]')?.value || '';
            const fechaCreacion = new Date().toLocaleString('es-EC');

            const result = await Swal.fire({
                title: '¿Deseas guardar el PDF?',
                html: `
                    <div style="text-align:left;">
                        <b>Nombre del Archivo:</b> ${nombreUnidad}<br>
                        <b>ID de la Unidad:</b> ${idUnidad}<br>
                        <b>Fecha de creación:</b> ${fechaCreacion}
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                const iframe = document.getElementById('iframePDF');
                if (!iframe || !iframe.src) {
                    Swal.fire('Error', 'No se encontró el PDF para guardar.', 'error');
                    return;
                }

                try {
                    const pdfResp = await fetch(iframe.src);
                    if (!pdfResp.ok) throw new Error('No se pudo obtener el PDF');
                    const pdfBlob = await pdfResp.blob();

                    const formData = new FormData();
                    formData.append('unidad_id', idUnidad);
                    formData.append('nombre_archivo', nombreUnidad + '.pdf');
                    formData.append('archivo_pdf', pdfBlob, nombreUnidad + '.pdf');

                    const resp = await fetch('/SysPlanificacion/app/gestionPDFS/guardarPlanificacion.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    if (data.success) {
                        Swal.fire('¡Guardado!', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo guardar el PDF', 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message || 'No se pudo guardar el PDF', 'error');
                }
            }
        });
    }
});

