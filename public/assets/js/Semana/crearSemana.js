document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica de fechas y encabezados (sin Pikaday) ---
    const semanaInicio = document.querySelector('input[name="semana_inicio"]');
    const semanaFin = document.querySelector('input[name="semana_fin"]');
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    // Guardar el valor anterior
    let semanaInicioPrev = semanaInicio ? semanaInicio.value : '';
    let semanaFinPrev = semanaFin ? semanaFin.value : '';

    function validarMesActual(input, prevValue) {
        if (!input.value) return true;
        const fecha = new Date(input.value);
        const hoy = new Date();
        const mesActual = hoy.getMonth();
        const anioActual = hoy.getFullYear();
        if (fecha.getFullYear() < anioActual || (fecha.getFullYear() === anioActual && fecha.getMonth() < mesActual)) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha inválida',
                text: 'No puedes seleccionar una fecha de un mes anterior al actual.',
            });
            input.value = prevValue;
            input.focus();
            return false;
        }
        return true;
    }

    if (semanaInicio) {
        semanaInicio.addEventListener('focus', function () {
            semanaInicioPrev = semanaInicio.value;
        });
        semanaInicio.addEventListener('change', function () {
            validarMesActual(semanaInicio, semanaInicioPrev);
        });
    }
    if (semanaFin) {
        semanaFin.addEventListener('focus', function () {
            semanaFinPrev = semanaFin.value;
        });
        semanaFin.addEventListener('change', function () {
            validarMesActual(semanaFin, semanaFinPrev);
        });
    }

    // --- Auto-save para semana_inicio y semana_fin ---
    if (semanaInicio) {
        semanaInicio.addEventListener('blur', async function () {
            const semanaId = window.idSemanaGuardada || document.getElementById('id_semana')?.value;
            if (!semanaId) return;
            await fetch('/SysPlanificacion/app/Semana/updateSemana.php', {
                method: 'POST',
                body: new URLSearchParams({
                    semana_id: semanaId,
                    campo: 'fecha_semana',
                    valor: semanaInicio.value
                })
            });
        });
    }
    if (semanaFin) {
        semanaFin.addEventListener('blur', async function () {
            const semanaId = window.idSemanaGuardada || document.getElementById('id_semana')?.value;
            if (!semanaId) return;
            await fetch('/SysPlanificacion/app/Semana/updateSemana.php', {
                method: 'POST',
                body: new URLSearchParams({
                    semana_id: semanaId,
                    campo: 'semana_fin',
                    valor: semanaFin.value
                })
            });
        });
    }
      // --- Inicialización de Quill.js para todos los campos de texto enriquecido ---
    const quillToolbar = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
    ];

    // Inicialización de Quill para todos los campos
    window.quill_editors = {};

    // Campos principales
    window.quill_editors['actividades_previas'] = new Quill('#editor_actividades_previas', {
        theme: 'snow',
        modules: { toolbar: quillToolbar }
    });
    window.quill_editors['contenido'] = new Quill('#editor_contenido', {
        theme: 'snow',
        modules: { toolbar: quillToolbar }
    });

    // Sincronización automática con input hidden
    window.quill_editors['actividades_previas'].on('text-change', function () {
        const input = document.querySelector('input[name="actividades_previas"]');
        if (input) input.value = window.quill_editors['actividades_previas'].root.innerHTML;
    });
    window.quill_editors['contenido'].on('text-change', function () {
        const input = document.querySelector('input[name="contenido"]');
        if (input) input.value = window.quill_editors['contenido'].root.innerHTML;
    });

    // Campos de la tabla (por día y tipo)
    const campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
    dias.forEach(dia => {
        campos.forEach(campo => {
            const id = `#editor_${campo}_${dia}`;
            const el = document.querySelector(id);
            if (el) {
                window.quill_editors[`${campo}_${dia}`] = new Quill(id, {
                    theme: 'snow',
                    modules: { toolbar: quillToolbar }
                });
                // Sincroniza con input hidden
                window.quill_editors[`${campo}_${dia}`].on('text-change', function () {
                    const input = document.querySelector(`input[name="${campo}_${dia}"]`);
                    if (input) input.value = window.quill_editors[`${campo}_${dia}`].root.innerHTML;
                });
            }
        });
    });

    // --- Manejo del formulario por AJAX (original) ---
    const form = document.getElementById('formSemana');
    const msgDiv = document.getElementById('msgSemana');
    let idSemanaGuardada = null; // Aquí guardamos el id de la semana

