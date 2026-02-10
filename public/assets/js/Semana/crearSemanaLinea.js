document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formSemanaPL');
    if (!form) return;

    console.log('=== VERIFICANDO ELEMENTOS DEL DOM ===');
    console.log('✓ Formulario encontrado:', form);
    
    // Verificar que existen los editores en el DOM
    const editoresEsperados = [
        'editor_contenido_pl',
        'editor_tema_clase_SAnterior_pl',
        'editor_atividades_previas_clase_pl',
        'editor_objetivo_pl'
    ];
    
    editoresEsperados.forEach(id => {
        const el = document.getElementById(id);
        console.log(`${el ? '✓' : '✗'} Editor ${id}:`, el ? 'EXISTE' : 'NO ENCONTRADO');
    });
    
    // Verificar que existen los inputs hidden
    const inputsEsperados = [
        'contenido',
        'tema_clase_SAnterior',
        'Atividades_previas_clase',
        'tiempo_actividades_previas_clase',
        'objetivo'
    ];
    
    inputsEsperados.forEach(name => {
        const input = form.querySelector(`input[name="${name}"]`);
        console.log(`${input ? '✓' : '✗'} Input[name="${name}"]:`, input ? 'EXISTE' : 'NO ENCONTRADO');
    });
    console.log('=====================================');

    // --- Inicialización de Quill.js ---
    const quillToolbar = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
    ];

    window.quill_editors_pl = {};

    // Define los campos y su relación con los IDs de los editores
    const quillFields = [
        { name: 'contenido', id: 'editor_contenido_pl' },
        { name: 'tema_clase_SAnterior', id: 'editor_tema_clase_SAnterior_pl' },
        { name: 'Atividades_previas_clase', id: 'editor_atividades_previas_clase_pl' },
        { name: 'objetivo', id: 'editor_objetivo_pl' },
        { name: 'innovacion', id: 'editor_innovacion_pl' },
        { name: 'apertura', id: 'editor_apertura_pl' },
        { name: 'desarrollo', id: 'editor_desarrollo_pl' },
        { name: 'cierre', id: 'editor_cierre_pl' },
        { name: 'trabajo_autonomo', id: 'editor_trabajo_autonomo_pl' }
    ];

    // Inicializar todos los editores Quill
    quillFields.forEach(field => {
        const el = document.getElementById(field.id);
        if (el) {
            window.quill_editors_pl[field.name] = new Quill(`#${field.id}`, {
                theme: 'snow',
                modules: { toolbar: quillToolbar }
            });
            console.log(`✓ Editor Quill inicializado: ${field.name} -> ID: ${field.id}`);
            
            // Sincroniza el contenido con el input hidden en cada cambio
            window.quill_editors_pl[field.name].on('text-change', function () {
                const input = form.querySelector(`input[name="${field.name}"]`);
                if (input) {
                    const contenido = window.quill_editors_pl[field.name].root.innerHTML;
                    input.value = contenido;
                    console.log(`✓ Sincronizado ${field.name}: "${contenido.substring(0, 80)}..."`);
                } else {
                    console.error(`✗ NO SE ENCONTRÓ input[name="${field.name}"]`);
                }
            });
        } else {
            console.error(`✗ NO SE ENCONTRÓ el elemento con ID: ${field.id}`);
        }
    });

    // Función para pintar verde
    function pintarVerde(element) {
        const original = element.style.backgroundColor;
        element.style.backgroundColor = '#b6fcb6';
        setTimeout(() => { element.style.backgroundColor = original; }, 1200);
    }

    // Función de auto-guardado mejorada (campo por campo como en semana normal)
    async function autoGuardarSemanaPL(inputEditado, campoNombre = null) {
        const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
        if (!idSemanaLineaInput || !idSemanaLineaInput.value) {
            console.log('No se puede auto-guardar: no hay id_semana_linea');
            return;
        }

        const idSemanaLinea = idSemanaLineaInput.value;
        
        // Determinar el campo y valor a guardar
        let campo = campoNombre || inputEditado?.name;
        let valor = '';
        
        if (inputEditado) {
            // Si es un editor Quill, obtener el contenido HTML
            if (window.quill_editors_pl[campo]) {
                valor = window.quill_editors_pl[campo].root.innerHTML;
                // Sincronizar con el input hidden
                inputEditado.value = valor;
            } else {
                valor = inputEditado.value;
            }
        }

        console.log(`Auto-guardando campo: ${campo}, valor: ${valor}, id_semana_linea: ${idSemanaLinea}`);

        // Preparar los datos para enviar solo el campo modificado
        const formData = new FormData();
        formData.append('id_semana_linea', idSemanaLinea);
        formData.append(campo, valor);

        try {
            const resp = await fetch('/SysPlanificacion/app/SemanaLinea/updateSemanaLinea.php', {
                method: 'POST',
                body: formData
            });
            const data = await resp.json();
            const msgDiv = document.getElementById('msgSemana');
            
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                // Mostrar mensaje de éxito temporal
                msgDiv.innerHTML = '<div class="alert alert-success py-2 mb-2" style="font-size:15px;">¡Guardado automáticamente!</div>';
                setTimeout(() => { msgDiv.innerHTML = ''; }, 1500);

                // Pintar de verde el elemento editado
                if (inputEditado) {
                    // Si es un input hidden de Quill, pintar el editor
                    if (window.quill_editors_pl[campo]) {
                        pintarVerde(window.quill_editors_pl[campo].root);
                    } else {
                        pintarVerde(inputEditado);
                    }
                }
            } else {
                console.error('Error al guardar:', data.message);
                msgDiv.innerHTML = `<div class="alert alert-danger py-2 mb-2" style="font-size:15px;">Error: ${data.message}</div>`;
                setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
            }
        } catch (e) {
            console.error('Error en auto-guardado:', e);
            const msgDiv = document.getElementById('msgSemana');
            msgDiv.innerHTML = '<div class="alert alert-danger py-2 mb-2" style="font-size:15px;">Error de conexión</div>';
            setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
        }
    }

    // Auto-guardado al perder el foco en los editores Quill
    quillFields.forEach(field => {
        if (window.quill_editors_pl[field.name]) {
            console.log(`Configurando auto-guardado Quill para: ${field.name}`);
            
            // Usar el evento selection-change para detectar blur
            window.quill_editors_pl[field.name].on('selection-change', function (range, oldRange, source) {
                if (oldRange && !range) { // blur (perdió el foco)
                    console.log(`Blur detectado en editor Quill: ${field.name}`);
                    
                    const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
                    if (!idSemanaLineaInput || !idSemanaLineaInput.value) {
                        console.log('No hay id_semana_linea, esperando que se llene la fecha primero');
                        
                        // Mostrar mensaje temporal indicando que se debe llenar la fecha primero
                        const msgDiv = document.getElementById('msgSemana');
                        msgDiv.innerHTML = '<div class="alert alert-info py-2 mb-2" style="font-size:14px;">Por favor, selecciona primero la fecha (sábado) para poder guardar los cambios.</div>';
                        setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
                        return;
                    }
                    
                    const input = form.querySelector(`input[name="${field.name}"]`);
                    if (input) {
                        // Sincronizar contenido antes de guardar
                        const contenido = window.quill_editors_pl[field.name].root.innerHTML;
                        input.value = contenido;
                        console.log(`Auto-guardando Quill ${field.name}, contenido: ${contenido.substring(0, 100)}...`);
                        autoGuardarSemanaPL(input, field.name);
                    } else {
                        console.warn(`No se encontró input hidden para: ${field.name}`);
                    }
                }
            });
            
            // ADICIONAL: También escuchar el evento text-change con un debounce
            let timeoutId = null;
            window.quill_editors_pl[field.name].on('text-change', function (delta, oldDelta, source) {
                if (source === 'user') {
                    console.log(`Cambio de texto detectado en: ${field.name}`);
                    
                    // Limpiar timeout anterior
                    if (timeoutId) clearTimeout(timeoutId);
                    
                    // Esperar 2 segundos después del último cambio para auto-guardar
                    timeoutId = setTimeout(() => {
                        const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
                        if (idSemanaLineaInput && idSemanaLineaInput.value) {
                            const input = form.querySelector(`input[name="${field.name}"]`);
                            if (input) {
                                const contenido = window.quill_editors_pl[field.name].root.innerHTML;
                                input.value = contenido;
                                console.log(`Auto-guardado automático después de 2s en ${field.name}`);
                                autoGuardarSemanaPL(input, field.name);
                            }
                        }
                    }, 2000);
                }
            });
        }
    });

    // Auto-guardado para inputs normales (tiempo y fechas)
    const hoy = new Date();
    let fechaSabadoAnterior = '';
    let fechaEntregaAnterior = '';

    // Campos que necesitan auto-guardado
    const camposAutoguardado = ['tiempo_actividades_previas_clase', 'tiempo_apertura', 'tiempo_desarrollo', 'tiempo_cierre', 'fecha_sabado', 'fecha_entrega'];
    
    camposAutoguardado.forEach(name => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (!input) {
            console.warn(`Campo no encontrado: ${name}`);
            return;
        }

        console.log(`Configurando auto-guardado para: ${name}`);

        // Guardar valor anterior al hacer foco
        if (name === 'fecha_sabado') {
            input.addEventListener('focus', () => {
                fechaSabadoAnterior = input.value;
            });
        }
        if (name === 'fecha_entrega') {
            input.addEventListener('focus', () => {
                fechaEntregaAnterior = input.value;
            });
        }

        // Validación en tiempo real para fecha_sabado
        if (name === 'fecha_sabado') {
            input.addEventListener('change', async () => {
                if (input.value) {
                    const fechaSeleccionada = new Date(input.value);
                    if (fechaSeleccionada.getDay() !== 6) {
                        await Swal.fire({
                            icon: 'warning',
                            title: 'Fecha inválida',
                            text: 'Solo puede seleccionar días sábado.',
                            confirmButtonText: 'OK'
                        });
                        input.value = fechaSabadoAnterior;
                        input.focus();
                        return;
                    }
                }
            });
        }

        // Auto-guardado al perder el foco
        input.addEventListener('blur', async () => {
            console.log(`Blur en campo: ${name}, valor: ${input.value}`);
            
            const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
            
            // Si no existe id_semana_linea y es fecha_sabado, crear el registro primero
            if ((!idSemanaLineaInput || !idSemanaLineaInput.value) && name === 'fecha_sabado' && input.value) {
                console.log('Creando nuevo registro de semana_linea con fecha_sabado');
                
                // Validar que sea sábado primero
                const fechaSeleccionada = new Date(input.value);
                if (fechaSeleccionada.getDay() !== 6) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'Solo puede seleccionar días sábado.',
                        confirmButtonText: 'OK'
                    });
                    input.value = fechaSabadoAnterior;
                    input.focus();
                    return;
                }
                
                // Sincronizar todos los editores Quill antes de crear
                quillFields.forEach(field => {
                    const inputHidden = form.querySelector(`input[name="${field.name}"]`);
                    if (inputHidden && window.quill_editors_pl[field.name]) {
                        const contenidoQuill = window.quill_editors_pl[field.name].root.innerHTML;
                        inputHidden.value = contenidoQuill;
                        console.log(`✓ Sincronizando ${field.name}:`);
                        console.log(`  - Contenido Quill: "${contenidoQuill.substring(0, 100)}..."`);
                        console.log(`  - Input hidden value: "${inputHidden.value.substring(0, 100)}..."`);
                    }
                });
                
                // Crear el registro con TODOS los datos del formulario
                const formData = new FormData(form);
                
                console.log('=== FormData que se enviará al servidor ===');
                for (let pair of formData.entries()) {
                    const valor = typeof pair[1] === 'string' ? pair[1].substring(0, 100) : pair[1];
                    console.log(`  ${pair[0]}: "${valor}"`);
                }
                console.log('===========================================');
                try {
                    const resp = await fetch('/SysPlanificacion/app/SemanaLinea/createSemanaLinea.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    
                    if (data.success && data.id_semana_linea) {
                        console.log(`Registro creado con id_semana_linea: ${data.id_semana_linea}`);
                        idSemanaLineaInput.value = data.id_semana_linea;
                        
                        // Mostrar mensaje de éxito
                        const msgDiv = document.getElementById('msgSemana');
                        msgDiv.innerHTML = '<div class="alert alert-success py-2 mb-2" style="font-size:15px;">¡Semana creada! Todos los cambios se guardarán automáticamente.</div>';
                        setTimeout(() => { msgDiv.innerHTML = ''; }, 3000);
                        
                        // Pintar campo de verde
                        pintarVerde(input);
                        
                        // Pintar de verde todos los editores Quill que tienen contenido
                        quillFields.forEach(field => {
                            if (window.quill_editors_pl[field.name]) {
                                const contenido = window.quill_editors_pl[field.name].root.innerHTML;
                                if (contenido && contenido.trim() !== '' && contenido.trim() !== '<p><br></p>') {
                                    console.log(`Editor ${field.name} tiene contenido, pintando verde`);
                                    pintarVerde(window.quill_editors_pl[field.name].root);
                                }
                            }
                        });
                        
                        // Pintar de verde todos los campos normales que tienen contenido
                        camposAutoguardado.forEach(campoName => {
                            if (campoName !== 'fecha_sabado') {
                                const campoInput = form.querySelector(`input[name="${campoName}"]`);
                                if (campoInput && campoInput.value && campoInput.value.trim() !== '') {
                                    console.log(`Campo ${campoName} tiene contenido, pintando verde`);
                                    pintarVerde(campoInput);
                                }
                            }
                        });
                    } else {
                        console.error('Error al crear registro:', data.message);
                        await Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo crear el registro',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (e) {
                    console.error('Error al crear registro:', e);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor',
                        confirmButtonText: 'OK'
                    });
                }
                return;
            }
            
            // Si no hay id_semana_linea después de intentar crear, no continuar
            if (!idSemanaLineaInput || !idSemanaLineaInput.value) {
                console.log('No hay id_semana_linea, no se puede auto-guardar');
                return;
            }

            // Validaciones especiales para fecha_sabado antes de guardar
            if (name === 'fecha_sabado' && input.value) {
                const fechaSeleccionada = new Date(input.value);
                
                // Validar mes actual o futuro
                if (
                    fechaSeleccionada.getFullYear() < hoy.getFullYear() ||
                    (fechaSeleccionada.getFullYear() === hoy.getFullYear() && fechaSeleccionada.getMonth() < hoy.getMonth())
                ) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'Solo puede seleccionar fechas del mes actual o futuro.',
                        confirmButtonText: 'OK'
                    });
                    input.value = fechaSabadoAnterior;
                    input.focus();
                    return;
                }
                
                // Validar que sea sábado
                if (fechaSeleccionada.getDay() !== 6) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'Solo puede seleccionar días sábado.',
                        confirmButtonText: 'OK'
                    });
                    input.value = fechaSabadoAnterior;
                    input.focus();
                    return;
                }
            }

            // Validación para fecha_entrega (mes actual o futuro)
            if (name === 'fecha_entrega' && input.value) {
                const fechaSeleccionada = new Date(input.value);
                if (
                    fechaSeleccionada.getFullYear() < hoy.getFullYear() ||
                    (fechaSeleccionada.getFullYear() === hoy.getFullYear() && fechaSeleccionada.getMonth() < hoy.getMonth())
                ) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'No puede seleccionar una fecha de un mes anterior al actual.',
                        confirmButtonText: 'OK'
                    });
                    input.value = fechaEntregaAnterior;
                    input.focus();
                    return;
                }
            }

            // Ejecutar auto-guardado
            console.log(`Ejecutando auto-guardado para ${name}`);
            await autoGuardarSemanaPL(input, name);
        });
    });

    // Al enviar el formulario manualmente
    const msgDiv = document.getElementById('msgSemana');
    form.addEventListener('submit', async function (e) {
        // Sincronizar todos los editores Quill antes de enviar
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
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '¡Semana Semipresencial guardada correctamente!',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar',
                    confirmButtonText: 'OK'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'OK'
            });
        }
    });

    // Cargar datos si ya existe una semana S/EL
    const idSemanaLineaInput = form.querySelector('input[name="id_semana_linea"]');
    if (idSemanaLineaInput && idSemanaLineaInput.value) {
        console.log(`Cargando datos de semana_linea: ${idSemanaLineaInput.value}`);
        fetch(`/SysPlanificacion/app/SemanaLinea/getSemanaLineaById.php?id_semana_linea=${idSemanaLineaInput.value}`)
            .then(resp => resp.json())
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success && data.semana) {
                    // Llena los campos normales (incluso si están vacíos)
                    ['fecha_sabado', 'tiempo_actividades_previas_clase', 'tiempo_apertura', 'tiempo_desarrollo', 'tiempo_cierre', 'fecha_entrega'].forEach(name => {
                        const input = form.querySelector(`input[name="${name}"]`);
                        if (input) {
                            input.value = data.semana[name] || '';
                            console.log(`Campo ${name} cargado: ${input.value}`);
                        }
                    });
                    
                    // Llena los editores Quill (incluso si están vacíos)
                    quillFields.forEach(field => {
                        if (window.quill_editors_pl[field.name]) {
                            const contenido = data.semana[field.name] || '';
                            window.quill_editors_pl[field.name].root.innerHTML = contenido;
                            const input = form.querySelector(`input[name="${field.name}"]`);
                            if (input) {
                                input.value = contenido;
                            }
                            console.log(`Editor Quill ${field.name} cargado con: ${contenido.substring(0, 50)}...`);
                        }
                    });

                    const collapseEl = document.getElementById('collapsePlanificacion');
                    if (collapseEl) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        bsCollapse.show();
                    }
                    
                    console.log('Todos los datos cargados correctamente');
                } else {
                    console.error('Error al cargar datos:', data.message);
                }
            })
            .catch(err => {
                console.error('Error en fetch:', err);
            });
    }

    // --- Botón Visualizar PDF Línea ---
    const btnVisualizarPDFLinea = document.getElementById('btnVisualizarPDFLinea');
    if (btnVisualizarPDFLinea) {
        btnVisualizarPDFLinea.addEventListener('click', function () {
            const idSemanaLinea = document.getElementById('id_semana_linea').value;
            if (!idSemanaLinea) {
                alert('No hay semana seleccionada.');
                return;
            }
            const iframe = document.getElementById('iframePDF');
            if (iframe) {
                iframe.src = `/SysPlanificacion/app/gestionPDFS/visualizarSemanLineaPDF.php?id_semana_linea=${idSemanaLinea}`;
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizarPDF'));
                modal.show();
            }
        });
    }

    // --- Botón Guardar PDF ---
    const btnGuardarPDF = document.getElementById('btnGuardarPDF');
    if (btnGuardarPDF) {
        btnGuardarPDF.addEventListener('click', async function () {
            const nombreUnidad = window.nombreUnidad || '';
            const idUnidad = document.querySelector('input[name="unidad_id"]')?.value || '';
            const fechaCreacion = new Date().toLocaleString('es-EC');

            const result = await Swal.fire({
                title: '¿Deseas guardar el PDF?',
                html: `
                    <div style="text-align:left;">
                        <b>Nombre del Archivo:</b> ${nombreUnidad}<br>
                        <b>ID de la Unidad:</b> ${idUnidad}<br>
                        <b>Fecha de creación:</b> ${fechaCreacion}
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                const iframe = document.getElementById('iframePDF');
                if (!iframe || !iframe.src) {
                    Swal.fire('Error', 'No se encontró el PDF para guardar.', 'error');
                    return;
                }

                try {
                    const pdfResp = await fetch(iframe.src);
                    if (!pdfResp.ok) throw new Error('No se pudo obtener el PDF');
                    const pdfBlob = await pdfResp.blob();

                    const formData = new FormData();
                    formData.append('unidad_id', idUnidad);
                    formData.append('nombre_archivo', nombreUnidad + '.pdf');
                    formData.append('archivo_pdf', pdfBlob, nombreUnidad + '.pdf');

                    const resp = await fetch('/SysPlanificacion/app/gestionPDFS/guardarPlanificacion.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await resp.json();
                    if (data.success) {
                        Swal.fire('¡Guardado!', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo guardar el PDF', 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', err.message || 'No se pudo guardar el PDF', 'error');
                }
            }
        });
    }
});

