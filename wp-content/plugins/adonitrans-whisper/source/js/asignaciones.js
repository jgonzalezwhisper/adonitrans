jQuery(document).ready(function($) {

    $(document).on('change', '#inicio_semana_asignacion', function() {
        const input = $(this);
        const date = new Date(input.val());
        const day = date.getDay();

        if (day !== 0) {
            Swal.fire(
                'Error',
                'Por favor, selecciona un lunes.',
                'error'
            );
            input.val('');
        } else {
            // Encuentra el próximo sábado
            let nextSaturday = new Date(date);
            nextSaturday.setDate(date.getDate() + 5); // 5 días después de lunes es sábado

            // Formatea la fecha como "YYYY-MM-DD"
            const formattedDate = nextSaturday.toISOString().split('T')[0];

            // Ajusta la fecha en el input de fin de semana
            $('#fin_semana_asignacion').val(formattedDate);
        }
    });

    $(document).on('click', '#wrap-asignaciones .boton', function(event) {
        event.preventDefault();
        action = $(this).data('action');

        $('#wrap-asignaciones .wrap-acciones').hide();
        $('#wrap-asignaciones .wrap-gestion[data-target="' + action + '"], #wrap-asignaciones .volver').show();

        calendar.render();
        calendar.updateSize();
    });

    $(document).on('change', '#id_conductor_asignado_filtcal', function(event) {
        const conductorId = $(this).val();

        $.ajax({
            url: asignacionAjax.ajaxurl, // URL definida en WordPress
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'filtrar_asignaciones', // Acción de WordPress
                conductor_id: conductorId // ID del conductor seleccionado
            },
            success: function(data) {
                console.log('Eventos recibidos:', JSON.stringify(data, null, 2));

                if (Array.isArray(data)) {
                    // Limpia las fuentes de eventos existentes
                    calendar.getEventSources().forEach(source => source.remove());

                    // Añade los nuevos eventos al calendario
                    calendar.addEventSource({
                        events: data, // Agrega directamente el array de eventos
                        color: 'blue', // Color opcional para los eventos
                        textColor: 'white' // Color opcional del texto
                    });

                    // Refresca los eventos del calendario
                    calendar.refetchEvents();
                } else {
                    console.error('El formato de los datos no es un array:', data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar los eventos:', error);
            }
        });
    });


    $(document).on('click', '#wrap-asignaciones .volver .button', function(event) {
        event.preventDefault();
        action = $(this).data('action');

        $('#wrap-asignaciones .wrap-acciones').show();
        $('#wrap-asignaciones .wrap-gestion, #wrap-asignaciones .volver').hide();
    });

    $(document).on('click', '#wrap-asignaciones .button.cancelar', function(event) {
        event.preventDefault();
        $('#asignacion-form')[0].reset();
        $('#wrap-asignacion-dia .franja').not(':first').remove();
        $("#asignacion-id").text('').val('');
        $('#id_conductor_asignado').val(null).trigger('change');
        $('#wrap-asignaciones .wrap-acciones').show();
        $('#wrap-asignaciones .wrap-gestion, #wrap-asignaciones .volver').hide();
    });

    /*FRANJAS DE ASIGNACION*/
    $(document).on('click', '#wrap-asignacion-dias .button-add', function(e) {
        e.preventDefault();

        var franjaCount = $('#wrap-asignacion-dias .franja').length;

        var newRow = $('.franja').last().clone();

        newRow.find('label').attr('for', 'franja-' + franjaCount);
        newRow.find('input').val('').attr('id', 'franja-' + franjaCount);

        $('#wrap-asignacion-dia').append(newRow);
    });

    $(document).on('click', '#wrap-asignacion-dias .remove', function(e) {
        e.preventDefault();

        var $wrapFranjas = $('#wrap-asignacion-dia');
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

    /*ELIMINAR POST ASIGNACION*/
    $(document).on('click', '.wrap-listado-asignaciones .delete-asignacion', function(event) {
        event.preventDefault();

        // Obtener el ID del usuario desde el botón
        let asignacionid = $(this).data('id');

        // Mostrar la confirmación con SweetAlert
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la Asignación de forma permanente.',
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
                    url: asignacionAjax.ajaxurl, // La URL de admin-ajax.php en WordPress
                    method: 'POST',
                    data: {
                        action: 'delete_asignacion', // Acción personalizada en WordPress
                        post_id: asignacionid
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire(
                                '¡Eliminado!',
                                'La asignación ha sido eliminado exitosamente.',
                                'success'
                            ).then(() => {
                                var fileUrl = asignacionAjax.plugin_url + "includes/parts/panel/asignacion.php";

                                $.ajax({
                                    url: fileUrl,
                                    method: "POST",
                                    data: {
                                        action: 'render_html_panel',
                                    },
                                    success: function(response) {
                                        $("#informacion").html(response);
                                        initVehiculos();
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

    $(document).on('click', '.wrap-listado-asignaciones .edit-asignacion', function(event) {

        $('#asignacion-form button[type="submit"]').text('Editar Asignación');
        $('#wrap-asignaciones .wrap-title .title').text('Editar Asignación');

        let post_id = $(this).data('id');
        $("#asignacion-form #asignacion-id").val(post_id);
        $('body').addClass('actloader');

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: asignacionAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_asignacion_data',
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {

                    // Conservar solo la primera franja como plantilla
                    var $baseFranja = $('#wrap-asignacion-dia .franja').first();
                    $('#wrap-asignacion-dia .franja').not(':first').remove();

                    $.each(response.data, function(key, value) {
                        if (key === 'id_conductor_asignado') {
                            $('#' + key).val(value).trigger('change');
                        } else if (key !== 'asignaciones_de_la_semana') {
                            $('#' + key).val(value);
                        }
                    });

                    // Procesar asignaciones_de_la_semana
                    if (response.data.asignaciones_de_la_semana && response.data.asignaciones_de_la_semana.length > 0) {
                        $.each(response.data.asignaciones_de_la_semana, function(index, asignacion) {
                            let $currentFranja;

                            if (index === 0) {
                                // Usar la primera franja para la primera asignación
                                $currentFranja = $baseFranja;
                            } else {
                                // Clonar la franja base para asignaciones adicionales
                                $currentFranja = $baseFranja.clone();
                                $('#wrap-asignacion-dia').append($currentFranja);
                            }

                            // Rellenar los datos en la franja actual
                            $currentFranja.find('input[name="dia_inicio_de_asignacion[]"]').val(asignacion.dia_inicio_de_asignacion);
                            $currentFranja.find('input[name="dia_fin_de_asignacion[]"]').val(asignacion.dia_fin_de_asignacion);
                            $currentFranja.find('select[name="franja_horaria_asignacion[]"]').val(asignacion.franja_horaria_asignacion);
                        });
                    }


                    $("#wrap-asignaciones .wrap-listado-asignaciones").hide();
                    $("#wrap-asignaciones .wrap-gestion-asignaciones").show();
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
            }

        });
    });

    /*ENVIO Y VALIDACION DE FORMULARIO*/
    $(document).on('focusin', '#asignacion-form', function() {

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
                id_conductor_asignado: {
                    select2Required: true,
                },
                inicio_semana_asignacion: {
                    required: true,
                },
                fin_semana_asignacion: {
                    required: true,
                }
            },
            messages: {
                id_conductor_asignado: "Este dato es obligatorio",
                inicio_semana_asignacion: "Este dato es obligatorio",
                fin_semana_asignacion: "Este dato es obligatorio"
            },
            submitHandler: function(form) {

                // Recoger los datos del formulario
                var formData = new FormData(form);
                formData.append('action', 'create_asignacion');

                // Realizar la petición AJAX
                $.ajax({
                    url: asignacionAjax.ajaxurl, // Ruta del endpoint AJAX
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
                                    $('#asignacion-form')[0].reset();

                                    // Cargar nuevo contenido
                                    var fileUrl =
                                        asignacionAjax.plugin_url +
                                        'includes/parts/panel/asignacion.php';

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