document.addEventListener('DOMContentLoaded', async function() {
    const params = new URLSearchParams(window.location.search);
    const idUnidad = params.get('id_unidad');
    if (!idUnidad) return;

    // Espera a que Quill esté inicializado
    function waitForQuillEditors(callback) {
        let tries = 0;

        function check() {
            if (window.quill_editors && Object.keys(window.quill_editors).length > 0) {
                callback();
            } else if (++tries < 20) {
                setTimeout(check, 100);
            }
        }
        check();
    }

    // Trae los datos de la semana si existen
    const resp = await fetch(`/SysPlanificacion/app/Semana/getSemana.php?id_unidad=${idUnidad}`);
    const data = await resp.json();
    if (data.success && data.semana) {
        const semana = data.semana;
        if (semana.fecha_semana) document.getElementById('semana_inicio').value = semana.fecha_semana;
        if (semana.semana_fin) {
            console.log('Valor de semana_fin:', semana.semana_fin);
            document.getElementById('semana_fin').value = semana.semana_fin;
        }
        if (semana.tiempo_actividades_previas) document.querySelector('input[name="tiempo_previas"]').value = semana.tiempo_actividades_previas;

        waitForQuillEditors(() => {
            // Carga y sincroniza los campos Quill y sus inputs hidden
            if (semana.actividades_previas) {
                window.quill_editors['actividades_previas'].root.innerHTML = semana.actividades_previas;
                const input = document.querySelector('input[name="actividades_previas"]');
                if (input) input.value = semana.actividades_previas;
            }
            if (semana.contenido) {
                window.quill_editors['contenido'].root.innerHTML = semana.contenido;
                const input = document.querySelector('input[name="contenido"]');
                if (input) input.value = semana.contenido;
            }
            const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
            const campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
            dias.forEach(dia => {
                campos.forEach(campo => {
                    const key = `${campo}_${dia}`;
                    if (semana[key] && window.quill_editors[key]) {
                        window.quill_editors[key].root.innerHTML = semana[key];
                        const input = document.querySelector(`input[name="${key}"]`);
                        if (input) input.value = semana[key];
                    }
                });
            });
        });

        // Carga los campos de tiempo y fecha de entrega
        const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        dias.forEach(dia => {
            if (semana[`tiempo_objetivo_${dia}`]) document.querySelector(`input[name="tiempo_objetivo_${dia}"]`).value = semana[`tiempo_objetivo_${dia}`] || '';
            if (semana[`tiempo_apertura_${dia}`]) document.querySelector(`input[name="tiempo_apertura_${dia}"]`).value = semana[`tiempo_apertura_${dia}`] || '';
            if (semana[`tiempo_desarrollo_${dia}`]) document.querySelector(`input[name="tiempo_desarrollo_${dia}"]`).value = semana[`tiempo_desarrollo_${dia}`] || '';
            if (semana[`tiempo_cierre_${dia}`]) document.querySelector(`input[name="tiempo_cierre_${dia}"]`).value = semana[`tiempo_cierre_${dia}`] || '';
            if (semana[`fecha_entrega_${dia}`]) document.querySelector(`input[name="entrega_${dia}"]`).value = semana[`fecha_entrega_${dia}`] || '';
        });
        // Deshabilita el botón guardar si ya existe
        const btnGuardar = document.getElementById('btnGuardarSemana');
        if (btnGuardar) btnGuardar.style.display = 'none';
        document.getElementById('btnVisualizarPDF').disabled = false;
        // Guarda el id de la semana para auto-save
        window.idSemanaGuardada = semana.id_semana;
    }
});