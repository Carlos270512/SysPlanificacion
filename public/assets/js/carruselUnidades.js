document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('asignatura');
    const carruselContainer = document.getElementById('carruselUnidadesContainer');
    const unidadesCarousel = document.getElementById('unidadesCarousel');
    const flechaIzquierda = document.getElementById('flechaIzquierda');
    const flechaDerecha = document.getElementById('flechaDerecha');
    const buscadorContainer = document.getElementById('buscadorUnidadesContainer');
    const buscador = document.getElementById('buscadorUnidades');
    let unidadesOriginales = [];

    async function cargarUnidades(codigoAsignatura) {
        unidadesCarousel.innerHTML = '';
        carruselContainer.style.display = 'none';
        buscadorContainer.style.display = 'none';
        unidadesOriginales = [];

        if (!codigoAsignatura) return;

        const resp = await fetch(`/SysPlanificacion/app/Unidad/get_unidades.php?asignatura_codigo=${encodeURIComponent(codigoAsignatura)}`);
        if (!resp.ok) return;
        let unidades = await resp.json();
        
        // Filtrar Unidad 1 solo para el rol DOCENTE
        if (typeof userRole !== 'undefined' && userRole === 'DOCENTE') {
            unidades = unidades.filter(u => u.numero_unidad != 1);
        }
        
        unidadesOriginales = unidades;

        mostrarUnidades(unidadesOriginales);

        buscador.value = '';
        buscadorContainer.style.display = 'block';
    }

    function mostrarUnidades(unidades) {
        unidadesCarousel.innerHTML = '';
        if (!unidades.length) {
            unidadesCarousel.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="height:180px; width:100%;">
                <div class="alert alert-info text-center w-100 m-0">
                    No hay unidades registradas.
                </div>
            </div>
        `;
            carruselContainer.style.display = 'block';
            flechaIzquierda.style.display = 'none';
            flechaDerecha.style.display = 'none';
            return;
        }
        unidades.forEach((unidad) => {
            const card = document.createElement('div');
            // Agregar clase especial si es Unidad 1 (Unidad Base)
            const esUnidadBase = unidad.numero_unidad == 1;
            card.className = esUnidadBase ? 'card unidad-card unidad-base text-center shadow-sm' : 'card unidad-card text-center shadow-sm';
            card.innerHTML = `
            <div class="card-body d-flex flex-column justify-content-between">
                <h6 class="card-title mb-2 nombre-unidad">Unidad ${unidad.numero_unidad}</h6>
                <div class="mb-2 nombre-unidad">${unidad.nombre}</div>
                <button class="btn btn-ver-editar mt-auto btnVerEditarUnidad" data-id="${unidad.id_unidad}" data-codigo="${unidad.asignatura_codigo || ''}">
                    <i class="bi bi-pencil-square"></i> Ver/Editar
                </button>
            </div>
            `;
            unidadesCarousel.appendChild(card);
        });
        carruselContainer.style.display = 'block';
        actualizarFlechas();
    }

    // Buscador de unidades
    if (buscador) {
        buscador.addEventListener('input', function () {
            const texto = this.value.trim().toLowerCase();
            let filtradas = unidadesOriginales.filter(u => u.nombre.toLowerCase().includes(texto));
            
            mostrarUnidades(filtradas);
        });
    }

    // Evento para el botón Ver/Editar
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btnVerEditarUnidad')) {
            const btn = e.target.closest('.btnVerEditarUnidad');
            const idUnidad = btn.getAttribute('data-id');
            // Tomar el código de la asignatura seleccionado actualmente
            const codigo = select.value;
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
        const scrollLeft = Math.round(unidadesCarousel.scrollLeft);
        const maxScrollLeft = Math.round(unidadesCarousel.scrollWidth - unidadesCarousel.clientWidth);
        flechaIzquierda.style.display = scrollLeft > 5 ? 'block' : 'none';
        flechaDerecha.style.display = scrollLeft < maxScrollLeft - 5 ? 'block' : 'none';
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

    // Botón Planificación Principal
    const btnPlanificacionPrincipal = document.getElementById('btnPlanificacionPrincipal');
    if (btnPlanificacionPrincipal) {
        btnPlanificacionPrincipal.addEventListener('click', function() {
            // Buscar la Unidad 1 en el carrusel
            const todasLasUnidades = unidadesCarousel.querySelectorAll('.unidad-card');
            let unidad1 = null;
            let btnVerEditar1 = null;

            todasLasUnidades.forEach(card => {
                const titulo = card.querySelector('.nombre-unidad');
                if (titulo && titulo.textContent.includes('Unidad 1')) {
                    unidad1 = card;
                    btnVerEditar1 = card.querySelector('.btnVerEditarUnidad');
                }
            });

            if (unidad1 && btnVerEditar1) {
                // Hacer scroll a la Unidad 1
                unidad1.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                
                // Agregar efecto de brillo
                unidad1.classList.add('unidad-destaque');
                
                // Después de 4.5 segundos (3 ciclos de animación), hacer clic automáticamente
                setTimeout(() => {
                    unidad1.classList.remove('unidad-destaque');
                    btnVerEditar1.click();
                }, 4500);
            } else {
                // No existe la Unidad 1
                alert('⚠️ No se encontró la Unidad 1 (Planificación Principal).\n\nPor favor, cree una planificación primero usando el botón "Generar Planificación".');
            }
        });
    }
});