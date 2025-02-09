jQuery(document).ready(function($) {

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

    $(document).on('change', '#ciudad_inicio', function() {
        let ciudadId = $(this).val();
        let selectBarrio = $('#barrio_inicio');
        let selectBarrioFin = $('#barrio_fin');
        let selectCiudadFin = $('#ciudad_fin');
        let ciudadAdicionalRecorrido = $('.ciudad_adicional_recorrido');

        selectCiudadFin.val(ciudadId).trigger('change').prop('disabled', false);
        ciudadAdicionalRecorrido.val(ciudadId).trigger('change').prop('disabled', false);

        if (ciudadId !== '0') {
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');
            selectBarrioFin.empty().append('<option value="0">Selecciona un barrio</option>').trigger('change');

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

                        actualizarSelects($('#wrap-usuarios-adicionales .franja .barrio'), response.data);

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

    /*window.validarUltimaFranja();*/

    // Escuchar cambios en los selects dentro de #wrap-punto-recorrido
    $(document).on('change', '.ciudad_adicional_recorrido, .barrio_adicional_recorrido', function() {
        /*validarUltimaFranja();*/
    });

    $(document).on('change', '.barrio_adicional_recorrido', function() {
        let selectedOption = $(this).find('option:selected');
        let zona = selectedOption.attr('data-zona');

        if (zona !== undefined && zona !== null && zona !== '') {
            $(this).closest('.franja_item').find('.barrio_adicional_zona').val(zona);
        }
    });

    $(document).on('click', '#wrap-puntos-recorrido .button-add', function(e) {
        e.preventDefault();

        // Verificar que existe al menos una franja antes de añadir una nueva
        if ($('#wrap-punto-recorrido .franja').length === 0) {
            console.error("Error: No se encontró la plantilla para clonar.");
            $('.button-add').prop('disabled', true);
            return;
        }

        // Clonar la plantilla oculta
        var newRow = $('#plantilla-recorrido .franja').clone();

        // Verificar si la plantilla se clonó correctamente
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
        newRow.find('select').val('');
        newRow.find('.barrio_adicional_zona').val('');

        // Agregar la nueva fila al contenedor
        $('#wrap-punto-recorrido').append(newRow);

        // Inicializar Select2 en los nuevos elementos
        $('#wrap-punto-recorrido .ciudad_adicional_recorrido').last().select2();
        $('#wrap-punto-recorrido .barrio_adicional_recorrido').last().select2();

        // Llamar a la validación para verificar si se debe habilitar el botón nuevamente
        /*validarUltimaFranja();*/
    });

    $(document).on('click', '#wrap-puntos-recorrido .remove', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('#wrap-punto-recorrido');
        var $franja = $(this).closest('.franja');

        if ($wrapFranjas.find('.franja').length > 1) {
            $franja.remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: 'Debe haber al menos una franja horaria configurada',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Manejar el cambio de ciudad para cargar los barrios
    $(document).on('change', '.ciudad_adicional_recorrido', function() {
        var ciudadId = $(this).val();
        var $barrioSelect = $(this).closest('.franja').find('.barrio_adicional_recorrido');

        if (ciudadId) {
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
                        // Limpiar el select antes de agregar nuevas opciones
                        $barrioSelect.empty().append('<option value="">Selecciona un barrio</option>');

                        // Iterar sobre cada barrio en response.data
                        $.each(response.data, function(index, barrio) {

                            let dataZona = barrio.zona ? ' data-zona="' + barrio.zona + '"' : ''; // Agregar solo si existe
                            let option = '<option' + dataZona + ' value="' + barrio.barrio + '">' + barrio.barrio + '</option>';

                            // Agregar la opción a los selects
                            $barrioSelect.append(option);
                        });

                        // Habilitar el select y disparar el evento change
                        $barrioSelect.prop('disabled', false).trigger('change');
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
            $barrioSelect.empty().append('<option value="">Selecciona un barrio</option>').prop('disabled', true);
        }
    });

    /*FIN FRANJAS*/

    /*FRANJAS DE USUARIO ADICIONAL*/
    $(document).on('click', '#wrap-usuarios-adicionales .button-add', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-usuario-adicional .franja').length;

        if (franjaCount >= 3) {
            return;
        }

        var newRow = $('#clonar-pas-adicional .franja').clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount);

        // Obtener el select clonado y restablecer su estado
        var newSelect = newRow.find('.select');
        newSelect.addClass('select_vehiculo');

        // Agregar la nueva fila al DOM
        $('#wrap-usuario-adicional').append(newRow);

        newSelect.select2({
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

        let post_id = $(this).data('id');
        $("#recorrido-form #recorrido-id").val(post_id);
        $('body').addClass('actloader');

        // Enviar la solicitud AJAX para obtener los datos del recorrido
        $.ajax({
            url: recorridoAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_recorrido_data',
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {

                    $("#fecha_inicio_recorrido").val(response.data.fecha_inicio_recorrido);
                    $("#hora_inicio_recorrido").val(response.data.hora_inicio_recorrido);

                    if ($('#id_solicitante_recorrido').length) {
                        $("#id_solicitante_recorrido").val(response.data.id_solicitante_recorrido).trigger('change');
                    }

                    // Llenar campos que dependen de otros selectores
                    setTimeout(() => {
                        $('#ciudad_inicio').val(response.data.ciudad_inicio).trigger('change');
                        $('#ciudad_fin').val(response.data.ciudad_fin).trigger('change');
                        $('#centro_de_costo').val(response.data.centro_de_costo).trigger('change');
                    }, 1000);

                    setTimeout(() => {
                        $('#barrio_inicio').val(response.data.barrio_inicio).trigger('change');
                        $('#barrio_fin').val(response.data.barrio_fin).trigger('change');
                        if ($('#tarifaxempresa').length) {
                            $('#tarifaxempresa').val(response.data.codigo_de_ruta_recorrido).trigger('change');
                        }
                    }, 1600);

                    setTimeout(() => {
                        // Puntos adicionales
                        if (response.data.puntos_recorrido_adicionales && response.data.puntos_recorrido_adicionales.length) {
                            let puntos = response.data.puntos_recorrido_adicionales;

                            // Limpiar puntos existentes, excepto el primero
                            $('#wrap-punto-recorrido .franja:not(:first)').remove();

                            // Asignar el primer punto al HTML existente
                            let primerPunto = puntos[0];
                            $('#wrap-punto-recorrido .franja:first .ciudad_adicional_recorrido').val(primerPunto.ciudad).trigger('change');

                            setTimeout(() => {
                                $('#wrap-punto-recorrido .franja:first .barrio_adicional_recorrido').val(primerPunto.nombre_del_barrio).trigger('change');
                            }, 1500);

                            // Clonar y agregar los puntos restantes
                            for (let i = 1; i < puntos.length; i++) {
                                let punto = puntos[i];
                                let newRow = $('#plantilla-recorrido .franja').clone().removeAttr("id");

                                // Configurar los selects
                                newRow.find('.ciudad').removeClass('ciudad').addClass('ciudad_adicional_recorrido').val(punto.ciudad).trigger('change');
                                newRow.find('.barrio').removeClass('barrio').addClass('barrio_adicional_recorrido');

                                // Inicializar Select2
                                newRow.find('.ciudad_adicional_recorrido').select2({
                                    allowClear: true,
                                    width: '100%'
                                });
                                newRow.find('.barrio_adicional_recorrido').select2({
                                    allowClear: true,
                                    width: '100%'
                                });

                                // Agregar al contenedor
                                $('#wrap-punto-recorrido').append(newRow);

                                // // Asignar barrio con retraso
                                // setTimeout(((barrioSelect, barrio) => {
                                //     return () => barrioSelect.val(barrio).trigger('change');
                                // })(newRow.find('.barrio_adicional_recorrido'), punto.nombre_del_barrio), 2500);
                            }
                        }

                        if ($("#wrap-usuarios-adicionales").length && response.data.usuarios_adicionales_recorrido) {

                            $.ajax({
                                url: recorridoAjax.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'get_colegas_empresa',
                                    colaborador_id: response.data.id_solicitante_recorrido
                                },
                                success: function(resdatacol) {
                                    if (resdatacol.success) {
                                        let colaboradores = resdatacol.data;
                                        let options = '<option value="">Selecciona un Colaborador</option>';

                                        colaboradores.forEach(function(colaborador) {
                                            options += '<option value="' + colaborador.ID + '">' + colaborador.display_name + ' (' + colaborador.user_email + ')</option>';
                                        });

                                        $('#clonar-pas-adicional .sel_adicional_usuario').html(options).trigger('change');
                                    }
                                }
                            });

                            $.each(response.data.usuarios_adicionales_recorrido, function(index, usua_adicional) {

                                let $currentFranja;

                                $currentFranja = $('#clonar-pas-adicional .franja').clone();

                                $currentFranja.find('label').attr('for', 'franja-' + index);
                                $currentFranja.find('input').val('').attr('id', 'franja-' + index);

                                // Obtener el select clonado y restablecer su estado
                                var newSelect = $currentFranja.find('.select');
                                newSelect.addClass('select_vehiculo');

                                // Agregar la nueva fila al DOM
                                $('#wrap-usuario-adicional').append($currentFranja);

                                newSelect.select2({
                                    placeholder: "Selecciona un Valor",
                                    allowClear: true,
                                    width: '100%'
                                });

                                // Rellenar los datos en la franja actual
                                $currentFranja.find('select[name="sel_id_usuario_adicional[]"]').val(usua_adicional.id_usuario_adicional).trigger('change');
                                $currentFranja.find('select[name="origen_adicional[]"]').val(usua_adicional.origen).trigger('change');
                                $currentFranja.find('input[name="direccion_origen_adicional[]"]').val(usua_adicional.direccion_origen);
                                $currentFranja.find('select[name="destino_adicional[]"]').val(usua_adicional.destino).trigger('change');
                                $currentFranja.find('input[name="direccion_destino_adicional[]"]').val(usua_adicional.direccion_destino);
                            });
                        }

                    }, 2000);

                    // Verificar si el selector de conductores existe
                    if ($('#id_conductor_recorrido').length) {
                        let id_recorrido = $('#recorrido-id').val();

                        // Llamar a la función obtener_conductores_asignados si el selector existe
                        $.ajax({
                            url: recorridoAjax.ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'obtener_conductores_asignados',
                                id_recorrido: id_recorrido
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#id_conductor_recorrido').html('<option value="0">Selecciona un Conductor</option>');
                                    $.each(response.data, function(index, conductor) {
                                        $('#id_conductor_recorrido').append(
                                            `<option value="${conductor.id}">${conductor.nombre}</option>`
                                        );
                                    });

                                    // Asegúrate de seleccionar el conductor si existe
                                    if (response.data.id_conductor_recorrido) {
                                        $('#id_conductor_recorrido').val(response.data.id_conductor_recorrido).trigger('change');
                                    }
                                } else {
                                    Swal.fire({
                                        title: '¡Error!',
                                        text: response.data,
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar',
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: '¡Error!',
                                    text: 'Error en la solicitud AJAX para obtener conductores.',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                });
                            }
                        });
                    }

                    setTimeout(() => {
                        $('body').removeClass('actloader');
                    }, 2000);


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
                            // Mostrar mensaje de error
                            Swal.fire(
                                'Error',
                                response.data.message || 'No se pudo eliminar el vehículo.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .iniciar-recorrido', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let recorridoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede reversar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Iniciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').addClass('actloader');
                $.ajax({
                    url: recorridoAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'iniciar_recorrido', // Acción personalizada en WordPress
                        post_id: recorridoid
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Recorrido Iniciado!',
                                'Has comenzado tu recorrido con éxito. ¡Disfruta del viaje y mantente seguro!',
                                'success'
                            ).then(() => {
                                var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                        post_id: recorridoid
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
                            $('body').removeClass('actloader');

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

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .panel-recorrido', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let recorridoid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Desea Continuar?',
            text: 'Esta seguro de retomar el servicio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').addClass('actloader');

                var fileUrl = recorridoAjax.plugin_url + "includes/parts/panel/conductor.php";

                $.ajax({
                    url: fileUrl,
                    method: "POST",
                    data: {
                        action: 'render_html_panel',
                        post_id: recorridoid
                    },
                    success: function(response) {
                        $("#informacion").html(response);
                        $('body').removeClass('actloader');
                    },
                    error: function() {
                        $("#informacion").html("<p>Error al cargar el contenido. Intenta nuevamente.</p>");
                    }
                });

            }
        });


    });

    $(document).on('click', '.close, #modal-recorrido', function(event) {
        if (event.target === this) {
            $('#modal-recorrido p span').text('');
            $('#modal-recorrido').fadeOut().css('display', 'none');
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
                centro_de_costo: {
                    select2Required: true,
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
                centro_de_costo: "Este dato es obligatorio",
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