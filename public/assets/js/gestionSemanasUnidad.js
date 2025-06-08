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
                const semana = data.semana;
                // Fechas principales
                document.getElementById('semana_inicio').value = semana.fecha_semana || '';
                document.getElementById('semana_fin').value = semana.semana_fin || '';
                document.querySelector('input[name="tiempo_previas"]').value = semana.tiempo_actividades_previas || '';
                // Actividades previas
                if (window.quill_editors && window.quill_editors['actividades_previas']) {
                    window.quill_editors['actividades_previas'].root.innerHTML = semana.actividades_previas || '';
                }
                document.querySelector('input[name="actividades_previas"]').value = semana.actividades_previas || '';
                // Contenido
                if (window.quill_editors && window.quill_editors['contenido']) {
                    window.quill_editors['contenido'].root.innerHTML = semana.contenido || '';
                }
                document.querySelector('input[name="contenido"]').value = semana.contenido || '';

                // Campos de cada día
                const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                const campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
                dias.forEach(dia => {
                    campos.forEach(campo => {
                        const key = `${campo}_${dia}`;
                        if (window.quill_editors && window.quill_editors[key]) {
                            window.quill_editors[key].root.innerHTML = semana[key] || '';
                        }
                        const input = document.querySelector(`input[name="${key}"]`);
                        if (input) input.value = semana[key] || '';
                    });
                    // Tiempos y fecha de entrega
                    if (document.querySelector(`input[name="tiempo_objetivo_${dia}"]`))
                        document.querySelector(`input[name="tiempo_objetivo_${dia}"]`).value = semana[`tiempo_objetivo_${dia}`] || '';
                    if (document.querySelector(`input[name="tiempo_apertura_${dia}"]`))
                        document.querySelector(`input[name="tiempo_apertura_${dia}"]`).value = semana[`tiempo_apertura_${dia}`] || '';
                    if (document.querySelector(`input[name="tiempo_desarrollo_${dia}"]`))
                        document.querySelector(`input[name="tiempo_desarrollo_${dia}"]`).value = semana[`tiempo_desarrollo_${dia}`] || '';
                    if (document.querySelector(`input[name="tiempo_cierre_${dia}"]`))
                        document.querySelector(`input[name="tiempo_cierre_${dia}"]`).value = semana[`tiempo_cierre_${dia}`] || '';
                    if (document.querySelector(`input[name="entrega_${dia}"]`))
                        document.querySelector(`input[name="entrega_${dia}"]`).value = semana[`fecha_entrega_${dia}`] || '';
                });

                // Guardar el id de la semana para autoguardado
                window.idSemanaGuardada = semana.id_semana;
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
    if (formSemana) {
    formSemana.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(formSemana);
        try {
            const resp = await fetch(formSemana.action, {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            if (data.success) {
                Swal.fire('Guardado', 'Semana guardada correctamente.', 'success');
                // Limpia el formulario si es necesario
                // formSemana.reset();
                // Recarga la tabla de semanas automáticamente
                cargarSemanas();
                // Opcional: deshabilita el botón guardar si solo se permite una semana activa
                document.getElementById('btnGuardarSemana').disabled = true;
                document.getElementById('btnGuardarSemana').style.display = 'none';
                document.getElementById('btnVisualizarPDF').disabled = false;
            } else {
                Swal.fire('Error', data.message || 'No se pudo guardar la semana.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error al guardar la semana.', 'error');
        }
    });
}
});