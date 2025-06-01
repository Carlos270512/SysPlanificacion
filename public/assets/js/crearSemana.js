document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica de fechas y encabezados (original) ---
    const semanaInicio = document.querySelector('input[name="semana_inicio"]');
    const semanaFin = document.querySelector('input[name="semana_fin"]');
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    if (semanaInicio) {
        const picker = new Pikaday({
            field: semanaInicio,
            format: 'YYYY-MM-DD',
            toString(date) {
                const day = ("0" + date.getDate()).slice(-2);
                const month = ("0" + (date.getMonth() + 1)).slice(-2);
                return date.getFullYear() + '-' + month + '-' + day;
            },
            disableDayFn: function (date) {
                return date.getDay() !== 1;
            }
        });

        semanaInicio.addEventListener('change', function () {
            if (!semanaInicio.value) return;
            const [anio, mes, dia] = semanaInicio.value.split('-').map(Number);
            const fecha = new Date(anio, mes - 1, dia);

            const viernes = new Date(fecha);
            viernes.setDate(fecha.getDate() + 4);
            semanaFin.value = viernes.toISOString().slice(0, 10);

            dias.forEach((diaNombre, idx) => {
                const th = document.getElementById('th_' + diaNombre);
                if (th) {
                    const d = new Date(fecha);
                    d.setDate(fecha.getDate() + idx);
                    const diaNum = ("0" + d.getDate()).slice(-2);
                    const mesNum = ("0" + (d.getMonth() + 1)).slice(-2);
                    const anio = d.getFullYear();
                    th.textContent = th.dataset.nombre + ' - ' + diaNum + '/' + mesNum + '/' + anio;
                }
                let input = document.querySelector('input[name="fecha_' + diaNombre + '"]');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'fecha_' + diaNombre;
                    semanaInicio.form.appendChild(input);
                }
                const d = new Date(fecha);
                d.setDate(fecha.getDate() + idx);
                input.value = d.toISOString().slice(0, 10);
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
                    const btnGuardar = document.getElementById('btnGuardarSemana');
                    const btnPDF = document.getElementById('btnVisualizarPDF');
                    if (btnGuardar) btnGuardar.disabled = true;
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
            // Solo enviamos los IDs al backend
            const idUnidad = form.querySelector('input[name="unidad_id"]')?.value || '';
            if (!idSemanaGuardada) {
                alert('Primero debe guardar la semana.');
                return;
            }
            const pdfWindow = window.open('', '_blank');
            fetch('/SysPlanificacion/app/gestionPDFS/visualizarPDF.php', {
                method: 'POST',
                body: new URLSearchParams({ semana_id: idSemanaGuardada, unidad_id: idUnidad })
            })
                .then(response => response.blob())
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    pdfWindow.location.href = url;
                });
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
            if (campo === 'semana_inicio' || campo === 'semana_fin' || campo === 'unidad_id') return;
            // Ajuste para campos de tiempo previas
            if (campo === 'tiempo_previas') campo = 'tiempo_actividades_previas';
            // Ajuste para campos de fecha de entrega
            if (campo.startsWith('entrega_')) {
                campo = 'fecha_entrega_' + campo.split('_')[1];
            }
            // Ajuste para campos de fecha de los días (si usas fecha_lunes, etc.)
            if (campo.startsWith('fecha_') && !['fecha_semana', 'fecha_entrega_lunes', 'fecha_entrega_martes', 'fecha_entrega_miercoles', 'fecha_entrega_jueves', 'fecha_entrega_viernes'].includes(campo)) {
                // Se guarda como fecha_lunes, fecha_martes, etc.
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