if (form) {
    form.addEventListener('submit', async function (e) {
        // Validar fechas antes de enviar
        let valido = true;
        if (semanaInicio && !validarMesActual(semanaInicio, '')) valido = false;
        if (semanaFin && !validarMesActual(semanaFin, '')) valido = false;

        // Validar rango de 7 días entre semanaInicio y semanaFin
        if (semanaInicio.value && semanaFin.value) {
            const inicio = new Date(semanaInicio.value);
            const fin = new Date(semanaFin.value);
            const diffMs = fin - inicio;
            const diffDias = diffMs / (1000 * 60 * 60 * 24);
            if (diffDias < 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fechas inválidas',
                    text: 'La fecha de fin no puede ser anterior a la fecha de inicio.',
                });
                valido = false;
            }
            if (diffDias > 7) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rango de fechas inválido',
                    text: 'Solo puedes seleccionar hasta 7 días entre la fecha de inicio y la fecha de fin.',
                });
                valido = false;
            }
        }

        if (!valido) {
            e.preventDefault();
            return;
        }

        // Copia el contenido de cada Quill al input hidden correspondiente
        Object.keys(window.quill_editors).forEach(key => {
            const input = document.querySelector(`input[name="${key}"]`);
            if (input) {
                input.value = window.quill_editors[key].root.innerHTML;
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
                msgDiv.innerHTML = '<div class="alert alert-success">¡Semana guardada correctamente!</div>';
                //const btnGuardar = document.getElementById('btnGuardarSemana');
                const btnPDF = document.getElementById('btnVisualizarPDF');
                //if (btnGuardar) btnGuardar.disabled = true;
                if (btnPDF) btnPDF.disabled = false;
                idSemanaGuardada = data.semana_id; // <-- Guardamos el id correcto del backend
                window.idSemanaGuardada = idSemanaGuardada; // Para acceso global
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al guardar') + '</div>';
            }
        } catch (err) {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        }
    });
}

    // --- Evento para el botón Visualizar PDF ---
    const btnPDF = document.getElementById('btnVisualizarPDF');
if (btnPDF) {
    btnPDF.addEventListener('click', function () {
        const idUnidad = form.querySelector('input[name="unidad_id"]')?.value || '';
        const semanaId = window.idSemanaGuardada;
        if (!semanaId) {
            alert('Primero debe guardar la semana.');
            return;
        }
        // Deshabilita el botón guardar PDF mientras carga
        const btnGuardarPDF = document.getElementById('btnGuardarPDF');
        if (btnGuardarPDF) btnGuardarPDF.disabled = true;

        fetch('/SysPlanificacion/app/gestionPDFS/visualizarPDF.php', {
            method: 'POST',
            body: new URLSearchParams({ semana_id: semanaId, unidad_id: idUnidad })
        })
            .then(response => response.blob())
            .then(blob => {
                const url = URL.createObjectURL(blob);
                // Carga el PDF en el iframe del modal
                const iframe = document.getElementById('iframePDF');
                if (iframe) iframe.src = url;
                // Guarda el blob para el botón "Guardar PDF"
                window._ultimoPDFBlob = blob;
                // Habilita el botón guardar PDF
                if (btnGuardarPDF) btnGuardarPDF.disabled = false;
                // Muestra el modal
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPDF'));
                modal.show();
            });
    });
}
if (semanaFin && semanaInicio) {
    let semanaFinPrev = semanaFin.value;
    semanaFin.addEventListener('focus', function () {
        semanaFinPrev = semanaFin.value;
    });
    semanaFin.addEventListener('change', function () {
        if (!semanaInicio.value || !semanaFin.value) return;
        const inicio = new Date(semanaInicio.value);
        const fin = new Date(semanaFin.value);
        const diffMs = fin - inicio;
        const diffDias = diffMs / (1000 * 60 * 60 * 24);
        if (diffDias < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas inválidas',
                text: 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            });
            semanaFin.value = semanaFinPrev;
            semanaFin.focus();
            return;
        }
        if (diffDias > 7) {
            Swal.fire({
                icon: 'warning',
                title: 'Rango de fechas inválido',
                text: 'Solo puedes seleccionar hasta 7 días entre la fecha de inicio y la fecha de fin.',
            });
            semanaFin.value = semanaFinPrev;
            semanaFin.focus();
        }
    });
}

//botn para procesar el pdf 

