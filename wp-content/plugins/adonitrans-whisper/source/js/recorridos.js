jQuery(document).ready(function($) {

    /*CODIGO OPTIMIZADO CAMBIO DE CIUDAD*/
    // Función para manejar la lógica de negocio (solicitud AJAX y actualización del DOM)
    function updateBarrioSelect(ciudadId, $barrioSelect) {
        if (!ciudadId) {
            return $barrioSelect.empty().append('<option value="">Selecciona un barrio</option>').prop('disabled', true);
        }

        $('body').addClass('actloader');
        $.post(recorridoAjax.ajaxurl, {
            action: 'get_barrios',
            ciudad_id: ciudadId
        }, function(response) {
            $('body').removeClass('actloader');

            if (!response.success) {
                showError(response.data.message || 'No se encontraron barrios.');
                return;
            }

            $barrioSelect.empty().append('<option value="">Selecciona un barrio</option>');

            $.each(response.data, function(_, barrio) {
                $barrioSelect.append('<option value="' + barrio.barrio + '"' + (barrio.zona ? ' data-zona="' + barrio.zona + '"' : '') + '>' + barrio.barrio + '</option>');
            });

            $barrioSelect.prop('disabled', false).trigger('change');
        }, 'json').fail(function() {
            $('body').removeClass('actloader');
            showError('Error al cargar los barrios. Intenta nuevamente.');
        });
    }

    // Función para mostrar errores
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message
        });
    }

    // Función para registrar listeners dinámicamente
    function handleCityChange(selector, barrioSelector) {
        $(document).on('change', selector, function() {
            var ciudadId = this.value,
                $barrioSelect = $(this).closest('.franja').find(barrioSelector);
            updateBarrioSelect(ciudadId, $barrioSelect);
        });
    }

    // Registro de listeners iniciales
    handleCityChange('.ciudad_adicional_recorrido', '.barrio_adicional_recorrido');
    handleCityChange('.ciudad_origen_pasajero_adicional', '.barrio_origen_pasajero_adi');
    handleCityChange('.ciudad_destino_pasajero_adicional', '.barrio_destino_pasajero_adi');

    $(document).on('change', '#id_solicitante_recorrido', function() {
        let idSolicitante = $(this).val();
        let centro_de_costo = $("#centro_de_costo");
        let ciudad_inicio = $("#ciudad_inicio");
        let selectBarrio = $('#barrio_inicio');
        let selectBarrioFin = $('#barrio_fin');
        let selectCiudadFin = $('#ciudad_fin');
        let ciudadAdicionalRecorrido = $('.ciudad_adicional_recorrido');
        let plantillarecorridociudad = $('#plantilla-recorrido .ciudad');
        let selectrutas = $("#tarifaxempresa");

        if (idSolicitante !== '0') {

            $.ajax({
                url: recorridoAjax.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_rutas',
                    id_solicitante: idSolicitante,
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        // Limpiar el select antes de agregar nuevas opciones
                        selectrutas.empty().append('<option value="0">Selecciona un centro de costo</option>');

                        // Añadir las opciones dinámicamente
                        $.each(response.data, function(index, centro) {
                            selectrutas.append('<option data-valor="' + centro.valor + '" data-nombre="' + centro.nombre_de_ruta + '" value="' + centro.codigo + '"> (' + centro.codigo + ') ' + centro.nombre_de_ruta + '</option>');
                        });

                        selectrutas.prop('disabled', false).trigger('change');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron centros de costo.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los centros de costo. Intenta nuevamente.'
                    });
                }
            });


            $.ajax({
                url: recorridoAjax.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_centros_de_costo',
                    id_solicitante: idSolicitante,
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        // Limpiar el select antes de agregar nuevas opciones
                        centro_de_costo.empty().append('<option value="0">Selecciona un centro de costo</option>');

                        // Añadir las opciones dinámicamente
                        $.each(response.data, function(index, centro) {
                            centro_de_costo.append('<option value="' + centro.codigo + '">' + centro.nombre + '</option>');
                        });

                        centro_de_costo.prop('disabled', false).trigger('change');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron centros de costo.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los centros de costo. Intenta nuevamente.'
                    });
                }
            });

            $.ajax({
                url: recorridoAjax.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'consultar_ciudades_por_empresa',
                    id_solicitante: idSolicitante,
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {

                        $('#wrap-puntos-recorrido .franja').not(':first').each(function() {
                            $(this).find('.remove').click();
                        });

                        // Limpiar el select antes de agregar nuevas opciones
                        ciudad_inicio.empty().append('<option value="0">Selecciona una ciudad</option>');
                        selectCiudadFin.empty().append('<option value="0">Selecciona una ciudad</option>');
                        selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>');
                        selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>');

                        // Añadir las opciones dinámicamente
                        $.each(response.data, function(index, ciudad) {
                            ciudad_inicio.append('<option value="' + ciudad.id + '">' + ciudad.ciudad_para_empresa + '</option>');
                            selectCiudadFin.append('<option value="' + ciudad.id + '">' + ciudad.ciudad_para_empresa + '</option>');

                            ciudadAdicionalRecorrido.each(function() {
                                $(this).append('<option value="' + ciudad.id + '">' + ciudad.ciudad_para_empresa + '</option>');
                            });

                            plantillarecorridociudad.each(function() {
                                $(this).append('<option value="' + ciudad.id + '">' + ciudad.ciudad_para_empresa + '</option>');
                            });

                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron centros de costo.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los centros de costo. Intenta nuevamente.'
                    });
                }
            });
        } else {
            ciudad_inicio.empty().append('<option value="0">Selecciona una ciudad</option>');
            selectCiudadFin.empty().append('<option value="0">Selecciona una ciudad</option>');
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>');
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>');

            centro_de_costo.prop('disabled', true).trigger('change');
            centro_de_costo.empty().append('<option value="0">Selecciona un centro de costo</option>').trigger('change');
        }
    });

    function actualizarSelects(selects, datos) {
        selects.each(function() {
            let select = $(this);

            let valorSeleccionado = select.val();

            select.empty().append('<option value="">Selecciona un barrio</option>');

            $.each(datos, function(index, barrio) {
                let dataZona = barrio.zona ? ' data-zona="' + barrio.zona + '"' : '';
                let option = '<option' + dataZona + ' value="' + barrio.barrio + '">' + barrio.barrio + '</option>';
                select.append(option);
            });

            if (valorSeleccionado && select.find('option[value="' + valorSeleccionado + '"]').length) {
                select.val(valorSeleccionado);
            }

            if (select.hasClass('select2-hidden-accessible')) {
                select.trigger('change');
            }
        });
    }

    function llenarSelects(barriosEmpresa, data) {
        let selectCiudadInicio = $('#ciudad_inicio');
        let selectCiudadFin = $('#ciudad_fin');
        let selectBarrioInicio = $('#barrio_inicio');
        let selectBarrioFin = $('#barrio_fin');

        // Vaciar selects antes de llenarlos
        selectCiudadInicio.empty().append('<option value="">Seleccione una ciudad</option>');
        selectCiudadFin.empty().append('<option value="">Seleccione una ciudad</option>');
        selectBarrioInicio.empty().append('<option value="">Seleccione un barrio</option>').prop('disabled', false);
        selectBarrioFin.empty().append('<option value="">Seleccione un barrio</option>').prop('disabled', false);

        // Iterar sobre las ciudades y agregarlas a los selects
        barriosEmpresa.forEach(ciudad => {
            selectCiudadInicio.append(`<option value="${ciudad.id}">${ciudad.ciudad}</option>`);
            selectCiudadFin.append(`<option value="${ciudad.id}">${ciudad.ciudad}</option>`);
        });

        // Función para llenar barrios según la ciudad seleccionada
        function llenarBarrios(ciudadId, selectBarrio, barrioSeleccionado = '') {
            let ciudad = barriosEmpresa.find(c => c.id == ciudadId);
            selectBarrio.empty().append('<option value="">Seleccione un barrio</option>');

            if (ciudad && ciudad.barrios.length > 0) {
                ciudad.barrios.forEach(barrio => {
                    let value = barrio.zona;
                    let text = barrio.barrio || barrio.zona; // Usar zona si existe, si no el barrio
                    let isSelected = barrioSeleccionado == value ? 'selected' : '';
                    selectBarrio.append(`<option value="${value}" ${isSelected}>${text}</option>`);
                });
            }
        }

        // Agregar evento onchange para actualizar barrios dinámicamente
        selectCiudadInicio.on('change', function() {
            let ciudadId = $(this).val();
            llenarBarrios(ciudadId, selectBarrioInicio);
        });

        selectCiudadFin.on('change', function() {
            let ciudadId = $(this).val();
            llenarBarrios(ciudadId, selectBarrioFin);
        });

        // Seleccionar la ciudad y llenar barrios de inicio si existen
        if (data.ciudad_inicio) {
            selectCiudadInicio.val(data.ciudad_inicio).trigger('change');
            llenarBarrios(data.ciudad_inicio, selectBarrioInicio, data.barrio_inicio);
        }

        // Seleccionar la ciudad y llenar barrios de fin si existen
        if (data.ciudad_fin) {
            selectCiudadFin.val(data.ciudad_fin).trigger('change');
            llenarBarrios(data.ciudad_fin, selectBarrioFin, data.barrio_fin);
        }
    }

    function llenarSelectsRepetidor(barriosEmpresa, $fila, data) {
        let selectCiudad = $fila.find('.ciudad_adicional_recorrido');
        let selectBarrio = $fila.find('.barrio_adicional_recorrido');

        // Vaciar selects antes de llenarlos
        selectCiudad.empty().append('<option value="">Seleccione una ciudad</option>').prop('disabled', false);
        selectBarrio.empty().append('<option value="">Seleccione un barrio</option>').prop('disabled', false);

        // Llenar el select de ciudades
        barriosEmpresa.forEach(ciudad => {
            selectCiudad.append(`<option value="${ciudad.id}">${ciudad.ciudad}</option>`);
        });

        // Función para llenar barrios según la ciudad seleccionada
        function llenarBarrios(ciudadId, selectBarrio, barrioSeleccionado = '') {
            let ciudad = barriosEmpresa.find(c => c.id == ciudadId);
            selectBarrio.empty().append('<option value="">Seleccione un barrio</option>');

            if (ciudad && ciudad.barrios.length > 0) {
                ciudad.barrios.forEach(barrio => {
                    let value = barrio.zona;
                    let text = barrio.barrio || barrio.zona;
                    let isSelected = barrioSeleccionado == value ? 'selected' : '';
                    selectBarrio.append(`<option value="${value}" ${isSelected}>${text}</option>`);
                });
            }
        }

        // Desactivar cualquier evento previo y agregar uno nuevo
        $(document).off('change', '.ciudad_adicional_recorrido').on('change', '.ciudad_adicional_recorrido', function() {
            let ciudadId = $(this).val();
            let selectBarrio = $(this).closest('.franja').find('.barrio_adicional_recorrido');
            llenarBarrios(ciudadId, selectBarrio);
        });

        // Seleccionar la ciudad y llenar barrios si existen en `data`
        if (data.ciudad) {
            selectCiudad.val(data.ciudad).trigger('change');
            llenarBarrios(data.ciudad, selectBarrio, data.barrio);
        }
    }

    function llenarSelectsRepetidorV2(barriosEmpresa, $fila, data) {
        let selectCiudadOrigen = $fila.find('.ciudad_origen_pasajero_adicional');
        let selectBarrioOrigen = $fila.find('.barrio_origen_pasajero_adi');
        let selectCiudadDestino = $fila.find('.ciudad_destino_pasajero_adicional');
        let selectBarrioDestino = $fila.find('.barrio_destino_pasajero_adi');

        // Vaciar selects antes de llenarlos
        selectCiudadOrigen.empty().append('<option value="">Selecciona una ciudad Origen</option>').prop('disabled', false);
        selectBarrioOrigen.empty().append('<option value="">Seleccione un barrio</option>').prop('disabled', false);
        selectCiudadDestino.empty().append('<option value="">Selecciona una ciudad Destino</option>').prop('disabled', false);
        selectBarrioDestino.empty().append('<option value="">Seleccione un barrio</option>').prop('disabled', false);

        // Llenar el select de ciudades
        barriosEmpresa.forEach(ciudad => {
            selectCiudadOrigen.append(`<option value="${ciudad.id}">${ciudad.ciudad}</option>`);
            selectCiudadDestino.append(`<option value="${ciudad.id}">${ciudad.ciudad}</option>`);
        });

        // Función para llenar barrios según la ciudad seleccionada
        function llenarBarrios(ciudadId, selectBarrio, barrioSeleccionado = '') {
            let ciudad = barriosEmpresa.find(c => c.id == ciudadId);
            selectBarrio.empty().append('<option value="">Seleccione un barrio</option>');

            if (ciudad && ciudad.barrios.length > 0) {
                ciudad.barrios.forEach(barrio => {
                    let value = barrio.zona;
                    let text = barrio.barrio || barrio.zona;
                    let isSelected = barrioSeleccionado == value ? 'selected' : '';
                    selectBarrio.append(`<option value="${value}" ${isSelected}>${text}</option>`);
                });
            }
        }

        // Desactivar eventos previos y agregar nuevos
        $(document).off('change', '.ciudad_origen_pasajero_adicional').on('change', '.ciudad_origen_pasajero_adicional', function() {
            let ciudadId = $(this).val();
            let selectBarrio = $(this).closest('.franja').find('.barrio_origen_pasajero_adi');
            llenarBarrios(ciudadId, selectBarrio);
        });

        $(document).off('change', '.ciudad_destino_pasajero_adicional').on('change', '.ciudad_destino_pasajero_adicional', function() {
            let ciudadId = $(this).val();
            let selectBarrio = $(this).closest('.franja').find('.barrio_destino_pasajero_adi');
            llenarBarrios(ciudadId, selectBarrio);
        });

        // Seleccionar la ciudad y llenar barrios si existen en `data`
        if (data.ciudad_origen) {
            selectCiudadOrigen.val(data.ciudad_origen).trigger('change');
            llenarBarrios(data.ciudad_origen, selectBarrioOrigen, data.origen);
        }
        if (data.ciudad_destino) {
            selectCiudadDestino.val(data.ciudad_destino).trigger('change');
            llenarBarrios(data.ciudad_destino, selectBarrioDestino, data.destino);
        }

        $fila.find('.direccion_origen_adicional').val(data.direccion_origen);
        $fila.find('.direccion_destino_adicional').val(data.direccion_destino);
    }

    $(document).on('change', '#ciudad_inicio', function() {
        let ciudadId = $(this).val();
        let selectBarrio = $('#barrio_inicio');
        let selectBarrioFin = $('#barrio_fin');
        let selectCiudadFin = $('#ciudad_fin');

        selectCiudadFin.val(ciudadId).trigger('change').prop('disabled', false);

        if (ciudadId !== '0') {
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');
            $('body').addClass('actloader');

            $.ajax({
                url: recorridoAjax.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_barrios',
                    ciudad_id: ciudadId,
                },
                success: function(response) {
                    if (response.success) {

                        // Limpiar los selects antes de agregar nuevas opciones
                        selectBarrio.empty().append('<option value="">Selecciona un barrio</option>');
                        selectBarrioFin.empty().append('<option value="">Selecciona un barrio</option>');

                        $.each(response.data, function(index, barrio) {
                            let dataZona = barrio.zona ? ' data-zona="' + barrio.zona + '"' : ''; // Agregar solo si existe
                            let option = '<option' + dataZona + ' value="' + barrio.barrio + '">' + barrio.barrio + '</option>';

                            // Agregar la opción a los selects
                            selectBarrio.append(option);
                            selectBarrioFin.append(option);
                        });

                        // Habilitar los selects y disparar el evento change
                        selectBarrio.prop('disabled', false).trigger('change');
                        selectBarrioFin.prop('disabled', false).trigger('change');

                        $("#dir_inicial_recorrido").prop('disabled', false);

                        actualizarSelects($('#wrap-usuarios-adicionales .franja .barrio'), response.data);

                        $('body').removeClass('actloader');

                    } else {
                        $('body').removeClass('actloader');
                        // Mostrar un mensaje de error si no hay datos
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron barrios.'
                        });
                    }
                },
                error: function() {
                    $('body').removeClass('actloader');
                    // Mostrar un mensaje de error si la solicitud falla
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los barrios. Intenta nuevamente.'
                    });
                }
            });
        } else {
            selectBarrio.prop('disabled', true).trigger('change');
            selectBarrioFin.prop('disabled', true).trigger('change');
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');
        }
    });

    $(document).on('change', '#ciudad_fin', function() {
        let ciudadId = $(this).val();
        let selectBarrioFin = $('#barrio_fin');

        if (ciudadId !== '0') {
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>');

            $.ajax({
                url: recorridoAjax.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_barrios',
                    ciudad_id: ciudadId,
                },
                success: function(response) {
                    if (response.success) {
                        selectBarrioFin.empty().append('<option value="">Selecciona un barrio</option>');

                        // Iterar sobre cada barrio en response.data
                        $.each(response.data, function(index, barrio) {
                            // Crear una opción para el select de fin
                            selectBarrioFin.append(
                                '<option data-zona="' + barrio.zona + '" value="' + barrio.barrio + '">' + barrio.barrio + '</option>'
                            );
                        });
                        selectBarrioFin.prop('disabled', false).trigger('change');
                        $("#dir_final_recorrido").prop('disabled', false);
                    } else {
                        // Mostrar un mensaje de error si no hay datos
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron barrios.'
                        });
                    }
                },
                error: function() {
                    // Mostrar un mensaje de error si la solicitud falla
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los barrios. Intenta nuevamente.'
                    });
                }
            });
        } else {
            selectBarrioFin.prop('disabled', true);
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>');
        }
    });

    $(document).on('change', '#barrio_inicio', function() {
        let selectedOption = $(this).find('option:selected');
        let zona = selectedOption.attr('data-zona');

        if (zona !== undefined && zona !== null && zona !== '') {
            $("#barrio_zona_inicio").val(zona);
        }
    });

    $(document).on('change', '#barrio_fin', function() {
        let selectedOption = $(this).find('option:selected');
        let zona = selectedOption.attr('data-zona');

        if (zona !== undefined && zona !== null && zona !== '') {
            $("#barrio_zona_fin").val(zona);
        }
    });

    /*FRANJAS DE ASIGNACION*/

    $(document).on('change', '.barrio_adicional_recorrido', function() {
        let selectedOption = $(this).find('option:selected');
        let zona = selectedOption.attr('data-zona');

        if (zona !== undefined && zona !== null && zona !== '') {
            $(this).closest('.franja_item').find('.barrio_adicional_zona').val(zona);
        }
    });

    $(document).on('click', '#wrap-puntos-recorrido .button-add', function(e) {
        e.preventDefault();

        var newRow = $('#plantilla-recorrido .franja').clone();

        if (newRow.length === 0) {
            console.error("Error: No se encontró la plantilla para clonar.");
            return;
        }

        // Asegurar que no haya ids duplicados
        newRow.removeAttr("id");

        // Cambiar clases en los selects
        newRow.find('.ciudad').removeClass('ciudad').addClass('ciudad_adicional_recorrido');
        newRow.find('.barrio').removeClass('barrio').addClass('barrio_adicional_recorrido');

        // Limpiar los valores de los selects y inputs clonados
        newRow.find('select').val('').removeAttr('disabled');
        newRow.find('.direccion_adicional_zona').val('').removeAttr('disabled');

        // Agregar la nueva fila al contenedor
        $('#wrap-punto-recorrido').append(newRow);

        // Inicializar Select2 en los nuevos elementos
        newRow.find('.ciudad_adicional_recorrido').select2();
        newRow.find('.barrio_adicional_recorrido').select2();
    });

    $(document).on('click', '#wrap-puntos-recorrido .remove', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('#wrap-punto-recorrido');
        var $franja = $(this).closest('.franja');

        // Eliminar las reglas de validación asociadas a los elementos dentro de la franja
        $franja.find('select, input[type="text"]').each(function() {
            $(this).rules("remove");
        });

        // Eliminar la franja
        $franja.remove();
    });
    /*FIN FRANJAS RECORRIDO ADICIONAL*/

    /*FRANJAS DE USUARIO ADICIONAL*/
    $(document).on('click', '#wrap-usuarios-adicionales .button-add', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-usuario-adicional .franja').length;

        if (franjaCount >= 3) {
            return;
        }

        var newRow = $('#clonar-pas-adicional .franja').clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount).removeAttr('disabled');
        newRow.find('select').val('').removeAttr('disabled');

        // Obtener el select clonado y restablecer su estado
        var newSelect = newRow.find('select');

        // Agregar la nueva fila al DOM
        $('#wrap-usuario-adicional').append(newRow);

        newSelect.removeAttr('disabled').select2({
            placeholder: "Selecciona un Valor",
            allowClear: true,
            width: '100%'
        });
    });

    $(document).on('click', '#wrap-usuarios-adicionales .remove', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-usuario-adicional .franja').length;
        var $franja = $(this).closest('.franja');
        $franja.remove();
    });

    $(document).on('click', '#crear-recorrido', function(event) {
        event.preventDefault();

        $('#recorrido-form')[0].reset();
        $('#wrap-recorridos .wrap-gestion-recorridos button[type="submit"]').text('Crear Solicitud');

        $("#wrap-recorridos .wrap-listado-recorridos").hide();
        $("#wrap-recorridos .wrap-gestion-recorridos").show();
    });

    $(document).on('click', '#wrap-recorridos .wrap-gestion-recorridos .cancelar', function(event) {
        $('#recorrido-form')[0].reset();
        $("#recorrido-id").text('').val('');
        $('#select-rolesusuario').val(null).trigger('change');
        $('#wrap-recorridos #wrap-titform-recorrido').removeClass().addClass('wrap wrap-title');
        $('#wrap-usuarios-adicionales #wrap-usuario-adicional').empty();
        $("#wrap-recorridos .wrap-gestion-recorridos").hide();
        $("#wrap-recorridos .wrap-listado-recorridos").show();
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .edit-recorrido', function(event) {
        // Configuración inicial del formulario
        $('#wrap-recorridos .wrap-gestion-recorridos button[type="submit"]').text('Editar Solicitud');
        $('#wrap-recorridos .wrap-gestion-recorridos .title').text('Editar Solicitud');
        $("#wrap-recorridos .wrap-listado-recorridos").hide();
        $("#wrap-recorridos .wrap-gestion-recorridos").show();

        $("#wrap-punto-recorrido, #wrap-usuario-adicional").empty();

        let post_id = $(this).data('id');
        $("#recorrido-form #recorrido-id").val(post_id);
        $('body').addClass('actloader');

        $(document).off('change', '#barrio_inicio');
        $(document).off('change', '#barrio_fin');



        // Enviar la solicitud AJAX para obtener los datos del recorrido
        $.ajax({
            url: recorridoAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_recorrido_data',
                post_id: post_id
            },
            success: function(response) {
                $('body').removeClass('actloader');
                if (response.success) {

                    $("#fecha_inicio_recorrido").val(response.data.fecha_inicio_recorrido);
                    $("#hora_inicio_recorrido").val(response.data.hora_inicio_recorrido);
                    $("#dir_inicial_recorrido").val(response.data.dir_inicial_recorrido);
                    $("#dir_final_recorrido").val(response.data.dir_final_recorrido);
                    $("#comentario_colaborador_inicio_recorrido").val(response.data.comentario_colaborador_inicio_recorrido);

                    if ($('#id_solicitante_recorrido').length) {
                        $(document).off('change', '#id_solicitante_recorrido');
                        $("#id_solicitante_recorrido").val(response.data.id_solicitante_recorrido).trigger('change');
                    }

                    let barriosEmpresa = response.data.barrios_empresa;
                    $(document).off('change', '#ciudad_inicio');
                    $(document).off('change', '#ciudad_fin');
                    $(document).off('change', '#barrio_inicio');
                    $(document).off('change', '#barrio_fin');
                    llenarSelects(barriosEmpresa, response.data);

                    /*RAZON DE USO*/
                    $("#razon_uso_recorrido").empty().append('<option value="">Seleccione una Opción</option>');

                    response.data.razon_de_uso_para_el_recorrido.forEach(razon => {
                        $("#razon_uso_recorrido").append(`<option value="${razon.razon}">${razon.razon}</option>`);
                    });
                    if (response.data.razon_de_uso_del_recorrido && $('#razon_uso_recorrido').length) {
                        $("#razon_uso_recorrido").val(response.data.razon_de_uso_del_recorrido).trigger('change');
                    }

                    /*PERSONA QUE AUTORIZA*/
                    $("#persona_autoriza_recorrido").empty().append('<option value="">Seleccione una Opción</option>');

                    response.data.usuarios_administradores_empresa.forEach(persona => {
                        $("#persona_autoriza_recorrido").append(`<option value="${persona.ID}">${persona.user_firstname} ${persona.user_lastname}</option>`);
                    });
                    if (response.data.quien_autoriza_recorrido && $('#persona_autoriza_recorrido').length) {
                        $("#persona_autoriza_recorrido").val(response.data.quien_autoriza_recorrido).trigger('change');
                    }

                    /*RUTA*/
                    $("#tarifaxempresa").empty().append('<option value="">Seleccione una Opción</option>');

                    response.data.rutas_empresa.forEach(ruta => {
                        $("#tarifaxempresa").append(`<option value="${ruta.codigo}">${ruta.nombre_de_ruta}</option>`);
                    });
                    if (response.data.codigo_de_ruta_recorrido && $('#tarifaxempresa').length) {
                        $("#tarifaxempresa").val(response.data.codigo_de_ruta_recorrido).trigger('change');
                    }

                    /* PARADA ADICIONAL */
                    if (response.data.puntos_recorrido_adicionales) {
                        response.data.puntos_recorrido_adicionales.forEach(punto => {

                            var newRow = $('#plantilla-recorrido .franja').clone();

                            if (newRow.length === 0) {
                                console.error("Error: No se encontró la plantilla para clonar.");
                                return;
                            }

                            newRow.removeAttr("id");
                            newRow.find('.ciudad').removeClass('ciudad').addClass('ciudad_adicional_recorrido');
                            newRow.find('.barrio').removeClass('barrio').addClass('barrio_adicional_recorrido');
                            newRow.find('.direccion_adicional_zona').val(punto.direccion).removeAttr('disabled');

                            $('#wrap-punto-recorrido').append(newRow);

                            // Llenar selects en la nueva fila
                            llenarSelectsRepetidor(barriosEmpresa, newRow, punto);

                            // Inicializar Select2
                            newRow.find('.ciudad_adicional_recorrido, .barrio_adicional_recorrido').select2();
                        });
                    }

                    /* USUARIOS ADICIONALES */
                    if (response.data.usuarios_adicionales_recorrido) {
                        response.data.usuarios_adicionales_recorrido.forEach((usuario, index) => {

                            var newRow = $('#clonar-pas-adicional .franja').clone();

                            if (newRow.length === 0) {
                                console.error("Error: No se encontró la plantilla para clonar.");
                                return;
                            }

                            var newRow = $('#clonar-pas-adicional .franja').clone();

                            newRow.find('label').attr('for', 'franja-' + index);
                            newRow.find('input').val('').attr('id', 'franja-' + index).removeAttr('disabled');
                            newRow.find('select').val('').removeAttr('disabled');

                            // Obtener el select clonado y restablecer su estado
                            var newSelect = newRow.find('select');

                            // Agregar la nueva fila al DOM
                            $('#wrap-usuario-adicional').append(newRow);

                            /*newSelect.removeAttr('disabled').select2({
                                placeholder: "Selecciona un Valor",
                                allowClear: true,
                                width: '100%'
                            });*/

                            // Llenar selects en la nueva fila
                            llenarSelectsRepetidorV2(barriosEmpresa, newRow, usuario);

                            // Inicializar Select2
                            newRow.find(newSelect).select2();
                        });
                    }



                } else {
                    $('body').removeClass('actloader');
                    Swal.fire({
                        title: 'Algo ha ocurrido!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function() {
                $('body').removeClass('actloader');
                Swal.fire({
                    title: '¡Error!',
                    text: 'Hubo un problema al procesar la solicitud. Por favor intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        });
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .delete-recorrido', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let recorridoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el Recorrido de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').addClass('actloader');
                // Si el usuario confirma, realiza la solicitud AJAX
                $.ajax({
                    url: recorridoAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'delete_recorrido', // Acción personalizada en WordPress
                        post_id: recorridoid
                    },
                    success: function(response) {

                        if (response.success) {

                            $('body').removeClass('actloader');

                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'El recorrido ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/recorrido.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initRecorridos();
                                    },
                                    error: function() {
                                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                                    }
                                });

                            });
                        } else {
                            $('body').removeClass('actloader');
                            // Mostrar mensaje de error
                            Swal.fire(
                                'Error',
                                response.data.message || 'No se pudo eliminar el vehículo.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
                        // Mostrar mensaje de error si AJAX falla
                        Swal.fire(
                            'Error',
                            'Hubo un problema al intentar eliminar el vehículo.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .cancelar-recorrido', function(event) {
        event.preventDefault();
        let recorridoid = $(this).data('id');

        $("#cancelarServicioForm #idServicio").val(recorridoid);
        $('#modal-cancelar').fadeIn().css('display', 'flex');
    });

    $(document).on('submit', '#cancelarServicioForm', function(event) {
        event.preventDefault(); // Evitar el envío tradicional del formulario

        $('body').addClass('actloader'); // Mostrar un loader (si lo tienes)

        var formData = new FormData(this); // Crear un objeto FormData con los datos del formulario
        formData.append('action', 'cancelar_recorrido_data');

        $.ajax({
            url: recorridoAjax.ajaxurl, // URL de la solicitud AJAX
            method: 'POST',
            data: formData, // Enviar el objeto FormData
            processData: false, // No procesar los datos (necesario para FormData)
            contentType: false, // No establecer el tipo de contenido (necesario para FormData)
            success: function(response) {
                $('body').removeClass('actloader'); // Ocultar el loader

                if (response.success) {

                    // Cerrar la modal y limpiar el formulario
                    $('#modal-cancelar').fadeOut();
                    $('#cancelarServicioForm')[0].reset();

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: '¡Cancelación exitosa!',
                        text: 'El servicio ha sido cancelado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/recorrido.php";

                        $.ajax({
                            url: fileUrl,
                            method: "POST",
                            data: {
                                action: 'render_html_panel',
                            },
                            success: function(response) {
                                $("#informacion").html(response);
                                initRecorridos();
                            },
                            error: function() {
                                $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                            }
                        });
                    });


                } else {
                    $('body').removeClass('actloader');
                    // Mostrar mensaje de error desde la respuesta
                    Swal.fire({
                        title: 'Algo ha ocurrido!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function() {
                $('body').removeClass('actloader'); // Ocultar el loader
                // Mostrar mensaje de error genérico
                Swal.fire({
                    title: '¡Error!',
                    text: 'Hubo un problema al procesar la solicitud. Por favor intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            }
        });
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .ver-recorrido', function(event) {
        event.preventDefault();
        let recorridoid = $(this).data('id');
        let empresa = $(this).closest('tr').find('.empresa').text();

        $('body').addClass('actloader');

        $('#mod-idrec').text(recorridoid);
        $('#modal-recorrido').fadeIn().css('display', 'flex');

        $.ajax({
            url: recorridoAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'ver_recorrido_data',
                post_id: recorridoid
            },
            success: function(response) {
                if (response.success) {

                    $("#mod-daterec").text(response.data.fecha_inicio_recorrido);
                    $("#mod-desinirec").text(response.data.destino_inicio);
                    $("#mod-desfinrec").text(response.data.destino_final);
                    $("#mod-nombrec").text(response.data.nomb_usuario);
                    $("#mod-emprec").text(empresa);

                    $('body').removeClass('actloader');

                } else {
                    $('body').removeClass('actloader');
                    Swal.fire({
                        title: 'Algo ha ocurrido!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function() {
                $('body').removeClass('actloader');
                Swal.fire({
                    title: '¡Error!',
                    text: 'Hubo un problema al procesar la solicitud. Por favor intenta nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        });
    });

    $(document).on('click', '.close, #modal-recorrido, #modal-cancelar', function(event) {
        if (event.target === this) {
            $('#modal-recorrido p span').text('');
            $idmod = $(this).closest('.modal');
            $idmod.fadeOut().css('display', 'none');
        }
    });

    /*ENVIO Y VALIDACION DE FORMULARIO*/
    $(document).on('focusin', '#recorrido-form', function() {

        // Extender jQuery Validation para que funcione con select2 y elementos dinámicos
        $.validator.setDefaults({
            ignore: ':hidden:not(.select2-hidden-accessible)', // Ignorar elementos ocultos excepto select2
        });

        // Validación personalizada para select2
        $.validator.addMethod("select2Required", function(value, element, param) {
            return value !== null && value !== ""; // Validar que el valor no esté vacío
        }, "Este dato es obligatorio");

        $(this).validate({
            rules: {
                id_solicitante_recorrido: {
                    required: true,
                },
                ciudad_inicio: {
                    required: true,
                },
                barrio_inicio: {
                    required: true,
                },
                ciudad_fin: {
                    required: true,
                },
                barrio_fin: {
                    required: true,
                },
                fecha_inicio_recorrido: {
                    required: true,
                },
                hora_inicio_recorrido: {
                    required: true,
                },
                dir_inicial_recorrido: {
                    required: true,
                },
                dir_final_recorrido: {
                    required: true,
                },
                comentario_colaborador_inicio_recorrido: {
                    required: true,
                },
                razon_uso_recorrido: {
                    required: true,
                },
                persona_autoriza_recorrido: {
                    required: true,
                }
            },
            messages: {
                id_solicitante_recorrido: "Este dato es obligatorio",
                ciudad_inicio: "Este dato es obligatorio",
                barrio_inicio: "Este dato es obligatorio",
                ciudad_fin: "Este dato es obligatorio",
                barrio_fin: "Este dato es obligatorio",
                fecha_inicio_recorrido: "Este dato es obligatorio",
                hora_inicio_recorrido: "Este dato es obligatorio",
                dir_inicial_recorrido: "Este dato es obligatorio",
                dir_final_recorrido: "Este dato es obligatorio",
                comentario_colaborador_inicio_recorrido: "Este dato es obligatorio",
                razon_uso_recorrido: "Este dato es obligatorio",
                persona_autoriza_recorrido: "Este dato es obligatorio",
            },
            submitHandler: function(form) {

                // Recoger los datos del formulario
                var formData = new FormData(form);
                formData.append('action', 'create_recorrido');

                // Verificar si el select existe
                if ($('#tarifaxempresa').length) {
                    var selectedOption = $('#tarifaxempresa').find(':selected');

                    if (selectedOption.length) {
                        var selectedValue = selectedOption.val();
                        var dataValor = selectedOption.data('valor');
                        var nombre = selectedOption.data('nombre');

                        formData.append('tarifa_codigo', selectedValue);
                        formData.append('tarifa_ruta', nombre);
                        formData.append('tarifa_valor', dataValor);
                    }
                }

                // Realizar la petición AJAX
                $.ajax({
                    url: recorridoAjax.ajaxurl, // Ruta del endpoint AJAX
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('body').addClass('actloader');
                    },
                    success: function(response) {
                        $('body').removeClass('actloader');
                        if (response.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Reiniciar formulario
                                    $('#recorrido-form')[0].reset();

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        recorridoAjax.plugin_url +
                                        'includes/parts/panel/recorrido.php';

                                    $.ajax({
                                        url: fileUrl,
                                        method: "POST",
                                        data: {
                                            action: 'render_html_panel',
                                        },
                                        success: function(response) {
                                            $('#informacion').html(response);
                                            initRecorridos();
                                        },
                                        error: function() {
                                            $('#informacion').html(
                                                '<p>Error al cargar el contenido. Intenta nuevamente.</p>'
                                            );
                                        },
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Algo ha ocurrido!',
                                text: response.data.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                            });
                        }
                    },
                    error: function() {
                        $('body').removeClass('actloader');
                        Swal.fire({
                            title: '¡Error!',
                            text: 'Hubo un problema al procesar el formulario. Por favor intenta nuevamente.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    },
                });
            },
        });
    });
});