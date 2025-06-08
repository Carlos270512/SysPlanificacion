document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const idUnidad = params.get('id_unidad');
    const tablaSemanas = document.getElementById('tablaSemanasUnidad');
    const btnNuevaSemana = document.getElementById('btnNuevaSemana');
    const formSemana = document.getElementById('formSemana');

    // 1. Cargar semanas de la unidad
    async function cargarSemanas() {
        tablaSemanas.innerHTML = '<div class="text-center">Cargando...</div>';
        try {
            const resp = await fetch(`/SysPlanificacion/app/Semana/listarSemanas.php?id_unidad=${idUnidad}`);
            const data = await resp.json();
            if (data.success && data.semanas.length > 0) {
                let html = `<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
                data.semanas.forEach((semana, idx) => {
                    html += `<tr>
                        <td>${idx + 1}</td>
                        <td>${semana.fecha_semana || ''}</td>
                        <td>${semana.semana_fin || ''}</td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm btn-editar-semana" data-id="${semana.id_semana}">Editar</button>
                            <button class="btn btn-outline-danger btn-sm btn-eliminar-semana" data-id="${semana.id_semana}">Eliminar</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                tablaSemanas.innerHTML = html;
            } else {
                tablaSemanas.innerHTML = '<div class="alert alert-warning mb-0">No hay semanas registradas para esta unidad.</div>';
            }
        } catch (e) {
            tablaSemanas.innerHTML = '<div class="alert alert-danger mb-0">Error al cargar semanas.</div>';
        }
    }

    // 2. Botón "Nueva semana"
    btnNuevaSemana.addEventListener('click', function () {
        if (!formSemana) return;
        formSemana.reset();
        // Limpiar editores Quill si existen
        if (window.quill_editors) {
            Object.values(window.quill_editors).forEach(editor => editor.setContents([]));
        }
        // Limpiar campos hidden y fechas
        document.getElementById('semana_inicio').value = '';
        document.getElementById('semana_fin').value = '';
        // Limpiar idSemanaGuardada para indicar que es nueva
        window.idSemanaGuardada = null;
        // Limpiar campo oculto id_semana
        document.getElementById('id_semana').value = '';
        // Habilitar botón guardar
        document.getElementById('btnGuardarSemana').disabled = false;
        document.getElementById('btnGuardarSemana').style.display = '';
        document.getElementById('btnVisualizarPDF').disabled = true;
    });

    // 3. Botón "Editar"
    tablaSemanas.addEventListener('click', async function (e) {
        if (e.target.classList.contains('btn-editar-semana')) {
            const idSemana = e.target.getAttribute('data-id');
            try {
                const resp = await fetch(`/SysPlanificacion/app/Semana/getSemanaById.php?id_semana=${idSemana}`);
                const data = await resp.json();
                if (data.success && data.semana) {
                    // Llenar el formulario con los datos de la semana seleccionada
                    const semana = data.semana;
                    document.getElementById('semana_inicio').value = semana.fecha_semana || '';
                    document.getElementById('semana_fin').value = semana.semana_fin || '';
                    document.querySelector('input[name="tiempo_previas"]').value = semana.tiempo_actividades_previas || '';
                    // Llenar campos Quill y otros campos según tu lógica
                    if (window.quill_editors) {
                        if (semana.actividades_previas && window.quill_editors['editor_actividades_previas']) {
                            window.quill_editors['editor_actividades_previas'].setContents(window.quill_editors['editor_actividades_previas'].clipboard.convert(semana.actividades_previas));
                        }
                        if (semana.contenido && window.quill_editors['editor_contenido']) {
                            window.quill_editors['editor_contenido'].setContents(window.quill_editors['editor_contenido'].clipboard.convert(semana.contenido));
                        }
                        // Repite para los campos de cada día si es necesario
                    }
                    // Llenar campos de días (objetivo, apertura, etc.) si tu backend los devuelve
                    // ...
                    // Guardar el id de la semana para autoguardado
                    window.idSemanaGuardada = semana.id_semana;
                    // Poner el id en el campo oculto
                    document.getElementById('id_semana').value = semana.id_semana;
                    // Habilitar botones
                    document.getElementById('btnGuardarSemana').disabled = false;
                    document.getElementById('btnGuardarSemana').style.display = '';
                    document.getElementById('btnVisualizarPDF').disabled = false;
                }
            } catch (e) {
                alert('Error al cargar la semana seleccionada');
            }
        }
        // 4. Botón "Eliminar" (opcional)
        if (e.target.classList.contains('btn-eliminar-semana')) {
            const idSemana = e.target.getAttribute('data-id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la semana seleccionada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const resp = await fetch('/SysPlanificacion/app/Semana/eliminarSemana.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id_semana=' + encodeURIComponent(idSemana)
                        });
                        const data = await resp.json();
                        if (data.success) {
                            Swal.fire(
                                '¡Eliminado!',
                                'La semana ha sido eliminada correctamente.',
                                'success'
                            );
                            cargarSemanas();
                        } else {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar la semana.',
                                'error'
                            );
                        }
                    } catch (e) {
                        Swal.fire(
                            'Error',
                            'Error al eliminar la semana.',
                            'error'
                        );
                    }
                }
            });
        }
    });

    // Inicializar tabla al cargar
    if (idUnidad) cargarSemanas();
});