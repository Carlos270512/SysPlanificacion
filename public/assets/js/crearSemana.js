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
            disableDayFn: function(date) {
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

    const quill_editors = {};

    // Campos principales
    quill_editors['actividades_previas'] = new Quill('#editor_actividades_previas', { 
        theme: 'snow',
        modules: { toolbar: quillToolbar }
    });
    quill_editors['contenido'] = new Quill('#editor_contenido', { 
        theme: 'snow',
        modules: { toolbar: quillToolbar }
    });

    // Campos de la tabla (por día y tipo)
    const campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
    dias.forEach(dia => {
        campos.forEach(campo => {
            const id = `#editor_${campo}_${dia}`;
            const el = document.querySelector(id);
            if (el) {
                quill_editors[`${campo}_${dia}`] = new Quill(id, { 
                    theme: 'snow',
                    modules: { toolbar: quillToolbar }
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
            Object.keys(quill_editors).forEach(key => {
                const input = document.querySelector(`input[name="${key}"]`);
                if (input) {
                    input.value = quill_editors[key].root.innerHTML;
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
});