document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Pickaday en todos los campos de fecha
    const pickers = document.querySelectorAll('input[type="date"]');
    pickers.forEach(input => {
        new Pikaday({
            field: input,
            format: 'YYYY-MM-DD',
            toString(date) {
                const day = ("0" + date.getDate()).slice(-2);
                const month = ("0" + (date.getMonth() + 1)).slice(-2);
                return date.getFullYear() + '-' + month + '-' + day;
            }
        });
    });

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
                } else {
                    msgDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al guardar') + '</div>';
                }
            } catch (err) {
                msgDiv.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
            }
        });
    }
});