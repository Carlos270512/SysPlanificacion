document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('asignatura');
    const carruselContainer = document.getElementById('carruselUnidadesContainer');
    const unidadesCarousel = document.getElementById('unidadesCarousel');
    const flechaIzquierda = document.getElementById('flechaIzquierda');
    const flechaDerecha = document.getElementById('flechaDerecha');

    async function cargarUnidades(codigoAsignatura) {
        unidadesCarousel.innerHTML = '';
        carruselContainer.style.display = 'none';

        if (!codigoAsignatura) return;

        const resp = await fetch(`/SysPlanificacion/app/Unidad/get_unidades.php?asignatura_codigo=${encodeURIComponent(codigoAsignatura)}`);
        if (!resp.ok) return;
        const unidades = await resp.json();

        if (!unidades.length) {
            unidadesCarousel.innerHTML = `<div class="alert alert-info m-3">No hay unidades registradas.</div>`;
            carruselContainer.style.display = 'block';
            flechaIzquierda.style.display = 'none';
            flechaDerecha.style.display = 'none';
            return;
        }

        unidades.forEach((unidad) => {
            const card = document.createElement('div');
            card.className = 'card unidad-card text-center shadow-sm';
            card.innerHTML = `
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title mb-2 nombre-unidad">Unidad ${unidad.numero_unidad}</h6>
                <div class="mb-2 nombre-unidad">${unidad.nombre}</div>
                <button class="btn btn-ver-editar mt-auto btnVerEditarUnidad" data-id="${unidad.id_unidad}" data-codigo="${codigoAsignatura}">
                    <i class="bi bi-pencil-square"></i> Ver/Editar
                </button>
            </div>
            `;
            unidadesCarousel.appendChild(card);
        });

        carruselContainer.style.display = 'block';
        actualizarFlechas();
    }

    // Evento para el botón Ver/Editar
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btnVerEditarUnidad')) {
            const btn = e.target.closest('.btnVerEditarUnidad');
            const idUnidad = btn.getAttribute('data-id');
            const codigo = btn.getAttribute('data-codigo');
            window.location.href = `crearPlanificaciones.php?codigo=${encodeURIComponent(codigo)}&id_unidad=${encodeURIComponent(idUnidad)}`;
        }
    });

    // Flechas de desplazamiento
    flechaIzquierda.addEventListener('click', function () {
        unidadesCarousel.scrollBy({ left: -240, behavior: 'smooth' });
        setTimeout(actualizarFlechas, 400);
    });
    flechaDerecha.addEventListener('click', function () {
        unidadesCarousel.scrollBy({ left: 240, behavior: 'smooth' });
        setTimeout(actualizarFlechas, 400);
    });

    // Mostrar/ocultar flechas según scroll
    function actualizarFlechas() {
        const scrollLeft = unidadesCarousel.scrollLeft;
        const maxScrollLeft = unidadesCarousel.scrollWidth - unidadesCarousel.clientWidth;
        flechaIzquierda.style.display = scrollLeft > 10 ? 'block' : 'none';
        flechaDerecha.style.display = scrollLeft < maxScrollLeft - 10 ? 'block' : 'none';
    }
    unidadesCarousel.addEventListener('scroll', actualizarFlechas);

    // Cargar unidades al cambiar asignatura
    if (select) {
        select.addEventListener('change', function () {
            cargarUnidades(this.value);
        });
        // Cargar al inicio si hay valor
        if (select.value) {
            cargarUnidades(select.value);
        }
    }
});