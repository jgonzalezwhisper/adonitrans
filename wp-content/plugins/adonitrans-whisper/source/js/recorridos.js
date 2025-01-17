jQuery(document).ready(function($) {

    $(document).on('change', '#id_solicitante_recorrido', function() {
        let idSolicitante = $(this).val();
        let centro_de_costo = $("#centro_de_costo");

        if (idSolicitante !== '0') {
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
        } else {
            centro_de_costo.prop('disabled', true).trigger('change');
            centro_de_costo.empty().append('<option value="0">Selecciona un centro de costo</option>').trigger('change');
        }
    });

    $(document).on('change', '#ciudad_inicio', function() {
        var ciudadId = $(this).val();
        var selectBarrio = $('#barrio_inicio');
        var selectBarrioFin = $('#barrio_fin');
        var selectCiudadFin = $('#ciudad_fin');

        selectCiudadFin.val(ciudadId).trigger('change').prop('disabled', false);

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
                        $.each(response.data, function(index, barrio) {
                            selectBarrio.append('<option value="' + barrio + '">' + barrio + '</option>');
                            selectBarrioFin.append('<option value="' + barrio + '">' + barrio + '</option>');
                        });
                        selectBarrio.prop('disabled', false).trigger('change');
                        selectBarrioFin.prop('disabled', false).trigger('change');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron barrios.'
                        });
                    }
                },
                error: function() {
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
        var ciudadId = $(this).val();
        var selectBarrio = $('#barrio_fin');

        if (ciudadId !== '0') {
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>');

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
                        $.each(response.data, function(index, barrio) {
                            selectBarrio.append('<option value="' + barrio + '">' + barrio + '</option>');
                        });
                        selectBarrio.prop('disabled', false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.data.message || 'No se encontraron barrios.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los barrios. Intenta nuevamente.'
                    });
                }
            });
        } else {
            selectBarrio.prop('disabled', true);
            selectBarrio.empty().append('<option value="0">Selecciona un barrio</option>');
        }
    });

    $(document).on('click', '#crear-recorrido', function(event) {
        event.preventDefault();

        $('#recorrido-form')[0].reset();
        $('#wrap-recorridos .wrap-gestion-recorridos button[type="submit"]').text('Crear Solicitud');

        $("#wrap-recorridos .wrap-listado-recorridos").hide();
        $("#wrap-recorridos .wrap-gestion-recorridos").show();
    });

    $(document).on('click', '#wrap-recorridos .wrap-gestion-recorridos button[type="button"]', function(event) {
        $('#recorrido-form')[0].reset();
        $("#recorrido-id").text('').val('');
        $('#select-rolesusuario').val(null).trigger('change');
        $("#wrap-recorridos .wrap-gestion-recorridos").hide();
        $("#wrap-recorridos .wrap-listado-recorridos").show();
    });

    $(document).on('click', '#wrap-recorridos .wrap-listado-recorridos .edit-recorrido', function(event) {

        $('#wrap-recorridos .wrap-gestion-recorridos button[type="submit"]').text('Editar Solicitud');
        $('#wrap-recorridos .wrap-gestion-recorridos .title').text('Editar Solicitud');
        $("#wrap-recorridos .wrap-listado-recorridos").hide();
        $("#wrap-recorridos .wrap-gestion-recorridos").show();

        let post_id = $(this).data('id');
        $("#recorrido-form #recorrido-id").val(post_id);
        $('body').addClass('actloader');

        // Enviar la solicitud AJAX para obtener los datos del usuario
        $.ajax({
            url: recorridoAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'load_recorrido_data',
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {
                    $.each(response.data, function(key, value) {
                        if (key === 'id_solicitante_recorrido' || key === 'ciudad_inicio' || key === 'ciudad_fin') {
                            $('#' + key).val(value).trigger('change');
                        } else {
                            $('#' + key).val(value);
                        }
                    });

                    setTimeout(() => {
                        $('#barrio_inicio').val(response.data.barrio_inicio).trigger('change');
                        $('#barrio_fin').val(response.data.barrio_fin).trigger('change');
                        $('#centro_de_costo').val(response.data.centro_de_costo).trigger('change');
                    }, 1000);
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