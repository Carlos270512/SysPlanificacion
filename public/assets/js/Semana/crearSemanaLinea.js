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
            });
        }
    });

    // Al enviar el formulario, copia el contenido de Quill a los inputs hidden
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
});