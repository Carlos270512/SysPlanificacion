class CrearUnidadForm {
    constructor(formId, msgDivId = 'msgUnidad') {
        this.form = document.getElementById(formId);
        this.msgDivId = msgDivId;
        this.btnNuevaSemana = document.getElementById('btnNuevaSemana');
        this.unidadId = null;
        this.updateUrl = '/SysPlanificacion/app/Unidad/updateUnidad.php';
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
            this.initAutoSave();
        }
        if (this.btnNuevaSemana) {
            this.btnNuevaSemana.addEventListener('click', this.handleNuevaSemana.bind(this));
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this.form);

        let msgDiv = document.getElementById(this.msgDivId);
        if (!msgDiv) {
            msgDiv = document.createElement('div');
            msgDiv.id = this.msgDivId;
            this.form.parentNode.insertBefore(msgDiv, this.form);
        }
        msgDiv.innerHTML = '';

        try {
            const resp = await fetch(this.form.action, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.success) {
                msgDiv.innerHTML = '<div class="alert alert-success">Unidad guardada correctamente.</div>';
                // Guarda el id_unidad en el campo oculto
                if (data.unidad_id) {
                    this.unidadId = data.unidad_id;
                    document.getElementById('id_unidad').value = data.unidad_id;
                }
                // Habilita el botón y guarda el id de la unidad
                if (this.btnNuevaSemana) {
                    this.btnNuevaSemana.disabled = false;
                }
            } else {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al guardar') + '</div>';
            }
        } catch (err) {
            msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        }
    }

    handleNuevaSemana() {
        if (this.unidadId) {
            window.location.href = `crearSemanaPlanificacion.php?id_unidad=${encodeURIComponent(this.unidadId)}`;
        }
    }

    initAutoSave() {
        // Selecciona todos los campos editables
        const fields = this.form.querySelectorAll('input[name], textarea[name]');
        fields.forEach(field => {
            // No autosave para el campo oculto ni para asignatura_codigo ni id_unidad
            if (['asignatura_codigo', 'id_unidad'].includes(field.name)) return;
            field.addEventListener('blur', async () => {
                // Solo si ya existe id_unidad (ya fue creada la unidad)
                const idUnidad = document.getElementById('id_unidad').value;
                if (!idUnidad) return;
                // Prepara los datos para actualizar todos los campos
                const formData = new FormData();
                formData.append('id_unidad', idUnidad);
                const allFields = this.form.querySelectorAll('input[name], textarea[name]');
                allFields.forEach(f => {
                    formData.append(f.name, f.value);
                });
                try {
                    const resp = await fetch(this.updateUrl, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    // Feedback visual: si es Quill, pinta el div, si no, el input
                    const quillDiv = document.getElementById('editor_' + field.name);
                    if (data.success) {
                        if (quillDiv) {
                            quillDiv.classList.add('quill-valid');
                            setTimeout(() => quillDiv.classList.remove('quill-valid'), 1500);
                        } else {
                            field.classList.add('is-valid');
                            setTimeout(() => field.classList.remove('is-valid'), 1500);
                        }
                    } else {
                        if (quillDiv) {
                            quillDiv.classList.add('quill-invalid');
                            setTimeout(() => quillDiv.classList.remove('quill-invalid'), 1500);
                        } else {
                            field.classList.add('is-invalid');
                            setTimeout(() => field.classList.remove('is-invalid'), 1500);
                        }
                    }
                } catch (err) {
                    const quillDiv = document.getElementById('editor_' + field.name);
                    if (quillDiv) {
                        quillDiv.classList.add('quill-invalid');
                        setTimeout(() => quillDiv.classList.remove('quill-invalid'), 1500);
                    } else {
                        field.classList.add('is-invalid');
                        setTimeout(() => field.classList.remove('is-invalid'), 1500);
                    }
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new CrearUnidadForm('formUnidad');
});