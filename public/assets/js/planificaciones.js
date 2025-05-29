document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnGenerarPlanificacion');
    const spinner = document.getElementById('spinner');
    const select = document.getElementById('asignatura');

    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            btn.setAttribute('disabled', 'disabled');
            spinner.classList.remove('d-none');
            // Efecto bonito: puedes cambiar el timeout o agregar animaciones
            setTimeout(() => {
                const codigo = select.value;
                window.location.href = `crearPlanificaciones.php?codigo=${encodeURIComponent(codigo)}`;
            }, 900); // 900ms de efecto
        });
    }
});