document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formSemanaPL');
    if (!form) return;

    // Define los campos y su relación con los IDs de los editores
    const quillFields = [
        { name: 'contenido', id: 'editor_contenido_pl' },
        { name: 'objetivo', id: 'editor_objetivo_pl' },
        { name: 'actividades', id: 'editor_actividades_pl' },
        { name: 'desarrollo', id: 'editor_desarrollo_pl' },
        { name: 'cierre', id: 'editor_cierre_pl' },
        { name: 'evaluacion_clase', id: 'editor_evaluacion_clase_pl' },
        { name: 'equipo_herramientas_recursos', id: 'editor_equipo_herramientas_recursos_pl' },
        { name: 'actividades_refuerzo', id: 'editor_actividades_refuerzo_pl' }
    ];

    const quillToolbar = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
    ];

    window.quill_editors_pl = {};

    // Inicializa Quill y sincroniza con los inputs hidden
    quillFields.forEach(field => {
        const el = document.getElementById(field.id);
        if (el) {
            window.quill_editors_pl[field.name] = new Quill(`#${field.id}`, {
                theme: 'snow',
                modules: { toolbar: quillToolbar }
            });
            window.quill_editors_pl[field.name].on('text-change', function () {
                const input = form.querySelector(`input[name="${field.name}"]`);
                if (input) input.value = window.quill_editors_pl[field.name].root.innerHTML;
                autoGuardarSemanaPL(input);
            });
        }
    });

    // Auto-guardado para inputs normales
    [
        'tiempo_actividades',
        'tiempo_desarrollo',
        'tiempo_cierre',
        'fecha_sabado'
    ].forEach(name => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (input) {
            input.addEventListener('input', () => autoGuardarSemanaPL(input));
        }
    });
    function pintarVerde(element) {
        const original = element.style.backgroundColor;
        element.style.backgroundColor = '#b6fcb6';
        setTimeout(() => { element.style.backgroundColor = original; }, 1200);
    }
    // Función de auto-guardado
    async function autoGuardarSemanaPL(inputEditado) {
        const idSemanaLinea = form.querySelector('input[name="id_semana_linea"]');
        if (!idSemanaLinea || !idSemanaLinea.value) return;

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
            // Mensaje verde
            const msgDiv = document.getElementById('msgSemana');
            if (data.success) {
                msgDiv.innerHTML = '<div class="alert alert-success py-2 mb-2" style="font-size:15px;">¡Guardado automáticamente!</div>';
                setTimeout(() => { msgDiv.innerHTML = ''; }, 1500);
                // Pinta de verde el campo editado
                if (inputEditado) pintarVerde(inputEditado);
            }
        } catch (e) {
            // Manejo de error opcional
        }
    }

    // Al enviar el formulario manualmente
    const msgDiv = document.getElementById('msgSemana');
    form.addEventListener('submit', async function (e) {
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
                msgDiv.innerHTML = '<div class="alert alert-success">¡Semana PL guardada correctamente!</div>';
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al guardar') + '</div>';
            }
        } catch (err) {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        }
    });
    // Cargar datos si ya existe una semana PL/EL
    const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
    if (idSemanaLineaInput && idSemanaLineaInput.value) {
        fetch(`/SysPlanificacion/app/SemanaLinea/getSemanaLineaById.php?id_semana_linea=${idSemanaLineaInput.value}`)
            .then(resp => resp.json())
            .then(data => {
                if (data.success && data.semana) {
                    // Llena los campos normales
                    [
                        'fecha_sabado',
                        'tiempo_actividades',
                        'tiempo_desarrollo',
                        'tiempo_cierre'
                    ].forEach(name => {
                        const input = form.querySelector(`input[name="${name}"]`);
                        if (input && data.semana[name]) input.value = data.semana[name];
                    });
                    // Llena los editores Quill
                    quillFields.forEach(field => {
                        if (window.quill_editors_pl[field.name] && data.semana[field.name]) {
                            window.quill_editors_pl[field.name].root.innerHTML = data.semana[field.name];
                            // También actualiza el input hidden
                            const input = form.querySelector(`input[name="${field.name}"]`);
                            if (input) input.value = data.semana[field.name];
                        }
                    });
                }
            });
    }
});