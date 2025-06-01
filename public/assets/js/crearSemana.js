document.addEventListener('DOMContentLoaded', function () {
    // Selecciona los campos de fecha y define los días de la semana
    const semanaInicio = document.querySelector('input[name="semana_inicio"]');
    const semanaFin = document.querySelector('input[name="semana_fin"]');
    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

    // Inicializa el picker para solo permitir lunes
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
                // Solo habilita lunes (getDay() === 1)
                return date.getDay() !== 1;
            }
        });

        // Cuando cambia la fecha de inicio (lunes)
        semanaInicio.addEventListener('change', function () {
            if (!semanaInicio.value) return;
            // Descomponer la fecha seleccionada en año, mes, día (evita problemas de zona horaria)
            const [anio, mes, dia] = semanaInicio.value.split('-').map(Number);
            // Crear la fecha base en local
            const fecha = new Date(anio, mes - 1, dia);

            // Calcula el viernes de esa semana y lo pone en semanaFin
            const viernes = new Date(fecha);
            viernes.setDate(fecha.getDate() + 4);
            semanaFin.value = viernes.toISOString().slice(0, 10);

            // Actualiza los encabezados de la tabla con el nombre y la fecha de cada día
            dias.forEach((diaNombre, idx) => {
                const th = document.getElementById('th_' + diaNombre);
                if (th) {
                    const d = new Date(fecha);
                    d.setDate(fecha.getDate() + idx);
                    // Formato: dd/mm/yyyy
                    const diaNum = ("0" + d.getDate()).slice(-2);
                    const mesNum = ("0" + (d.getMonth() + 1)).slice(-2);
                    const anio = d.getFullYear();
                    th.textContent = th.dataset.nombre + ' - ' + diaNum + '/' + mesNum + '/' + anio;
                }
                // Crea o actualiza el campo oculto para enviar la fecha de cada día
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

    // Manejo del formulario por AJAX
    const form = document.getElementById('formSemana');
    const msgDiv = document.getElementById('msgSemana');
    if (form) {
        form.addEventListener('submit', async function (e) {
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
                    form.reset();
                    // Limpia encabezados y fechas
                    dias.forEach(dia => {
                        const th = document.getElementById('th_' + dia);
                        if (th) th.textContent = th.dataset.nombre;
                        let input = document.querySelector('input[name="fecha_' + dia + '"]');
                        if (input) input.value = '';
                    });
                    semanaFin.value = '';
                } else {
                    msgDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al guardar') + '</div>';
                }
            } catch (err) {
                msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
            }
        });
    }
});