const btnGuardarPDF = document.getElementById('btnGuardarPDF');
if (btnGuardarPDF) {
    btnGuardarPDF.addEventListener('click', async function () {
        // Obtén los datos necesarios
        const nombreUnidad = window.nombreUnidad || 'planificacion.pdf';
        const idUnidad = document.querySelector('input[name="unidad_id"]')?.value || '';
        const fechaCreacion = new Date().toLocaleString();
        const blob = window._ultimoPDFBlob;

        if (!blob) {
            Swal.fire('Error', 'No se ha generado el PDF.', 'error');
            return;
        }

        // Muestra el SweetAlert de confirmación
        const result = await Swal.fire({
            title: '¿Guardar este PDF?',
            html: `
                <div style="text-align:left">
                    <b>Nombre de archivo:</b> ${nombreUnidad}.pdf<br>
                    <b>ID Unidad:</b> ${idUnidad}<br>
                    <b>Fecha de creación:</b> ${fechaCreacion}
                </div>
                <hr>
                ¿Deseas guardar este PDF en la base de datos?
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            if (result.isConfirmed) {
    try {
        // Mostrar loader
        Swal.fire({
            title: 'Guardando...',
            text: 'El PDF se está guardando.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Prepara el FormData
        const formData = new FormData();
        formData.append('unidad_id', idUnidad);
        formData.append('nombre_archivo', nombreUnidad + '.pdf');
        formData.append('archivo_pdf', blob, nombreUnidad + '.pdf');
        // Si tienes usuario, puedes agregarlo aquí:
        // formData.append('usuario_creacion', usuario);

        // Envía al backend
        const response = await fetch('/SysPlanificacion/app/gestionPDFS/guardarPlanificacion.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            Swal.fire('¡Éxito!', data.message || 'PDF guardado correctamente.', 'success');
        } else {
            Swal.fire('Error', data.message || 'No se pudo guardar el PDF.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Ocurrió un error al guardar el PDF.', 'error');
    }
}
            
        }
    });
}

    // --- AUTO-SAVE Y PINTADO EN VERDE ---

    // Función para pintar de verde temporalmente
    function pintarVerde(element) {
        const original = element.style.backgroundColor;
        element.style.backgroundColor = '#b6fcb6';
        setTimeout(() => { element.style.backgroundColor = original; }, 1200);
    }

    // Auto-save para inputs de texto y fecha
    document.querySelectorAll('input[type="text"], input[type="date"]').forEach(input => {
        input.addEventListener('blur', async function () {
            const semanaId = window.idSemanaGuardada || idSemanaGuardada;
            if (!semanaId) return;
            let campo = input.name;
            const valor = input.value;
            // No guardar campos que no existen en la tabla
            if (campo === 'unidad_id') return;
            if (campo === 'semana_inicio') campo = 'fecha_semana';
            // Ajuste para campos de tiempo previas
            if (campo === 'tiempo_previas') campo = 'tiempo_actividades_previas';
            // Ajuste para campos de fecha de entrega
            if (campo.startsWith('entrega_')) {
                campo = 'fecha_entrega_' + campo.split('_')[1];
            }
            // Ajuste para campos de fecha de los días (si usas fecha_lunes, etc.)
            if (campo.startsWith('fecha_') && !['fecha_semana', 'fecha_entrega_lunes', 'fecha_entrega_martes', 'fecha_entrega_miercoles', 'fecha_entrega_jueves', 'fecha_entrega_viernes'].includes(campo)) {
                campo = campo;
            }
            const resp = await fetch('/SysPlanificacion/app/Semana/updateSemana.php', {
                method: 'POST',
                body: new URLSearchParams({
                    semana_id: semanaId,
                    campo: campo,
                    valor: valor
                })
            });
            const data = await resp.json();
            if (data.success) pintarVerde(input);
        });
    });
document.querySelectorAll('input[type="date"][name^="entrega_"]').forEach(function(input) {
    let prevValue = input.value;
    input.addEventListener('focus', function() {
        prevValue = input.value;
    });
    input.addEventListener('change', function() {
        const fecha = new Date(input.value);
        const hoy = new Date();
        const mesActual = hoy.getMonth();
        const anioActual = hoy.getFullYear();
        if (input.value && (fecha.getFullYear() < anioActual || (fecha.getFullYear() === anioActual && fecha.getMonth() < mesActual))) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha inválida',
                text: 'Solo puedes seleccionar fechas del mes actual o posteriores.',
            });
            input.value = prevValue;
            input.focus();
        }
    });
});
    // Auto-save para campos Quill
    Object.keys(window.quill_editors).forEach(key => {
        const quill = window.quill_editors[key];
        quill.on('selection-change', async function (range, oldRange, source) {
            if (oldRange && !range) { // blur
                const semanaId = window.idSemanaGuardada || idSemanaGuardada;
                if (!semanaId) return;
                const valor = quill.root.innerHTML;
                // Sincroniza el input hidden
                const input = document.querySelector(`input[name="${key}"]`);
                if (input) input.value = valor;
                let campoDB = key;
                if (campoDB === 'tiempo_previas') campoDB = 'tiempo_actividades_previas';
                const resp = await fetch('/SysPlanificacion/app/Semana/updateSemana.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        semana_id: semanaId,
                        campo: campoDB,
                        valor: valor
                    })
                });
                const data = await resp.json();
                if (data.success) pintarVerde(quill.root);
            }
        });
    });
});